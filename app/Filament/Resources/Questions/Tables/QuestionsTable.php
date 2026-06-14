<?php

namespace App\Filament\Resources\Questions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $table
            ->columns([
                TextColumn::make('body')
                    ->label('Pregunta')
                    ->wrap()
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('idealFollower.name')
                    ->label('Seguidor ideal')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'gray')
                    ->placeholder('Sin categoría')
                    ->sortable(),
                TextColumn::make('beliefs_count')
                    ->label('Mitos/Verdades')
                    ->counts('beliefs')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Criterio B4: filtrar preguntas por Seguidor Ideal y por Categoría.
                SelectFilter::make('ideal_follower_id')
                    ->label('Seguidor ideal')
                    ->relationship('idealFollower', 'name', $scopeToTenant)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name', $scopeToTenant)
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('beliefs')
                    ->label('Mitos/Verdades')
                    ->placeholder('Todas')
                    ->trueLabel('Con creencias')
                    ->falseLabel('Sin creencias')
                    ->queries(
                        true: fn (Builder $query) => $query->has('beliefs'),
                        false: fn (Builder $query) => $query->doesntHave('beliefs'),
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
