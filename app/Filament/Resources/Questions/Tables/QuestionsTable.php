<?php

namespace App\Filament\Resources\Questions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $table
            ->defaultSort('created_at', 'desc')
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
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Aún no hay preguntas')
            ->emptyStateDescription('Registra las dudas reales de tu audiencia: son la materia prima del contenido.')
            ->emptyStateIcon('heroicon-o-question-mark-circle')
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
