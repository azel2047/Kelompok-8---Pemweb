<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikuler';

    protected $fillable = ['nama_ekskul', 'pembina'];

    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'siswa_ekstrakurikuler', 'ekstrakurikuler_id', 'siswa_id')
                    ->withTimestamps();
    }
}
