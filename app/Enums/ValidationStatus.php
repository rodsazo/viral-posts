<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Estado de validación de una idea ganadora. Derivado (no se guarda): una idea está
 * "Validada" si tiene al menos un ejemplo real (URLs de posts virales de otros
 * creadores); sin ejemplos, queda "Pendiente de validación" (sin evidencia de viralidad).
 */
enum ValidationStatus: string implements HasColor, HasLabel
{
    case Validated = 'validated';
    case Pending = 'pending';

    public function getLabel(): string
    {
        return match ($this) {
            self::Validated => 'Validada',
            self::Pending => 'Pendiente de validación',
        };
    }

    /** Color para badges de Filament (admin). */
    public function getColor(): string
    {
        return match ($this) {
            self::Validated => 'success',
            self::Pending => 'gray',
        };
    }

    /** Color de la paleta Flux (badges del Estudio). */
    public function fluxColor(): string
    {
        return match ($this) {
            self::Validated => 'green',
            self::Pending => 'zinc',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Validated => 'check-badge',
            self::Pending => 'clock',
        };
    }
}
