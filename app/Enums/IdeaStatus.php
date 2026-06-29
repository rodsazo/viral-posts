<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Estado del flujo de una idea ganadora (distinto de la "validación", que mide si tiene
 * ejemplos reales). Toda idea nace en Borrador; al estudiarla pasa a Hipótesis; cuando
 * nos da resultado con la marca la marcamos Fija (hacer más contenido de ella), y si no
 * cuaja, Descartada.
 */
enum IdeaStatus: string implements HasColor, HasLabel
{
    case Borrador = 'borrador';
    case Hipotesis = 'hipotesis';
    case Fija = 'fija';
    case Descartada = 'descartada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Hipotesis => 'Hipótesis',
            self::Fija => 'Fija',
            self::Descartada => 'Descartada',
        };
    }

    /** Color para badges de Filament (admin). */
    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Hipotesis => 'info',
            self::Fija => 'success',
            self::Descartada => 'danger',
        };
    }

    /** Color de la paleta Flux (badges del Estudio). */
    public function fluxColor(): string
    {
        return match ($this) {
            self::Borrador => 'zinc',
            self::Hipotesis => 'blue',
            self::Fija => 'green',
            self::Descartada => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Borrador => 'pencil',
            self::Hipotesis => 'beaker',
            self::Fija => 'star',
            self::Descartada => 'archive-box-x-mark',
        };
    }

    /** Prioridad para ordenar la lista: primero las probadas (Fija), al final las descartadas. */
    public function sortPriority(): int
    {
        return match ($this) {
            self::Fija => 0,
            self::Hipotesis => 1,
            self::Borrador => 2,
            self::Descartada => 3,
        };
    }
}
