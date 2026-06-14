<?php

namespace App\Filament\Resources\Beliefs\Tables;

use App\Enums\BeliefType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BeliefsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                TextColumn::make('questions_count')
                    ->label('Preguntas')
                    ->counts('questions')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(BeliefType::class),
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
