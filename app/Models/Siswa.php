<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'kelas_id',
        'nisn',
        'qr_code_token',
        'foto_profil',
        'face_embedding',
    ];

    // =========================================================================
    // PERTANYAAN DOSEN/PENGUJI: "Bagaimana data wajah (Face Embedding) disimpan dan diolah?"
    // FUNGSI: Meng-cast kolom `face_embedding` (tipe JSON di database) menjadi array PHP otomatis saat diakses.
    // =========================================================================
    protected $casts = [
        'face_embedding' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'siswa_id');
    }

    // =========================================================================
    // PERTANYAAN DOSEN/PENGUJI: "Bagaimana relasi ekstrakurikuler dibuat dan apa nama tabel pivotnya?"
    // FUNGSI: Relasi Many-to-Many (Banyak ke Banyak) antara Siswa dan Ekstrakurikuler.
    // PENJELASAN: 
    // 1. Menggunakan method `belongsToMany`.
    // 2. Nama tabel pivot perantara adalah `siswa_ekstrakurikuler`.
    // 3. Menghubungkan foreign key `siswa_id` dan `ekstrakurikuler_id` dengan otomatis menyimpan timestamps.
    // =========================================================================
    public function ekstrakurikulers(): BelongsToMany
    {
        return $this->belongsToMany(Ekstrakurikuler::class, 'siswa_ekstrakurikuler', 'siswa_id', 'ekstrakurikuler_id')
                    ->withTimestamps();
    }

    public function ekstrakurikuler(): BelongsToMany
    {
        return $this->ekstrakurikulers();
    }
}
