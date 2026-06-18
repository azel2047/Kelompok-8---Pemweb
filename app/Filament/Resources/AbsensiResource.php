<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Kehadiran Siswa';
    protected static ?string $modelLabel = 'Absensi';
    protected static ?string $pluralModelLabel = 'Kehadiran Siswa';
    protected static string | \UnitEnum | null $navigationGroup = 'Menu Absensi';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('siswa_id')
                    ->relationship('siswa', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->user->name} ({$record->kelas->nama_kelas})")
                    ->label('Siswa')
                    ->searchable()
                    ->required(),
                
                Forms\Components\Select::make('jadwal_id')
                    ->relationship('jadwalPelajaran', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->mataPelajaran->nama_mapel} - {$record->kelas->nama_kelas} ({$record->hari}, " . substr($record->jam_mulai, 0, 5) . "-" . substr($record->jam_selesai, 0, 5) . ")")
                    ->label('Jadwal Pelajaran')
                    ->searchable()
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Alfa' => 'Alfa',
                    ])
                    ->label('Status')
                    ->default('Hadir')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('siswa.kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jadwalPelajaran.mataPelajaran.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Sakit' => 'info',
                        'Izin' => 'warning',
                        'Alfa' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Absen')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kelas')
                    ->relationship('siswa.kelas', 'nama_kelas')
                    ->label('Kelas'),
                
                Tables\Filters\SelectFilter::make('mataPelajaran')
                    ->relationship('jadwalPelajaran.mataPelajaran', 'nama_mapel')
                    ->label('Mata Pelajaran'),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Alfa' => 'Alfa',
                    ])
                    ->label('Status Kehadiran'),
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
            'index' => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}
