<?php

namespace App\Filament\Resources\ContentPieces\Tables;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use App\Models\ContentPiece;
use App\Support\Rum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
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
                ImageColumn::make('preview_image_url')
                    ->label('Vista previa')
                    ->height(48)
                    ->square()
                    ->toggleable(),
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
                TextColumn::make('rum')
                    ->label('RUM')
                    ->badge()
                    ->placeholder('—')
                    ->sortable()
                    ->color(fn ($state): string => Rum::color($state !== null ? (float) $state : null))
                    ->formatStateUsing(fn ($state): string => $state !== null ? number_format((float) $state, 1) : '—'),
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
                SelectFilter::make('rum')
                    ->label('RUM')
                    ->options([
                        'alto' => 'Alto (> 7)',
                        'medio' => 'Medio (5–7)',
                        'bajo' => 'Bajo (≤ 5)',
                        'sin' => 'Sin evaluar',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'alto' => $query->where('rum', '>', 7),
                        'medio' => $query->where('rum', '>', 5)->where('rum', '<=', 7),
                        'bajo' => $query->where('rum', '<=', 5),
                        'sin' => $query->whereNull('rum'),
                        default => $query,
                    }),
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
