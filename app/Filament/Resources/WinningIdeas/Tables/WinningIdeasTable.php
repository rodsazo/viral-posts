<?php

namespace App\Filament\Resources\WinningIdeas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WinningIdeasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('viral_mechanism')
                    ->label('Mecanismo')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('herasTemplate.number')
                    ->label('Plantilla')
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : null)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('questions_count')
                    ->label('Preguntas')
                    ->counts('questions')
                    ->badge(),
                TextColumn::make('content_pieces_count')
                    ->label('Piezas')
                    ->counts('contentPieces')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
