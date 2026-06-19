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

    // 1. INPUT FORM UTAMA (SUDAH STERIL TANPA UPLOADER)
    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_mapel')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('kode_mapel')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    // 2. LIST TABEL UTAMA (SUDAH STERIL TANPA KOLOM BANNER)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mapel')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('kode_mapel')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // 🔥 FITUR UTAMA: Tombol QR Absen tetep dipertahankan jangan sampai hilang
                \Filament\Actions\Action::make('showQr')
                    ->label('QR Absen')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->modalHeading(fn ($record) => "QR Code Absensi: {$record->nama_mapel}")
                    ->modalContent(fn ($record) => view('filament.components.qr-modal', ['record' => $record]))
                    ->modalSubmitAction(false),
                    
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
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