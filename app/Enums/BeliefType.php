<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BeliefType: string implements HasColor, HasLabel
{
    case Myth = 'myth';
    case Truth = 'truth';

    public function getLabel(): string
    {
        return match ($this) {
            self::Myth => 'Mito',
            self::Truth => 'Verdad',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Myth => 'danger',
            self::Truth => 'success',
        };
    }
}
