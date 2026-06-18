<?php

namespace App\Support\Ai;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Raíz del structured output: hasta 3 ideas ganadoras, distintas entre sí.
 */
class IdeaVariantSet implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    /**
     * @var IdeaVariant[]
     */
    #[Constrained(
        description: 'Entre 1 y 3 ideas ganadoras, claramente distintas en ángulo y enfoque.',
        itemClass: IdeaVariant::class,
        minItems: 1,
    )]
    public array $variants;
}
