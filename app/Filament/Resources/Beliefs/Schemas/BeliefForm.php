<?php

namespace App\Filament\Resources\Beliefs\Schemas;

use App\Enums\BeliefType;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BeliefForm
{
    public static function configure(Schema $schema): Schema
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo')
                    ->options(BeliefType::class)
                    ->required()
                    ->native(false),
                Textarea::make('statement')
                    ->label('Afirmación')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('stance')
                    ->label('Postura de la marca')
                    ->helperText('Qué/por qué se desmiente o impulsa.')
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('questions')
                    ->label('Preguntas relacionadas')
                    ->relationship('questions', 'body', $scopeToTenant)
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
}
