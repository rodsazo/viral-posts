<?php

namespace App\Filament\Resources\ContentPieces\Tables;

use App\Enums\ContentFormat;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContentPiecesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('format')
                    ->label('Formato')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('winningIdea.title')
                    ->label('Idea ganadora')
                    ->placeholder('Sin idea')
                    ->toggleable(),
                TextColumn::make('rating')
                    ->label('Calificación')
                    ->badge()
                    ->placeholder('—')
                    ->sortable(),
                IconColumn::make('url')
                    ->label('Publicada')
                    ->boolean()
                    ->state(fn ($record): bool => filled($record->url))
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ContentStatus::class),
                SelectFilter::make('format')
                    ->label('Formato')
                    ->options(ContentFormat::class),
                SelectFilter::make('rating')
                    ->label('Calificación')
                    ->options(ContentRating::class),
                TernaryFilter::make('winning_idea_id')
                    ->label('Idea ganadora')
                    ->placeholder('Todas')
                    ->trueLabel('Con idea')
                    ->falseLabel('Sueltas (sin idea)')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('winning_idea_id'),
                        false: fn (Builder $query) => $query->whereNull('winning_idea_id'),
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
