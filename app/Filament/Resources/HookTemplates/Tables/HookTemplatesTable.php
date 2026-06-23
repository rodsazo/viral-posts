<?php

namespace App\Filament\Resources\HookTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class HookTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                TextColumn::make('icon')
                    ->label('')
                    ->html()
                    ->formatStateUsing(fn (?string $state): HtmlString => new HtmlString(
                        $state ? '<i class="'.e($state).'"></i>' : '—'
                    )),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('viralReferent.name')
                    ->label('Referente')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('objective')
                    ->label('Objetivo')
                    ->limit(70)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('real_examples')
                    ->label('Ejemplos reales')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state): int => is_array($state) ? count($state) : 0),
            ])
            ->filters([
                //
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
