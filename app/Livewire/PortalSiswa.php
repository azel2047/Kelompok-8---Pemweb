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

    public function prosesScanQrKelas($payload)
    {
        $this->successMessage = '';
        $this->errorMessage = '';

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

        // Get recent attendance logs for this student
        $riwayatAbsensi = Absensi::with('jadwalPelajaran.mataPelajaran')
            ->where('siswa_id', $this->siswa->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.portal-siswa', [
            'jadwalHariIni' => $jadwalHariIni,
            'riwayatAbsensi' => $riwayatAbsensi,
            'hariIni' => $today,
            'todayAbsensi' => $todayAbsensi,
        ])->layout('layouts.app');
    }
}

