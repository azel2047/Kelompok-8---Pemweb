<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MataPelajaranResource\Pages;
use App\Models\MataPelajaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload; // <-- Import Utama File Upload Manual

class MataPelajaranResource extends Resource
{
    protected static ?string $model = MataPelajaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Mata Pelajaran';
    protected static ?string $modelLabel = 'Mata Pelajaran';
    protected static ?string $pluralModelLabel = 'Mata Pelajaran';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_mapel')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_mapel')
                    ->required()
                    ->maxLength(255),
                
                // INPUT FILE UPLOAD UTAMA DARI LAPTOP LU
                FileUpload::make('deskripsi_mapel_opsional')
                    ->label('Upload Banner Pengumuman Sekolah')
                    ->image() 
                    ->disk('public_custom') 
                    ->directory('images') 
                    ->preserveFilenames() 
                    ->visibility('public')
                    ->maxSize(2048), // Batas aman 2MB
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mapel')->searchable(),
                Tables\Columns\TextColumn::make('kode_mapel')->searchable(),
                Tables\Columns\ImageColumn::make('deskripsi_mapel_opsional')
                    ->label('Banner')
                    ->disk('public_custom'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMataPelajarans::route('/'),
            'create' => Pages\CreateMataPelajaran::route('/create'),
            'edit' => Pages\EditMataPelajaran::route('/{record}/edit'),
        ];
    }
}