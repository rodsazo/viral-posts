<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentStatus: string implements HasColor, HasLabel
{
    case Planificacion = 'planificacion';
    case GuionListo = 'guion_listo';
    case ListaParaGrabacion = 'lista_para_grabacion';
    case Grabada = 'grabada';
    case Editada = 'editada';
    case Publicada = 'publicada';

    public function getLabel(): string
    {
        return match ($this) {
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
            self::Planificacion => 'gray',
            self::GuionListo => 'info',
            self::ListaParaGrabacion => 'warning',
            self::Grabada => 'warning',
            self::Editada => 'primary',
            self::Publicada => 'success',
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
