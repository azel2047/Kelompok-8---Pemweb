<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MataPelajaranResource\Pages;
use App\Models\MataPelajaran;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MataPelajaranResource extends Resource
{
    protected static ?string $model = MataPelajaran::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';

    // 1. INPUT FORM UTAMA
    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_mapel')->required(),
                Forms\Components\TextInput::make('kode_mapel')->required(),
                
                // 🔥 SINKRONISASI MURNI VIA LIVEWIRE STATE (TIDAK PAKAI REQUEST INPUT MANUAL AGAR COCOK DENGAN @this.set)
                Forms\Components\TextInput::make('deskripsi_mapel_opsional')
                    ->label('Upload Banner')
                    ->view('filament.components.custom-uploader') 
                    ->columnSpanFull()
                    ->default('')
                    ->dehydrateStateUsing(function ($state) {
                        // Cukup kembalikan state apa adanya karena JavaScript sudah otomatis menyuntikkan Base64 ke sini
                        return $state ?? '';
                    }),
            ]);
    }

    // 2. LIST TABEL UTAMA (SUDAH DI-BYPASS)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mapel')->searchable(),
                Tables\Columns\TextColumn::make('kode_mapel')->searchable(),
                
                // 🔥 Render aman di tabel admin (Mendukung path lama ataupun Base64 baru)
                Tables\Columns\TextColumn::make('deskripsi_mapel_opsional')
                    ->label('Banner')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '<span class="text-xs text-gray-400">No Image</span>';
                        }
                        
                        // Cek apakah isinya base64 atau path file biasa
                        $url = str_starts_with($state, 'data:image') ? $state : asset($state);
                        return '<img src="' . $url . '" class="rounded object-cover" style="height: 40px; width: auto;" alt="Banner">';
                    }),
            ])
            ->actions([
                \Filament\Actions\Action::make('showQr')
                    ->label('QR Absen')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->modalHeading(fn ($record) => "QR Code Absensi: {$record->nama_mapel}")
                    ->modalContent(fn ($record) => view('filament.components.qr-modal', ['record' => $record]))
                    ->modalSubmitAction(false),
                    
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMataPelajarans::route('/'),
            'edit' => Pages\EditMataPelajaran::route('/{record}/edit'),
        ];
    }
}