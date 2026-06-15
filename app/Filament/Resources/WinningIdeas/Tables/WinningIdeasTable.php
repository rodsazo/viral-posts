<?php

namespace App\Filament\Resources\WinningIdeas\Tables;

use App\Enums\ViralMechanism;
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

class WinningIdeasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
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
                IconColumn::make('reference_url')
                    ->label('Referencia')
                    ->icon(fn ($record): ?string => filled($record->reference_url) ? 'heroicon-m-link' : null)
                    ->color('info')
                    ->url(fn ($record) => $record->reference_url, shouldOpenInNewTab: true)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Aún no hay ideas ganadoras')
            ->emptyStateDescription('Convierte tus preguntas en conceptos de contenido listos para producir.')
            ->emptyStateIcon('heroicon-o-light-bulb')
            ->filters([
                SelectFilter::make('viral_mechanism')
                    ->label('Mecanismo')
                    ->options(ViralMechanism::class),
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
