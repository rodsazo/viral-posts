<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Respuesta del cliente final desde la vista pública de la pieza.
 */
enum ClientReviewStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case ChangesRequested = 'changes_requested';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente de revisión',
            self::Approved => 'Aprobada por el cliente',
            self::ChangesRequested => 'Cambios solicitados',
        };
    }

    /** Etiqueta corta para badges. */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Pending => 'Sin revisar',
            self::Approved => 'Aprobada',
            self::ChangesRequested => 'Cambios pedidos',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Approved => 'success',
            self::ChangesRequested => 'warning',
        };
    }

    /** Color de la paleta Flux (Estudio). */
    public function fluxColor(): string
    {
        return match ($this) {
            self::Pending => 'zinc',
            self::Approved => 'green',
            self::ChangesRequested => 'amber',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Approved => 'check-badge',
            self::ChangesRequested => 'pencil-square',
        };
    }
}
