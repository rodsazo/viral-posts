<?php

namespace App\Support\Ai;

use Anthropic\Core\Attributes\Required;
use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Una postura defendible del personaje: opinión corta y repetible en muchos contenidos.
 */
class CharacterPosture implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'La postura en una frase corta, polémica y repetible.')]
    public string $statement;

    #[Constrained(description: 'Por qué funciona: qué creencia desafía o qué mecanismo de viralidad activa.')]
    public string $why;

    #[Required(enum: ['principal', 'secundaria'])]
    #[Constrained(description: 'Principal (bandera de los primeros contenidos) o secundaria (banco de reserva). Debe haber 2 principales y 3 secundarias.')]
    public string $kind;

    #[Constrained(description: 'true si es la "postura puente" que conecta el contenido con la conversión. Debe haber exactamente una postura puente.')]
    public bool $bridge;
}
