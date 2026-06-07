<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalPelajaran;
use App\Models\Absensi;

use Illuminate\Support\Facades\DB;

class PortalSiswa extends Component
{
    public $siswa;
    public $showScannerModal = false;
    public $showRegisterFaceModal = false;
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $user = Auth::user();
        
        // Ensure only siswa can access
        if (!$user || !$user->isSiswa() || !$user->siswa) {
            abort(403, 'Hanya siswa yang dapat mengakses portal ini.');
        }

        $this->siswa = $user->siswa;

        // Auto-generate token if empty
        if (empty($this->siswa->qr_code_token)) {
            $this->siswa->update([
                'qr_code_token' => 'TOKEN_' . strtoupper(uniqid()) . '_' . $this->siswa->id
            ]);
        }
    }

    public function toggleScannerModal()
    {
        $this->showScannerModal = !$this->showScannerModal;
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function prosesScanQrKelas($payload, $scannedEmbedding = null)
    {
        $this->successMessage = '';
        $this->errorMessage = '';

        // Face ID verification if registered
        if ($this->siswa->face_embedding) {
            if (empty($scannedEmbedding)) {
                $this->errorMessage = 'Verifikasi Face ID diperlukan untuk melakukan absensi.';
                $this->showScannerModal = false;
                return;
            }

            $distance = $this->calculateEuclideanDistance($this->siswa->face_embedding, $scannedEmbedding);
            if ($distance > 0.6) {
                $this->errorMessage = 'Absensi Gagal: Wajah tidak cocok dengan Face ID terdaftar (selisih: ' . round($distance, 3) . ').';
                $this->showScannerModal = false;
                return;
            }
        }

        $jadwalId = $payload;

        // Verify if payload is dynamic
        if (str_contains($payload, '|')) {
            $parts = explode('|', $payload);
            if (count($parts) === 3) {
                $jadwalId = $parts[0];
                $timeWindow = $parts[1];
                $hash = $parts[2];

                // Validate signature
                $expectedHash = substr(hash_hmac('sha256', $jadwalId . '|' . $timeWindow, config('app.key')), 0, 16);
                if (!hash_equals($expectedHash, $hash)) {
                    $this->errorMessage = 'QR Code tidak valid (tanda tangan tidak cocok).';
                    $this->showScannerModal = false;
                    return;
                }

                // Validate expiration (allow current window and up to 2 previous windows = 30 seconds max lag)
                $currentWindow = floor(time() / 15);
                $diff = $currentWindow - $timeWindow;
                if ($diff < 0 || $diff > 2) {
                    $this->errorMessage = 'QR Code sudah kedaluwarsa. Silakan scan QR Code terbaru dari layar guru.';
                    $this->showScannerModal = false;
                    return;
                }
            } else {
                $this->errorMessage = 'Format QR Code tidak valid.';
                $this->showScannerModal = false;
                return;
            }
        } else {
            // For testing and backward compatibility, only allow raw integer IDs in non-production environments
            if (app()->environment('production')) {
                $this->errorMessage = 'Metode absensi menggunakan kode statis dilarang. Harus scan langsung dari layar guru.';
                $this->showScannerModal = false;
                return;
            }
        }

        $jadwal = JadwalPelajaran::with('mataPelajaran')->find($jadwalId);

        if (!$jadwal) {
            $this->errorMessage = 'Jadwal pelajaran tidak valid atau tidak ditemukan.';
            $this->showScannerModal = false;
            return;
        }

        // Validate if schedule matches student's class
        if ($jadwal->kelas_id !== $this->siswa->kelas_id) {
            $this->errorMessage = "Jadwal ini untuk kelas lain. Anda terdaftar di kelas {$this->siswa->kelas->nama_kelas}.";
            $this->showScannerModal = false;
            return;
        }

        // Check if already present today for this schedule
        $alreadyPresent = Absensi::where('siswa_id', $this->siswa->id)
            ->where('jadwal_id', $jadwalId)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyPresent) {
            $this->errorMessage = "Anda sudah mencatatkan kehadiran pada pelajaran {$jadwal->mataPelajaran->nama_mapel} hari ini.";
            $this->showScannerModal = false;
            return;
        }

        try {
            // Call the database Stored Procedure (with SQLite fallback for tests)
            if (DB::getDriverName() === 'mysql') {
                DB::statement("CALL sp_catat_absen_qr(?, ?, 'Hadir')", [
                    $this->siswa->qr_code_token,
                    $jadwalId
                ]);
            } else {
                \App\Models\Absensi::create([
                    'siswa_id' => $this->siswa->id,
                    'jadwal_id' => $jadwalId,
                    'status' => 'Hadir',
                ]);
                \App\Models\AuditLog::create([
                    'aktivitas' => "Siswa ID {$this->siswa->id} Sukses Scan Absen.",
                ]);
            }

            $this->successMessage = "Absensi Berhasil! Anda tercatat Hadir pada pelajaran {$jadwal->mataPelajaran->nama_mapel}.";
            $this->showScannerModal = false;

        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal mencatatkan absensi: ' . $e->getMessage();
            $this->showScannerModal = false;
        }
    }

    public function simpanFaceEmbedding($embedding)
    {
        try {
            $embeddingArray = is_string($embedding) ? json_decode($embedding, true) : $embedding;
            if (!is_array($embeddingArray) || count($embeddingArray) !== 128) {
                $this->errorMessage = 'Vektor wajah tidak valid. Pastikan wajah terdeteksi dengan jelas.';
                return;
            }

            $this->siswa->update([
                'face_embedding' => $embeddingArray
            ]);

            \App\Models\AuditLog::create([
                'aktivitas' => "Siswa ID {$this->siswa->id} ({$this->siswa->user->name}) sukses mendaftarkan Face ID.",
            ]);

            $this->successMessage = 'Face ID berhasil didaftarkan! Sekarang absensi Anda memerlukan verifikasi wajah.';
            $this->showRegisterFaceModal = false;
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menyimpan Face ID: ' . $e->getMessage();
        }
    }

    public function hapusFaceEmbedding()
    {
        try {
            $this->siswa->update([
                'face_embedding' => null
            ]);
            
            \App\Models\AuditLog::create([
                'aktivitas' => "Siswa ID {$this->siswa->id} ({$this->siswa->user->name}) menghapus data Face ID.",
            ]);

            $this->successMessage = 'Face ID berhasil dihapus.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menghapus Face ID: ' . $e->getMessage();
        }
    }

    private function calculateEuclideanDistance($registered, $scanned)
    {
        $registered = is_string($registered) ? json_decode($registered, true) : $registered;
        $scanned = is_string($scanned) ? json_decode($scanned, true) : $scanned;

        if (!is_array($registered) || !is_array($scanned) || count($registered) !== count($scanned) || count($registered) === 0) {
            return 9.9;
        }

        $sum = 0.0;
        foreach ($registered as $i => $val) {
            $diff = $val - $scanned[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    public function render()
    {
        // Get today's schedule
        $daysMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $today = $daysMap[date('l')];

        $jadwalHariIni = JadwalPelajaran::with('mataPelajaran')
            ->where('kelas_id', $this->siswa->kelas_id)
            ->where('hari', $today)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Get student's attendances recorded today
        $todayAbsensi = Absensi::where('siswa_id', $this->siswa->id)
            ->whereDate('created_at', today())
            ->pluck('jadwal_id')
            ->toArray();

        // Calculate attendance stats this month
        $absensiBulanIni = Absensi::with('jadwalPelajaran')
            ->where('siswa_id', $this->siswa->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();

        $hadirBulanIniCount = 0;
        $tepatWaktuCount = 0;
        $terlambatCount = 0;
        $izinSakitCount = 0;

        foreach ($absensiBulanIni as $absen) {
            if (in_array($absen->status, ['Sakit', 'Izin'])) {
                $izinSakitCount++;
            } elseif ($absen->status === 'Hadir') {
                $hadirBulanIniCount++;

                $jadwal = $absen->jadwalPelajaran;
                if ($jadwal) {
                    $checkinTime = $absen->created_at->format('H:i:s');
                    $startTimePlus15 = date('H:i:s', strtotime($jadwal->jam_mulai . ' +15 minutes'));
                    if ($checkinTime > $startTimePlus15) {
                        $terlambatCount++;
                    } else {
                        $tepatWaktuCount++;
                    }
                } else {
                    $tepatWaktuCount++;
                }
            }
        }

        $kehadiranPercentage = $hadirBulanIniCount > 0 ? round(($hadirBulanIniCount / 20) * 100, 1) : 0;
        if ($kehadiranPercentage > 100) $kehadiranPercentage = 100;

        // Get full schedule for all days of the week
        $jadwalSeminggu = JadwalPelajaran::with('mataPelajaran')
            ->where('kelas_id', $this->siswa->kelas_id)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 WHEN 'Minggu' THEN 7 ELSE 8 END")
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Get all time history
        $riwayatAbsensiLengkap = Absensi::with('jadwalPelajaran.mataPelajaran')
            ->where('siswa_id', $this->siswa->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $defaultSelectedDay = $today === 'Minggu' ? 'Senin' : $today;

        return view('livewire.portal-siswa', [
            'jadwalHariIni' => $jadwalHariIni,
            'todayAbsensi' => $todayAbsensi,
            'hadirBulanIniCount' => $hadirBulanIniCount,
            'tepatWaktuCount' => $tepatWaktuCount,
            'terlambatCount' => $terlambatCount,
            'izinSakitCount' => $izinSakitCount,
            'kehadiranPercentage' => $kehadiranPercentage,
            'jadwalSeminggu' => $jadwalSeminggu,
            'riwayatAbsensiLengkap' => $riwayatAbsensiLengkap,
            'hariIni' => $today,
            'defaultSelectedDay' => $defaultSelectedDay,
        ])->layout('layouts.app');
    }
}

