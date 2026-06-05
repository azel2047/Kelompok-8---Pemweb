<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Login Siswa');
    }

    public function test_siswa_can_login_and_is_redirected_to_portal()
    {
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1']);
        
        $user = User::create([
            'name' => 'Ahmad Roni',
            'email' => 'siswa@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);

        $siswa = Siswa::create([
            'user_id' => $user->id,
            'kelas_id' => $kelas->id,
            'nisn' => '1234567890',
            'qr_code_token' => 'TOKEN_TEST_123',
        ]);

        $response = $this->post('/login', [
            'email' => 'siswa@absen.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/portal-siswa');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_filament_on_login()
    {
        $user = User::create([
            'name' => 'Administrator',
            'email' => 'admin@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@absen.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_siswa_cannot_access_filament_admin_panel()
    {
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1']);
        $user = User::create([
            'name' => 'Ahmad Roni',
            'email' => 'siswa@absen.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa',
        ]);
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'kelas_id' => $kelas->id,
            'nisn' => '1234567890',
            'qr_code_token' => 'TOKEN_TEST_123',
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin');
        $response->assertStatus(403);
    }
}
