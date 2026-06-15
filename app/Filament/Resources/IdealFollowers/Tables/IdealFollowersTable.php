<?php

namespace App\Filament\Resources\IdealFollowers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IdealFollowersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Perfil')
                    ->searchable(),
                TextColumn::make('questions_count')
                    ->label('Preguntas')
                    ->counts('questions')
                    ->badge(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Aún no hay seguidores ideales')
            ->emptyStateDescription('Define a quién le hablas: crea tu primer perfil de audiencia.')
            ->emptyStateIcon('heroicon-o-users')
            ->filters([
                TernaryFilter::make('questions')
                    ->label('Preguntas')
                    ->placeholder('Todos')
                    ->trueLabel('Con preguntas')
                    ->falseLabel('Sin preguntas')
                    ->queries(
                        true: fn (Builder $query) => $query->has('questions'),
                        false: fn (Builder $query) => $query->doesntHave('questions'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
