<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengumumanResource\Pages;
use App\Models\Pengumuman;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengumumanResource extends Resource
{
    protected static ?string $model = Pengumuman::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';
    
    protected static ?string $navigationLabel = 'Pengumuman Sekolah';
    
    protected static ?string $pluralLabel = 'Pengumuman';

    public static function form(Schema $form): Schema
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('judul')
                ->required()
                ->maxLength(255),
                
            // 🔥 JANGKAR UTAMA: Field tersembunyi agar Livewire tahu kolom ini valid untuk disimpan
            Forms\Components\Hidden::make('banner_base64')
                ->dehydrated(true), // Memaksa data ikut dikirim saat save
                
            \Filament\Schemas\Components\View::make('filament.components.pengumuman-uploader')
                ->columnSpanFull(),
                
            Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->label('Tampilkan di Portal'),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengumunans::route('/'),
            'create' => Pages\CreatePengumuman::route('/create'),
            'edit' => Pages\EditPengumuman::route('/{record}/edit'),
        ];
    }
}