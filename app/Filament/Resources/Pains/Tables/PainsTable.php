<?php

namespace App\Filament\Resources\Pains\Tables;

use App\Enums\PainType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PainsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('body')
                    ->label('Enunciado')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('idealFollower.name')
                    ->label('Seguidor')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Sin seguidor')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(PainType::class),
                SelectFilter::make('ideal_follower_id')
                    ->label('Seguidor')
                    ->relationship('idealFollower', 'name'),
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
