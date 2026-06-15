<?php

namespace App\Filament\Resources\ContentPieces\Tables;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use App\Models\ContentPiece;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
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
                TextColumn::make('objective')
                    ->label('Objetivo')
                    ->badge()
                    ->placeholder('—')
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
                    ->label('Enlace')
                    ->icon(fn ($record): ?string => filled($record->url) ? 'heroicon-m-link' : null)
                    ->color('info')
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true)
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Publicada')
                    ->date()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Sin piezas de contenido')
            ->emptyStateDescription('Crea tu primera pieza y muévela por el pipeline de producción.')
            ->emptyStateIcon('heroicon-o-film')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ContentStatus::class),
                SelectFilter::make('objective')
                    ->label('Objetivo')
                    ->options(ContentObjective::class),
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
                Action::make('publish')
                    ->label('Marcar publicada')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (ContentPiece $record): bool => $record->status !== ContentStatus::Publicada)
                    ->requiresConfirmation()
                    ->modalHeading('Marcar pieza como publicada')
                    ->modalDescription('Se marcará como publicada con la fecha de hoy.')
                    ->action(function (ContentPiece $record): void {
                        $record->update([
                            'status' => ContentStatus::Publicada,
                            'published_at' => $record->published_at ?? now(),
                        ]);

                        Notification::make()
                            ->title("«{$record->title}» marcada como publicada")
                            ->success()
                            ->send();
                    }),
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
