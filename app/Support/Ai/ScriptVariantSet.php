<?php

namespace App\Support\Ai;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Raíz del structured output: hasta 3 variantes de guión, distintas entre sí.
 */
class ScriptVariantSet implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    /**
     * @var ScriptVariant[]
     */
    #[Constrained(
        description: 'Entre 1 y 3 variantes de guión, claramente distintas en ángulo y tono.',
        itemClass: ScriptVariant::class,
        minItems: 1,
    )]
    public array $variants;
}
