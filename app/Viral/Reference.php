<?php

namespace App\Viral;

/**
 * Una referencia viral de un formato/subformato: un post real (Instagram, TikTok…)
 * que ejemplifica la fórmula. Se muestra en el Estudio ("Ver ejemplo") al elegir el
 * formato o subformato.
 */
final class Reference
{
    public function __construct(
        public string $name,
        public string $url,
    ) {}
}
