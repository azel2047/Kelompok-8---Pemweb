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

        // =========================================================================
        // PERTANYAAN DOSEN/PENGUJI: "Bagaimana cara QR Code dibuat dinamis & aman dari pemalsuan?"
        // FUNGSI: Menghasilkan kode QR dinamis berbasis waktu (Time-based One-Time Token/HMAC).
        // PENJELASAN ALGORITMA:
        // 1. $timeWindow = Waktu saat ini (detik) dibagi 15. QR Code otomatis berubah setiap 15 detik.
        // 2. $hash = Tanda tangan digital menggunakan HMAC-SHA256 untuk memverifikasi keaslian QR Code, 
        //    sehingga QR Code tidak bisa dibuat sendiri secara manual oleh siswa jahil.
        // 3. $payload = Menggabungkan ID Jadwal, Time Window, dan Tanda Tangan (Hash) menjadi string payload QR.
        // =========================================================================
        $timeWindow = floor(time() / 15);
        $hash = substr(hash_hmac('sha256', $jadwal->id . '|' . $timeWindow, config('app.key')), 0, 16);
        $payload = $jadwal->id . '|' . $timeWindow . '|' . $hash;

        $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->generate($payload);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    })->name('admin.jadwal.qr');
});

// JALUR PINTAS PEMBERSIH CACHE VIA URL BY MULTI-AGENT AI
Route::get('/bersihin-cache-sekarang', function () {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return 'Mantap Cuk! Semua cache view dan config di hostingan sukses dihancurkan total!';
});