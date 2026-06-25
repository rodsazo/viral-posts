<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentStatus: string implements HasColor, HasLabel
{
    case Borrador = 'borrador';
    case Planificacion = 'planificacion';
    case GuionListo = 'guion_listo';
    case ListaParaGrabacion = 'lista_para_grabacion';
    case Grabada = 'grabada';
    case Editada = 'editada';
    case Publicada = 'publicada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Planificacion => 'Planificación',
            self::GuionListo => 'Guión listo',
            self::ListaParaGrabacion => 'Lista para grabación',
            self::Grabada => 'Grabada',
            self::Editada => 'Editada',
            self::Publicada => 'Publicada',
        };
    }

    public function getColor(): string|array
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Planificacion => 'gray',
            self::GuionListo => 'info',
            self::ListaParaGrabacion => 'warning',
            self::Grabada => 'warning',
            self::Editada => 'primary',
            self::Publicada => 'success',
        };
    }

    /**
     * Color de la paleta Flux (badges del Estudio). Distinto de getColor(), que usa
     * los nombres semánticos de Filament (info/primary/success). Aquí pintamos el flujo
     * como una progresión de color: planificación → publicada.
     */
    public function fluxColor(): string
    {
        return match ($this) {
            self::Borrador => 'zinc',
            self::Planificacion => 'violet',
            self::GuionListo => 'blue',
            self::ListaParaGrabacion => 'cyan',
            self::Grabada => 'amber',
            self::Editada => 'pink',
            self::Publicada => 'green',
        };
    }

    /**
     * Orden del flujo de producción.
     */
    public static function ordered(): array
    {
        return self::cases();
    }
}
