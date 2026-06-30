<?php

namespace App\Filament\Resources\WinningIdeas\Tables;

use App\Enums\IdeaStatus;
use App\Enums\ViralMechanism;
use App\Models\WinningIdea;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('viral_mechanism')
                    ->label('Mecanismo')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('herasTemplate.name')
                    ->label('Plantilla')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('content_pieces_count')
                    ->label('Piezas')
                    ->counts('contentPieces')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('validation')
                    ->label('Validación')
                    ->badge()
                    ->state(fn (WinningIdea $record) => $record->validationStatus())
                    ->tooltip(fn (WinningIdea $record): string => ($record->example_urls ? count($record->example_urls) : 0).' ejemplo(s) real(es)'),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Aún no hay ideas ganadoras')
            ->emptyStateDescription('Describe formatos de contenido que ya funcionan, listos para producir.')
            ->emptyStateIcon('heroicon-o-light-bulb')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(IdeaStatus::class),
                SelectFilter::make('viral_mechanism')
                    ->label('Mecanismo')
                    ->options(ViralMechanism::class),
                TernaryFilter::make('validated')
                    ->label('Validación')
                    ->placeholder('Todas')
                    ->trueLabel('Validadas (con ejemplos)')
                    ->falseLabel('Pendientes (sin ejemplos)')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('example_urls')->where('example_urls', '!=', '[]'),
                        false: fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereNull('example_urls')->orWhere('example_urls', '[]')),
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
