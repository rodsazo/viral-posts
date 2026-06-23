<?php

namespace App\Filament\Resources\ContentCtas\Tables;

use App\Enums\CtaCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentCtasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge(),
                TextColumn::make('text')
                    ->label('Texto')
                    ->wrap()
                    ->searchable()
                    ->limit(120),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(CtaCategory::class),
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
