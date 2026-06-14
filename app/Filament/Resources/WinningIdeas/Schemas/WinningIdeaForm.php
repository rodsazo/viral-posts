<?php

namespace App\Filament\Resources\WinningIdeas\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class WinningIdeaForm
{
    public static function configure(Schema $schema): Schema
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),
                TextInput::make('viral_mechanism')
                    ->label('Mecanismo de viralidad')
                    ->maxLength(255),
                Textarea::make('concept')
                    ->label('Concepto')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Select::make('questions')
                    ->label('Preguntas que resuelve')
                    ->relationship('questions', 'body', $scopeToTenant)
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Los mitos y verdades se derivan automáticamente de estas preguntas.')
                    ->columnSpanFull(),
            ]);
    }
}
