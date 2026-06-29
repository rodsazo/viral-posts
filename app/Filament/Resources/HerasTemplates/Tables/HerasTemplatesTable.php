<?php

namespace App\Filament\Resources\HerasTemplates\Tables;

use App\Models\Niche;
use App\Models\ViralReferent;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HerasTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('preview_image_url')
                    ->label('Vista previa')
                    ->height(56)
                    ->square()
                    ->defaultImageUrl(null)
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('viralReferent.name')
                    ->label('Referente')
                    ->badge()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('viralReferent.niche.name')
                    ->label('Nicho')
                    ->badge()
                    ->color(fn ($record) => $record->viralReferent?->niche?->color ?? 'gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('viral_mechanism')
                    ->label('Mecanismo')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('winning_ideas_count')
                    ->label('Ideas')
                    ->counts('winningIdeas')
                    ->badge()
                    ->toggleable(),
            ])
            ->emptyStateHeading('Sin plantillas')
            ->emptyStateDescription('Ejecuta el seeder para cargar las 30 plantillas Heras.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->filters([
                SelectFilter::make('viral_referent_id')
                    ->label('Referente viral')
                    ->options(fn (): array => ViralReferent::query()->orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('niche')
                    ->label('Nicho')
                    ->options(fn (): array => Niche::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $query, $nicheId) => $query->whereHas(
                            'viralReferent',
                            fn (Builder $q) => $q->where('niche_id', $nicheId),
                        ),
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
