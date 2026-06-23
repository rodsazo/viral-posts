<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Categoría de una llamada a la acción (CTA) de la marca. De momento solo dos tipos;
 * pensado para crecer (p. ej. "Link en bio", "Guardar", etc.).
 */
enum CtaCategory: string implements HasColor, HasLabel
{
    case Follow = 'follow';
    case Keyword = 'keyword';

    public function getLabel(): string
    {
        return match ($this) {
            self::Follow => 'Seguir',
            self::Keyword => 'Palabra clave',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Follow => 'info',
            self::Keyword => 'warning',
        };
    }

    /** Instrucción para el prompt: qué busca lograr esta categoría de CTA. */
    public function promptIntent(): string
    {
        return match ($this) {
            self::Follow => 'invitar al espectador a SEGUIR la cuenta para no perderse más contenido como este',
            self::Keyword => 'invitar al espectador a comentar una PALABRA CLAVE para recibir algo a cambio (un recurso, enlace o mensaje directo)',
        };
    }
}
