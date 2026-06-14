<?php

namespace App\Filament\Resources\HerasTemplates\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HerasTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('number')
            ->columns([
                TextColumn::make('number')
                    ->label('#')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('suggested_format')
                    ->label('Formato sugerido')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('viral_mechanism')
                    ->label('Mecanismo')
                    ->badge()
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('winning_ideas_count')
                    ->label('Ideas')
                    ->counts('winningIdeas')
                    ->badge()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
