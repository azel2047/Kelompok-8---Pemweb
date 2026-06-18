<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mata_pelajaran', 'deskripsi_mapel_opsional')) {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->longText('deskripsi_mapel_opsional')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mata_pelajaran', 'deskripsi_mapel_opsional')) {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->dropColumn('deskripsi_mapel_opsional');
            });
        }
    }
};
