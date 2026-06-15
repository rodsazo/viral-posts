<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentObjective: string implements HasColor, HasLabel
{
    case Viralidad = 'viralidad';
    case Venta = 'venta';

    public function getLabel(): string
    {
        return match ($this) {
            self::Viralidad => 'Viralidad',
            self::Venta => 'Venta',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Viralidad => 'info',
            self::Venta => 'success',
        };
    }
}
