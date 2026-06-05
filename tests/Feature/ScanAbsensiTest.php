<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\JadwalPelajaran;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\AuditLog;
use Livewire\Livewire;
use App\Livewire\PortalSiswa;
use Illuminate\Support\Facades\DB;

class ScanAbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'mysql') {
            DB::unprepared("DROP PROCEDURE IF EXISTS sp_catat_absen_qr");
            DB::unprepared("
                CREATE PROCEDURE sp_catat_absen_qr(IN p_token VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN p_jadwal_id INT, IN p_status VARCHAR(10))
                BEGIN
                    DECLARE v_siswa_id INT;
                    SELECT id INTO v_siswa_id FROM siswa WHERE qr_code_token = p_token COLLATE utf8mb4_unicode_ci LIMIT 1;
                    IF v_siswa_id IS NOT NULL THEN
                        INSERT INTO absensi (siswa_id, jadwal_id, status, created_at, updated_at) 
                        VALUES (v_siswa_id, p_jadwal_id, p_status, NOW(), NOW());
                    END IF;
                END
            ");

            DB::unprepared("DROP TRIGGER IF EXISTS tr_after_insert_absensi");
            DB::unprepared("
                CREATE TRIGGER tr_after_insert_absensi
                AFTER INSERT ON absensi FOR EACH ROW
                BEGIN
                    INSERT INTO audit_logs (aktivitas, created_at, updated_at) 
                    VALUES (CONCAT('Siswa ID ', NEW.siswa_id, ' Sukses Scan Absen.'), NOW(), NOW());
                END
            ");
        }
    }

    public function test_student_can_scan_class_qr_code_and_record_attendance()
    {
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1']);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Pemrograman Web', 'kode_mapel' => 'PW001']);
        
        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'Jumat',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $userSiswa = User::create([
            'name' => 'Ahmad Roni',
            'email' => 'siswa@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas->id,
            'nisn' => '1234567890',
            'qr_code_token' => 'TOKEN_AHMAD_RONI_99',
        ]);

        // Login as the student
        $this->actingAs($userSiswa);

        // Test Livewire component
        Livewire::test(PortalSiswa::class)
            ->call('prosesScanQrKelas', $jadwal->id)
            ->assertHasNoErrors()
            ->assertSet('successMessage', "Absensi Berhasil! Anda tercatat Hadir pada pelajaran {$mapel->nama_mapel}.");

        // Verify DB recorded it
        $this->assertDatabaseHas('absensi', [
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'Hadir',
        ]);

        // Verify trigger created audit log
        $this->assertDatabaseHas('audit_logs', [
            'aktivitas' => 'Siswa ID ' . $siswa->id . ' Sukses Scan Absen.',
        ]);
    }

    public function test_student_cannot_scan_qr_code_for_different_class()
    {
        $kelas1 = Kelas::create(['nama_kelas' => 'XII RPL 1']);
        $kelas2 = Kelas::create(['nama_kelas' => 'XII RPL 2']); // different class
        $mapel = MataPelajaran::create(['nama_mapel' => 'Pemrograman Web', 'kode_mapel' => 'PW001']);
        
        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas2->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'Jumat',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $userSiswa = User::create([
            'name' => 'Ahmad Roni',
            'email' => 'siswa@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas1->id,
            'nisn' => '1234567890',
            'qr_code_token' => 'TOKEN_AHMAD_RONI_99',
        ]);

        $this->actingAs($userSiswa);

        Livewire::test(PortalSiswa::class)
            ->call('prosesScanQrKelas', $jadwal->id)
            ->assertHasNoErrors()
            ->assertSet('errorMessage', "Jadwal ini untuk kelas lain. Anda terdaftar di kelas {$kelas1->nama_kelas}.");

        // Verify DB has no absensi entry
        $this->assertDatabaseMissing('absensi', [
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
        ]);
    }

    public function test_student_can_scan_valid_dynamic_qr_payload()
    {
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1']);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Pemrograman Web', 'kode_mapel' => 'PW001']);
        
        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'Jumat',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $userSiswa = User::create([
            'name' => 'Ahmad Roni',
            'email' => 'siswa@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas->id,
            'nisn' => '1234567890',
            'qr_code_token' => 'TOKEN_AHMAD_RONI_99',
        ]);

        $this->actingAs($userSiswa);

        // Generate a valid dynamic payload
        $timeWindow = floor(time() / 15);
        $hash = substr(hash_hmac('sha256', $jadwal->id . '|' . $timeWindow, config('app.key')), 0, 16);
        $payload = $jadwal->id . '|' . $timeWindow . '|' . $hash;

        Livewire::test(PortalSiswa::class)
            ->call('prosesScanQrKelas', $payload)
            ->assertHasNoErrors()
            ->assertSet('successMessage', "Absensi Berhasil! Anda tercatat Hadir pada pelajaran {$mapel->nama_mapel}.");

        $this->assertDatabaseHas('absensi', [
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
        ]);
    }

    public function test_student_cannot_scan_invalid_signature_dynamic_qr()
    {
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1']);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Pemrograman Web', 'kode_mapel' => 'PW001']);
        
        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'Jumat',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $userSiswa = User::create([
            'name' => 'Ahmad Roni',
            'email' => 'siswa@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas->id,
            'nisn' => '1234567890',
            'qr_code_token' => 'TOKEN_AHMAD_RONI_99',
        ]);

        $this->actingAs($userSiswa);

        // Payload with invalid signature
        $timeWindow = floor(time() / 15);
        $payload = $jadwal->id . '|' . $timeWindow . '|invalidhash12345';

        Livewire::test(PortalSiswa::class)
            ->call('prosesScanQrKelas', $payload)
            ->assertHasNoErrors()
            ->assertSet('errorMessage', 'QR Code tidak valid (tanda tangan tidak cocok).');

        $this->assertDatabaseMissing('absensi', [
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
        ]);
    }

    public function test_student_cannot_scan_expired_dynamic_qr()
    {
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1']);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Pemrograman Web', 'kode_mapel' => 'PW001']);
        
        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'Jumat',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $userSiswa = User::create([
            'name' => 'Ahmad Roni',
            'email' => 'siswa@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas->id,
            'nisn' => '1234567890',
            'qr_code_token' => 'TOKEN_AHMAD_RONI_99',
        ]);

        $this->actingAs($userSiswa);

        // Payload generated 10 minutes ago (40 windows ago)
        $timeWindow = floor(time() / 15) - 40; 
        $hash = substr(hash_hmac('sha256', $jadwal->id . '|' . $timeWindow, config('app.key')), 0, 16);
        $payload = $jadwal->id . '|' . $timeWindow . '|' . $hash;

        Livewire::test(PortalSiswa::class)
            ->call('prosesScanQrKelas', $payload)
            ->assertHasNoErrors()
            ->assertSet('errorMessage', 'QR Code sudah kedaluwarsa. Silakan scan QR Code terbaru dari layar guru.');

        $this->assertDatabaseMissing('absensi', [
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
        ]);
    }
}

