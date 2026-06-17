<?php

namespace App\Filament\Resources\Niches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NichesTable
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
                TextColumn::make('viral_referents_count')
                    ->label('Referentes')
                    ->counts('viralReferents')
                    ->badge(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(60)
                    ->toggleable(),
            ])
            ->emptyStateHeading('Sin nichos todavía')
            ->emptyStateDescription('Crea nichos para clasificar a tus referentes virales.')
            ->emptyStateIcon('heroicon-o-hashtag')
            ->filters([
                //
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
