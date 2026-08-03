<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContentFormat: string implements HasLabel
{
    case HablandoACamara = 'hablando_a_camara';
    case Selfie = 'selfie';
    case Entrevista = 'entrevista';
    case Puv = 'puv';
    case Pov = 'pov';
    case Personajes = 'personajes';
    case Rankings = 'rankings';
    case Podcast = 'podcast';
    case Vlog = 'vlog';
    case DocumentalReto = 'documental_reto';
    case HablandoACamaraVisual = 'hablando_a_camara_visual';

    public function getLabel(): string
    {
        return match ($this) {
            self::HablandoACamara => 'Hablando a cámara',
            self::Selfie => 'Selfie',
            self::Entrevista => 'Entrevista',
            self::Puv => 'PUV',
            self::Pov => 'POV',
            self::Personajes => 'Personajes',
            self::Rankings => 'Rankings',
            self::Podcast => 'Podcast',
            self::Vlog => 'Vlog',
            self::DocumentalReto => 'Documental / Reto',
            self::HablandoACamaraVisual => 'Hablando a cámara (visual)',
        };
    }
}
