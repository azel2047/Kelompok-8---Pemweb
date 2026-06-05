<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Kelas
        $kelas1 = \App\Models\Kelas::create(['nama_kelas' => 'XII RPL 1']);
        $kelas2 = \App\Models\Kelas::create(['nama_kelas' => 'XII RPL 2']);

        // Create Mata Pelajaran
        $mapel1 = \App\Models\MataPelajaran::create([
            'nama_mapel' => 'Pemrograman Web',
            'kode_mapel' => 'PW001',
        ]);
        $mapel2 = \App\Models\MataPelajaran::create([
            'nama_mapel' => 'Basis Data',
            'kode_mapel' => 'BD002',
        ]);

        // Create Jadwal Pelajaran
        $jadwal1 = \App\Models\JadwalPelajaran::create([
            'kelas_id' => $kelas1->id,
            'mata_pelajaran_id' => $mapel1->id,
            'hari' => 'Jumat',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);
        $jadwal2 = \App\Models\JadwalPelajaran::create([
            'kelas_id' => $kelas1->id,
            'mata_pelajaran_id' => $mapel2->id,
            'hari' => 'Jumat',
            'jam_mulai' => '10:15:00',
            'jam_selesai' => '12:15:00',
        ]);

        // Create Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        // Create Guru
        User::create([
            'name' => 'Pak Budi Guru',
            'email' => 'guru@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Guru',
        ]);

        // Create Siswa 1
        $userSiswa1 = User::create([
            'name' => 'Ahmad Roni',
            'email' => 'siswa1@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);
        \App\Models\Siswa::create([
            'user_id' => $userSiswa1->id,
            'kelas_id' => $kelas1->id,
            'nisn' => '1234567890',
            'qr_code_token' => 'TOKEN_AHMAD_RONI_99',
            'foto_profil' => null,
        ]);

        // Create Siswa 2
        $userSiswa2 = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siswa2@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);
        \App\Models\Siswa::create([
            'user_id' => $userSiswa2->id,
            'kelas_id' => $kelas1->id,
            'nisn' => '1234567891',
            'qr_code_token' => 'TOKEN_SITI_AMINAH_88',
            'foto_profil' => null,
        ]);
    }
}
