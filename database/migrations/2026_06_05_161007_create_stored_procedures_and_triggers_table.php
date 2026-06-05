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
        
        // Create procedure
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

        // Create trigger
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
