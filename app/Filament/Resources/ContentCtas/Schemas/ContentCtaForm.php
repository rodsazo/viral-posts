<?php

namespace App\Filament\Resources\ContentCtas\Schemas;

use App\Enums\CtaCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContentCtaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->label('Categoría')
                    ->options(CtaCategory::class)
                    ->default(CtaCategory::Follow->value)
                    ->required()
                    ->native(false),
                Textarea::make('text')
                    ->label('Texto')
                    ->required()
                    ->maxLength(600)
                    ->rows(4)
                    ->helperText('La llamada a la acción, tal cual quieres que aparezca o inspire al guión.')
                    ->columnSpanFull(),
            ]);
    }
}
