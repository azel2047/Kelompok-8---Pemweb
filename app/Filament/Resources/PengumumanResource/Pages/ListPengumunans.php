<?php

namespace App\Filament\Resources\PengumumanResource\Pages;

use App\Filament\Resources\PengumumanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengumunans extends ListRecords
{
    protected static string $resource = PengumumanResource::class;

    // 🔥 BLOK INI YANG WAJIB ADA BIAR TOMBOL "NEW" NONGOOL DI ATAS TABEL
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Pengumuman'),
        ];
    }
}