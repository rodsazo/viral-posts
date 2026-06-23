<?php

namespace App\Support\Ai;

use Anthropic\Core\Attributes\Required;
use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Un dolor / problema / deseo dentro de una hipótesis de seguidor ideal.
 */
class PainItem implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    // Debe coincidir con App\Enums\PainType.
    #[Required(enum: ['pain', 'problem', 'desire'])]
    #[Constrained(description: 'Tipo: pain (dolor), problem (problema) o desire (deseo).')]
    public string $type;

    #[Constrained(description: 'El dolor, problema o deseo, en palabras del seguidor.')]
    public string $body;
}
