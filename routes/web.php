<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Livewire\PortalSiswa;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/portal-siswa', PortalSiswa::class)->name('portal.siswa');

    // Dynamic QR generation endpoint for teacher/admin
    Route::get('/admin/jadwal-pelajaran/{jadwal}/qr', function (\App\Models\JadwalPelajaran $jadwal) {
        // Only allow teachers or admins to view/generate this
        if (!auth()->user() || !in_array(auth()->user()->role, ['Admin', 'Guru'])) {
            abort(403, 'Akses ditolak.');
        }

        $timeWindow = floor(time() / 15);
        $hash = substr(hash_hmac('sha256', $jadwal->id . '|' . $timeWindow, config('app.key')), 0, 16);
        $payload = $jadwal->id . '|' . $timeWindow . '|' . $hash;

        $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate($payload);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    })->name('admin.jadwal.qr');
});


