<?php

namespace App\Filament\Resources\ViralReferents\Tables;

use App\Models\Niche;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ViralReferentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Referente')
                    ->searchable(),
                TextColumn::make('niche.name')
                    ->label('Nicho')
                    ->badge()
                    ->color(fn ($record) => $record->niche?->color ?? 'gray')
                    ->placeholder('Sin nicho')
                    ->sortable(),
                TextColumn::make('heras_templates_count')
                    ->label('Plantillas')
                    ->counts('herasTemplates')
                    ->badge(),
                IconColumn::make('instagram_url')
                    ->label('Instagram')
                    ->icon(fn ($record): ?string => filled($record->instagram_url) ? 'heroicon-m-link' : null)
                    ->color('info')
                    ->url(fn ($record) => $record->instagram_url, shouldOpenInNewTab: true)
                    ->toggleable(),
            ])
            ->emptyStateHeading('Sin referentes virales')
            ->emptyStateDescription('Registra a los creadores cuyo contenido te sirve de referencia.')
            ->emptyStateIcon('heroicon-o-star')
            ->filters([
                SelectFilter::make('niche_id')
                    ->label('Nicho')
                    ->options(fn (): array => Niche::query()->orderBy('name')->pluck('name', 'id')->all()),
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
