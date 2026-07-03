<?php

namespace App\Support\Ai;

use Anthropic\Core\Attributes\Required;
use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Un prop o firma sensorial del personaje, clasificado por el momento del video que ocupa.
 */
class CharacterProp implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'El objeto/acción y qué comunica (p. ej. "manual usado de posavasos = no necesitas leerlo").')]
    public string $description;

    #[Required(enum: ['durante', 'fondo', 'cierre'])]
    #[Constrained(description: 'Momento del video: objeto en mano durante, gag visual de fondo, o acción-firma de cierre.')]
    public string $moment;
}
