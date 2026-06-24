<?php

namespace App\Filament\Resources\Beliefs\Tables;

use App\Enums\BeliefType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BeliefsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),
                TextColumn::make('statement')
                    ->label('Afirmación')
                    ->wrap()
                    ->limit(90)
                    ->searchable(),
                TextColumn::make('idealFollower.name')
                    ->label('Seguidor ideal')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Sin mitos ni verdades')
            ->emptyStateDescription('Captura las creencias que quieres desmentir o impulsar en tu nicho.')
            ->emptyStateIcon('heroicon-o-scale')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(BeliefType::class),
                SelectFilter::make('ideal_follower_id')
                    ->label('Seguidor ideal')
                    ->relationship('idealFollower', 'name', fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()))
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
