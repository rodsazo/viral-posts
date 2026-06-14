<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentRating: string implements HasColor, HasLabel
{
    case Mala = 'mala';
    case Media = 'media';
    case Buena = 'buena';
    case Viral = 'viral';

    public function getLabel(): string
    {
        return match ($this) {
            self::Mala => 'Mala',
            self::Media => 'Media',
            self::Buena => 'Buena',
            self::Viral => 'Viral',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Mala => 'danger',
            self::Media => 'gray',
            self::Buena => 'success',
            self::Viral => 'warning',
        };
    }
}
