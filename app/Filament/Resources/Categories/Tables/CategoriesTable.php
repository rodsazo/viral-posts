<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ColorColumn::make('color')
                    ->label('Color'),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('questions_count')
                    ->label('Preguntas')
                    ->counts('questions')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Sin categorías todavía')
            ->emptyStateDescription('Crea categorías para organizar las preguntas de tu audiencia.')
            ->emptyStateIcon('heroicon-o-tag')
            ->filters([
                TernaryFilter::make('questions')
                    ->label('Preguntas')
                    ->placeholder('Todas')
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
