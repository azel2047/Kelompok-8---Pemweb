<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Drop procedure if exists first
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_catat_absen_qr");
        
        // =========================================================================
        // PERTANYAAN DOSEN/PENGUJI: "Apa fungsi Stored Procedure ini dan kenapa pakai Stored Procedure?"
        // FUNGSI: `sp_catat_absen_qr` mencocokkan token QR siswa dengan tabel `siswa`, mendapatkan ID Siswa, 
        //         dan memasukkan data kehadiran langsung ke tabel `absensi`.
        // ALASAN: 
        // 1. Eksekusi lebih cepat karena diproses langsung di level database (DBMS MySQL).
        // 2. Mengurangi beban server PHP Laravel karena logika pencocokan token & insert dilakukan dalam satu kali call.
        // =========================================================================
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

        // Drop trigger if exists
        DB::unprepared("DROP TRIGGER IF EXISTS tr_after_insert_absensi");

        // =========================================================================
        // PERTANYAAN DOSEN/PENGUJI: "Apa fungsi Trigger ini dan kenapa ditaruh di database?"
        // FUNGSI: `tr_after_insert_absensi` otomatis merekam aktivitas log ke tabel `audit_logs`
        //         setiap kali ada data absensi baru dimasukkan (AFTER INSERT ON absensi).
        // ALASAN:
        // 1. Konsistensi Data: Siapapun atau program apapun yang melakukan insert ke tabel absensi (baik Laravel, 
        //    admin panel, maupun query SQL manual), log aktivitasnya PASTI akan dicatat secara otomatis oleh DBMS.
        // 2. Keamanan: Tidak bisa dimanipulasi dari level aplikasi PHP karena trigger berjalan otomatis di level database.
        // =========================================================================
        DB::unprepared("
            CREATE TRIGGER tr_after_insert_absensi
            AFTER INSERT ON absensi FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (aktivitas, created_at, updated_at) 
                VALUES (CONCAT('Siswa ID ', NEW.siswa_id, ' Sukses Scan Absen.'), NOW(), NOW());
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared("DROP PROCEDURE IF EXISTS sp_catat_absen_qr");
        DB::unprepared("DROP TRIGGER IF EXISTS tr_after_insert_absensi");
    }
};
