<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PainType: string implements HasColor, HasLabel
{
    case Pain = 'pain';
    case Problem = 'problem';
    case Desire = 'desire';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pain => 'Dolor',
            self::Problem => 'Problema',
            self::Desire => 'Deseo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pain => 'danger',
            self::Problem => 'warning',
            self::Desire => 'success',
        };
    }
}
