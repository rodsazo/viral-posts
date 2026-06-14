<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $schema
            ->components([
                Select::make('ideal_follower_id')
                    ->label('Seguidor ideal')
                    ->relationship('idealFollower', 'name', $scopeToTenant)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name', $scopeToTenant)
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin categoría'),
                Textarea::make('body')
                    ->label('Pregunta')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('beliefs')
                    ->label('Mitos y verdades relacionados')
                    ->relationship('beliefs', 'statement', $scopeToTenant)
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Select::make('winningIdeas')
                    ->label('Ideas ganadoras relacionadas')
                    ->relationship('winningIdeas', 'title', $scopeToTenant)
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Notas internas')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}
