<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Niveles de conciencia (Víctor Heras): de inconsciente del problema (0) a
 * consciente del producto (4). El "Umbral Mínimo de Viralidad" busca el nivel
 * más bajo con audiencia suficiente, lo más cerca posible del nicho.
 */
enum AwarenessLevel: int implements HasColor, HasLabel
{
    case Unaware = 0;
    case ProblemAware = 1;
    case SolutionAware = 2;
    case ProviderAware = 3;
    case ProductAware = 4;

    public function getLabel(): string
    {
        return $this->value.' · '.match ($this) {
            self::Unaware => 'No sabe que tiene un problema',
            self::ProblemAware => 'Sabe que tiene un problema',
            self::SolutionAware => 'Sabe cómo quiere resolverlo',
            self::ProviderAware => 'Sabe con quién resolverlo',
            self::ProductAware => 'Sabe con qué producto resolverlo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Unaware => 'gray',
            self::ProblemAware => 'danger',
            self::SolutionAware => 'warning',
            self::ProviderAware => 'info',
            self::ProductAware => 'success',
        };
    }
}
