<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PeriodStatus: string implements HasColor, HasLabel
{
    case Borrador = 'borrador';
    case Publicado = 'publicado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Publicado => 'Publicado',
        };
    }

    public function getColor(): string|array
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Publicado => 'success',
        };
    }

    /** Color de la paleta Flux (badges/acentos del Estudio). */
    public function fluxColor(): string
    {
        return match ($this) {
            self::Borrador => 'zinc',
            self::Publicado => 'green',
        };
    }
}
