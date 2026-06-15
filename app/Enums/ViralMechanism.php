<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ViralMechanism: string implements HasColor, HasLabel
{
    case Curiosidad = 'curiosidad';
    case Sorpresa = 'sorpresa';
    case Controversia = 'controversia';
    case Identificacion = 'identificacion';
    case Emocion = 'emocion';
    case Humor = 'humor';
    case Utilidad = 'utilidad';
    case Inspiracion = 'inspiracion';

    public function getLabel(): string
    {
        return match ($this) {
            self::Curiosidad => 'Curiosidad',
            self::Sorpresa => 'Sorpresa',
            self::Controversia => 'Controversia',
            self::Identificacion => 'Identificación',
            self::Emocion => 'Emoción',
            self::Humor => 'Humor',
            self::Utilidad => 'Utilidad',
            self::Inspiracion => 'Inspiración',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Curiosidad => 'info',
            self::Sorpresa => 'warning',
            self::Controversia => 'danger',
            self::Identificacion => 'primary',
            self::Emocion => 'danger',
            self::Humor => 'warning',
            self::Utilidad => 'success',
            self::Inspiracion => 'info',
        };
    }
}
