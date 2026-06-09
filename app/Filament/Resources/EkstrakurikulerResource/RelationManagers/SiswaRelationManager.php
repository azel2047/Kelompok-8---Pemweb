<?php

namespace App\Filament\Resources\EkstrakurikulerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiswaRelationManager extends RelationManager
{
    protected static string $relationship = 'siswa';

    protected static ?string $title = 'Anggota Siswa';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nisn')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->with('user'))
                    ->recordSelect(fn (Forms\Components\Select $select) => $select->getOptionLabelFromRecordUsing(
                        fn (Model $record) => "{$record->user->name} ({$record->nisn})"
                    ))
                    ->label('Tambah Anggota (Attach)'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Keluarkan (Detach)'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
