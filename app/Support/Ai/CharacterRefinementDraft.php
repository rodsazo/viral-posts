<?php

namespace App\Support\Ai;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Salida de un turno de refinamiento conversacional del personaje: una nota breve de qué
 * se cambió + la versión COMPLETA propuesta del personaje. La propuesta solo se aplica al
 * personaje si el usuario pulsa "Usar esta versión".
 */
class CharacterRefinementDraft implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'Nota breve (1-2 frases, en español) de qué cambiaste respecto a la petición. Es el mensaje del chat; no repitas todo el personaje.')]
    public string $note;

    #[Constrained(description: 'La versión completa propuesta del personaje, con el cambio pedido aplicado y el resto conservado.')]
    public BrandCharacterDraft $character;
}
