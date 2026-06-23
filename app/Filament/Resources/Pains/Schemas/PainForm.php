<?php

namespace App\Filament\Resources\Pains\Schemas;

use App\Enums\PainType;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PainForm
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
                Select::make('type')
                    ->label('Tipo')
                    ->options(PainType::class)
                    ->default(PainType::Pain->value)
                    ->required()
                    ->native(false),
                Textarea::make('body')
                    ->label('Enunciado')
                    ->required()
                    ->rows(3)
                    ->helperText('El dolor, problema o deseo, en palabras del seguidor.')
                    ->columnSpanFull(),
            ]);
    }
}
