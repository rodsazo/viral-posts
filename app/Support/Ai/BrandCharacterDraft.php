<?php

namespace App\Support\Ai;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Personaje de marca completo generado por la IA: las 9 secciones del framework (handoff).
 * El SDK infiere el JSON schema desde estos tipos y rellena la respuesta.
 */
class BrandCharacterDraft implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'Nombre propio sugerido para el personaje (corto, memorable). Se usa solo si el usuario no dio uno.')]
    public string $name;

    // 0 · Esencia + promesa
    #[Constrained(description: 'Esencia en una línea: quién es el personaje para su audiencia, en una frase con gancho.')]
    public string $essence;

    #[Constrained(description: 'Promesa de marca reformulada en la voz del personaje (el problema del mercado + la solución).')]
    public string $promise_line;

    // 1 · Rol arquetípico
    #[Constrained(description: 'Rol arquetípico: qué es el creador para su audiencia (percepción en 1 segundo). Debe ser el único desde el cual la promesa resulta creíble.')]
    public string $archetype;

    #[Constrained(description: 'Por qué este arquetipo hace la promesa más creíble (y no otro, p. ej. no un gurú/experto).')]
    public string $archetype_why;

    #[Constrained(description: 'Fuente de autoridad del personaje coherente con el arquetipo (no "saber los manuales", sino p. ej. "conocer el camino de entrada").')]
    public string $authority_source;

    // 2 · Enemigo común
    #[Constrained(description: 'Enemigo común abstracto contra el que lucha el personaje (extraído del dolor de la promesa).')]
    public string $enemy_abstract;

    /**
     * @var string[]
     */
    #[Constrained(description: '3 enemigos concretos (cada uno es una fuente inagotable de contenido).', minItems: 1)]
    public array $enemies_concrete;

    #[Constrained(description: 'Regla de polarización: contra qué subgrupo se polariza y a quién NUNCA se ataca (la audiencia objetivo).')]
    public string $polarization_rule;

    // 3 · Posturas defendibles
    /**
     * @var CharacterPosture[]
     */
    #[Constrained(description: '5 posturas: 2 principales (una derriba la barrera de entrada, otra instala el deseo) + 3 secundarias. Exactamente una marcada como puente hacia la conversión.', itemClass: CharacterPosture::class, minItems: 1)]
    public array $postures;

    // 4 · Historia de origen (molde a rellenar con hechos reales del usuario)
    #[Constrained(description: 'Historia de origen, versión completa (bio/videos largos). Usa los hechos reales del usuario; molde: "yo creía X → quiebre concreto → descubrí Y (tesis) → hoy me dedico a Z". Marca con [HUECO: ...] lo que falte por confirmar.')]
    public string $origin_full;

    #[Constrained(description: 'Historia de origen, versión reel (hablando a cámara, ~30-45 s).')]
    public string $origin_reel;

    #[Constrained(description: 'Historia de origen, versión de una frase (respuesta a "¿a qué te dedicas?", con gancho de curiosidad).')]
    public string $origin_oneliner;

    // 5 · Voz y energía
    #[Constrained(description: 'Tono de voz (p. ej. "sobremesa con amigos, no tutorial").')]
    public string $voice_tone;

    #[Constrained(description: 'Política de jerga (p. ej. "cero jerga sin traducir en la misma frase").')]
    public string $voice_jargon;

    #[Constrained(description: 'Ritmo (p. ej. "gancho en 2 s, sin intros, frases cortas").')]
    public string $voice_rhythm;

    #[Constrained(description: 'Humor permitido y prohibido (p. ej. "autocrítico sí; reírse de la audiencia jamás").')]
    public string $voice_humor;

    #[Constrained(description: 'Firma verbal de cierre: una frase repetible al final de cada pieza.')]
    public string $verbal_signature;

    // 6 · Identidad visual
    #[Constrained(description: 'Principio rector de una línea de la identidad visual (derivado de la audiencia).')]
    public string $visual_principle;

    #[Constrained(description: 'Atuendo, con el límite explícito (máx. UN guiño al nicho) y el test "¿podrías ir así a tomar un café?".')]
    public string $visual_outfit;

    #[Constrained(description: 'Look (cuidado pero real, ni cosplay ni sobreproducido).')]
    public string $visual_look;

    #[Constrained(description: 'Entorno / fondo FIJO (siempre el mismo): descríbelo.')]
    public string $visual_environment;

    /**
     * @var CharacterProp[]
     */
    #[Constrained(description: 'Combo de props coherentes, cada uno en un momento distinto (durante/fondo/cierre). Prioriza acción+sonido de cierre sobre objetos estáticos.', itemClass: CharacterProp::class, minItems: 1)]
    public array $visual_props;

    // 7 · Formatos de producción naturales
    /**
     * @var string[]
     */
    #[Constrained(description: 'Formatos de producción naturales del personaje, priorizando dificultad baja (permiten volumen constante).', minItems: 1)]
    public array $production_formats;

    // 8 · Conexión con la conversión
    #[Constrained(description: 'Destino de conversión real (a dónde se lleva a la audiencia).')]
    public string $conversion_destination;

    #[Constrained(description: 'Cadena lógica explícita: enemigo → postura central → CTA natural → destino real.')]
    public string $conversion_chain;

    /**
     * @var string[]
     */
    #[Constrained(description: 'Lista cerrada de CTAs válidos: solo acciones/features que el destino realmente ofrece (no inventar).', minItems: 1)]
    public array $valid_ctas;

    // 9 · Reglas de coherencia (guardrails)
    /**
     * @var string[]
     */
    #[Constrained(description: '4-6 reglas de coherencia inviolables que protegen al personaje en toda producción futura.', minItems: 1)]
    public array $coherence_rules;
}
