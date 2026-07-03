<?php

namespace App\Support\Ai;

use Anthropic\Client;
use Anthropic\Messages\CacheControlEphemeral;
use Anthropic\Messages\TextBlockParam;
use Anthropic\Messages\ThinkingConfigAdaptive;
use App\Enums\PainType;
use App\Enums\ViralMechanism;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Capa de servicio del asistente de IA (Anthropic / Claude). Dado un caso de uso
 * + contexto, devuelve **hasta 3 sugerencias** tipadas. La UI (admin / Estudio)
 * solo muestra las opciones y aplica la elegida — la IA nunca reescribe por su cuenta.
 *
 * Patrón a replicar en futuros casos (ideas ganadoras, etc.): un método público que
 * recibe el contexto del caso y devuelve `Suggestion[]`.
 */
class ContentAssistant
{
    /** ¿Hay clave de API configurada? La UI lo usa para no ofrecer la acción sin clave. */
    public function isConfigured(): bool
    {
        return filled(config('services.anthropic.key'));
    }

    /**
     * Genera variantes de guión a partir del contexto. `$max` por defecto usa el nº
     * de sugerencias en línea de config; el generador del Estudio pasa el suyo (5).
     *
     * @return array<int, Suggestion>
     */
    public function suggestScripts(ScriptContext $context, ?int $max = null): array
    {
        $max = $max ?? (int) config('ai.script.inline_suggestions', 3);

        $set = $this->generate(
            system: $this->scriptSystemPrompt($max),
            messages: [['role' => 'user', 'content' => $context->toPrompt()]],
            format: ScriptVariantSet::class,
        );

        $variants = array_slice($set->variants ?? [], 0, $max);

        return array_map(
            fn (ScriptVariant $v, int $i): Suggestion => new Suggestion(
                label: 'Variante '.($i + 1),
                fields: [
                    'hook' => $v->hook,
                    'story' => $v->story,
                    'moral' => $v->moral,
                    'cta' => $v->cta,
                ],
                preview: implode("\n\n", [
                    'GANCHO: '.$v->hook,
                    'HISTORIA: '.$v->story,
                    'MORALEJA: '.$v->moral,
                    'CTA: '.$v->cta,
                ]),
            ),
            $variants,
            array_keys($variants),
        );
    }

    /**
     * Genera hasta 3 ideas ganadoras a partir de las preguntas y mitos/verdades.
     *
     * @return array<int, Suggestion>
     */
    public function suggestIdeas(IdeaContext $context, ?int $max = null): array
    {
        $max = $max ?? (int) config('ai.idea.suggestions', 3);

        $set = $this->generate(
            system: $this->ideaSystemPrompt($max),
            messages: [['role' => 'user', 'content' => $context->toPrompt()]],
            format: IdeaVariantSet::class,
        );

        $variants = array_slice($set->variants ?? [], 0, $max);

        return array_map(
            function (IdeaVariant $v, int $i): Suggestion {
                $mechanism = ViralMechanism::tryFrom($v->mechanism);

                $fields = ['title' => $v->title, 'concept' => $v->concept];

                if ($mechanism !== null) {
                    $fields['viral_mechanism'] = $mechanism->value;
                }

                return new Suggestion(
                    label: 'Idea '.($i + 1),
                    fields: $fields,
                    preview: implode("\n\n", [
                        'TÍTULO: '.$v->title,
                        'CONCEPTO: '.$v->concept,
                        'MECANISMO: '.($mechanism?->getLabel() ?? $v->mechanism),
                    ]),
                );
            },
            $variants,
            array_keys($variants),
        );
    }

    /**
     * Kickstart: genera hipótesis de seguidor ideal a partir del contexto de marca.
     * Devuelve un array anidado (no Suggestion[]) porque cada hipótesis lleva hijos.
     *
     * @return array<int, array{name: string, description: string, awareness_level: int, pains: array<int, array{type: string, body: string}>, questions: array<int, string>, beliefs: array<int, string>}>
     */
    public function suggestIdealFollowers(BrandContext $context, ?int $max = null): array
    {
        $max = $max ?? (int) config('ai.kickstart.suggestions', 3);

        $set = $this->generate(
            system: $this->kickstartSystemPrompt($max),
            messages: [['role' => 'user', 'content' => $context->toPrompt()]],
            format: IdealFollowerHypothesisSet::class,
        );

        $hypotheses = array_slice($set->hypotheses ?? [], 0, $max);

        return array_map(fn (IdealFollowerHypothesis $h): array => [
            'name' => $h->name,
            'description' => $h->description,
            'awareness_level' => $h->awareness_level,
            'pains' => array_map(fn (PainItem $p): array => [
                'type' => PainType::tryFrom($p->type)?->value ?? PainType::Pain->value,
                'body' => $p->body,
            ], $h->pains),
            'questions' => array_values($h->questions),
            'beliefs' => array_values($h->beliefs),
        ], $hypotheses);
    }

    /**
     * Genera un Personaje de Marca completo (las 9 secciones) a partir del contexto.
     * Devuelve un array serializable listo para persistir en `brand_characters`.
     *
     * @return array<string, mixed>
     */
    public function generateCharacter(CharacterContext $context): array
    {
        $draft = $this->generate(
            system: $this->characterSystemPrompt(),
            messages: [['role' => 'user', 'content' => $context->toPrompt()]],
            format: BrandCharacterDraft::class,
            timeout: (int) config('ai.character.request_timeout', 200),
            maxTokens: (int) config('ai.character.max_tokens', 16000),
        );

        return $this->characterToArray($draft);
    }

    /**
     * Refina un Personaje de Marca en modo conversación: recibe el hilo (documento actual +
     * historial + nueva instrucción) y devuelve una nota + la versión propuesta del personaje.
     * El bloque de sistema (metodología + documento actual) se marca cacheable (prompt caching).
     *
     * @return array{note: string, fields: array<string, mixed>}
     */
    public function refineCharacter(RefineCharacterContext $context): array
    {
        $system = [
            TextBlockParam::with(
                text: $context->toSystem(),
                cacheControl: CacheControlEphemeral::with(),
            ),
        ];

        $draft = $this->generate(
            system: $system,
            messages: $context->toMessages(),
            format: CharacterRefinementDraft::class,
            timeout: (int) config('ai.character.request_timeout', 200),
            maxTokens: (int) config('ai.character.max_tokens', 16000),
        );

        return [
            'note' => trim($draft->note),
            'fields' => $this->characterToArray($draft->character),
        ];
    }

    /**
     * Mapea un BrandCharacterDraft (salida de IA) al array de columnas de brand_characters.
     *
     * @return array<string, mixed>
     */
    private function characterToArray(BrandCharacterDraft $draft): array
    {
        return [
            'name' => trim($draft->name),
            'essence' => $draft->essence,
            'promise_line' => $draft->promise_line,
            'archetype' => $draft->archetype,
            'archetype_why' => $draft->archetype_why,
            'authority_source' => $draft->authority_source,
            'enemy_abstract' => $draft->enemy_abstract,
            'enemies_concrete' => array_values($draft->enemies_concrete),
            'polarization_rule' => $draft->polarization_rule,
            'postures' => array_map(fn (CharacterPosture $p): array => [
                'statement' => $p->statement,
                'why' => $p->why,
                'kind' => $p->kind,
                'bridge' => $p->bridge,
            ], $draft->postures),
            'origin_full' => $draft->origin_full,
            'origin_reel' => $draft->origin_reel,
            'origin_oneliner' => $draft->origin_oneliner,
            'voice_tone' => $draft->voice_tone,
            'voice_jargon' => $draft->voice_jargon,
            'voice_rhythm' => $draft->voice_rhythm,
            'voice_humor' => $draft->voice_humor,
            'verbal_signature' => $draft->verbal_signature,
            'visual_principle' => $draft->visual_principle,
            'visual_outfit' => $draft->visual_outfit,
            'visual_look' => $draft->visual_look,
            'visual_environment' => $draft->visual_environment,
            'visual_props' => array_map(fn (CharacterProp $p): array => [
                'description' => $p->description,
                'moment' => $p->moment,
            ], $draft->visual_props),
            'production_formats' => array_values($draft->production_formats),
            'conversion_destination' => $draft->conversion_destination,
            'conversion_chain' => $draft->conversion_chain,
            'valid_ctas' => array_values($draft->valid_ctas),
            'coherence_rules' => array_values($draft->coherence_rules),
        ];
    }

    /**
     * Refina el guión de una pieza en modo conversación: recibe el hilo (historial +
     * nueva instrucción) y devuelve una nota de cambios + la versión propuesta del guión.
     *
     * El bloque de sistema (marca + idea + audiencia + borrador base) se marca como
     * cacheable (prompt caching): entre "más cálido" y "más corto" ese prefijo no cambia,
     * así que en cada vuelta se paga solo una fracción de esos tokens.
     *
     * @return array{note: string, fields: array<string, string>}
     */
    public function refineScript(RefineContext $context): array
    {
        // Bloque de sistema estable → punto de caché (ephemeral). Lo mutable (la
        // conversación) viaja en `messages`, fuera del prefijo cacheado.
        $system = [
            TextBlockParam::with(
                text: $context->toSystem(),
                cacheControl: CacheControlEphemeral::with(),
            ),
        ];

        $refinement = $this->generate(
            system: $system,
            messages: $context->toMessages(),
            format: ScriptRefinement::class,
        );

        return [
            'note' => trim($refinement->note),
            'fields' => [
                'hook' => $refinement->hook,
                'story' => $refinement->story,
                'moral' => $refinement->moral,
                'cta' => $refinement->cta,
            ],
        ];
    }

    /**
     * Llamada genérica con structured output. `$format` es una clase StructuredOutputModel.
     * `$system` puede ser texto plano o bloques (p. ej. con cache_control para prompt caching).
     *
     * @template T of object
     *
     * @param  string|array<int, TextBlockParam>  $system
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  class-string<T>  $format
     * @return T
     */
    private function generate(string|array $system, array $messages, string $format, ?int $timeout = null, ?int $maxTokens = null): object
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Falta configurar ANTHROPIC_API_KEY para usar el asistente de IA.');
        }

        $timeout = $timeout ?? (int) config('ai.request_timeout', 120);
        $maxTokens = $maxTokens ?? 4096;

        // Generar varios guiones con razonamiento puede superar el max_execution_time
        // por defecto de PHP (30 s). Ampliamos el límite solo para esta operación.
        if (function_exists('set_time_limit')) {
            @set_time_limit($timeout + 30);
        }

        $outputConfig = ['format' => $format];

        if (filled(config('ai.effort'))) {
            $outputConfig['effort'] = (string) config('ai.effort');
        }

        $model = (string) config('services.anthropic.model');
        $workflow = class_basename($format);
        $startedAt = microtime(true);

        // Logueamos el prompt enviado antes de la llamada: así queda registro aunque
        // la petición falle o se agote el tiempo a mitad de generación.
        Log::channel('ai')->info('Petición IA', [
            'workflow' => $workflow,
            'model' => $model,
            'system' => is_array($system) ? array_map(fn (TextBlockParam $b): string => $b->text, $system) : $system,
            'messages' => $messages,
        ]);

        try {
            $message = $this->client()->messages->create(
                maxTokens: $maxTokens,
                model: $model,
                system: $system,
                thinking: ThinkingConfigAdaptive::with(),
                messages: $messages,
                outputConfig: $outputConfig,
                requestOptions: ['timeout' => (float) $timeout],
            );

            $parsed = $message->parsedOutput();
        } catch (Throwable $e) {
            Log::channel('ai')->error('Respuesta IA fallida', [
                'workflow' => $workflow,
                'model' => $model,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'error' => $e->getMessage(),
            ]);

            report($e);

            throw new RuntimeException('No se pudo obtener una sugerencia de la IA. Inténtalo de nuevo en un momento.', previous: $e);
        }

        if (! $parsed instanceof $format) {
            // stop_reason = 'max_tokens' delata truncación (subir ai.*.max_tokens).
            $rawText = collect($message->content ?? [])
                ->map(fn ($block): string => (string) ($block->text ?? ''))
                ->implode('');

            Log::channel('ai')->error('Respuesta IA inesperada', [
                'workflow' => $workflow,
                'model' => $model,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'stop_reason' => $message->stopReason,
                'max_tokens' => $maxTokens,
                'output_tokens' => $message->usage->outputTokens ?? null,
                'raw_snippet' => mb_substr($rawText, 0, 500),
            ]);

            throw new RuntimeException('La IA devolvió una respuesta inesperada. Inténtalo de nuevo.');
        }

        Log::channel('ai')->info('Respuesta IA', [
            'workflow' => $workflow,
            'model' => $model,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'result' => $this->stringifyResult($parsed),
        ]);

        return $parsed;
    }

    /**
     * Serializa la salida estructurada de la IA a JSON legible para el log.
     */
    private function stringifyResult(object $parsed): string
    {
        if (method_exists($parsed, 'toJson')) {
            return (string) $parsed->toJson();
        }

        return (string) json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function client(): Client
    {
        return new Client(apiKey: (string) config('services.anthropic.key'));
    }

    private function scriptSystemPrompt(int $count): string
    {
        $role = (string) config('ai.script.system.role');
        $formula = $this->bullets((array) config('ai.script.formula', []));
        $rules = $this->bullets((array) config('ai.script.system.rules', []));
        $checklist = $this->bullets((array) config('ai.script.system.checklist', []));

        return <<<PROMPT
        {$role}

        Tu tarea: a partir del contexto que te da el creador (idea, preguntas de la audiencia y mitos/verdades),
        proponer {$count} variantes de guión claramente distintas en ángulo y tono. Cada variante sigue esta
        fórmula (estructura del guión):
        {$formula}

        Reglas:
        {$rules}

        Verificación: Cada contenido debe cumplir con la mayor cantidad posible de las siguientes verificaciones:
        {$checklist}
        PROMPT;
    }

    private function characterSystemPrompt(): string
    {
        $role = (string) config('ai.character.system.role');
        $method = (string) config('ai.character.system.method');
        $rules = $this->bullets((array) config('ai.character.system.rules', []));

        return <<<PROMPT
        {$role}

        Tu tarea: a partir de los insumos del usuario (marca, audiencia, destino de conversión y hechos
        de la historia de origen), construye un personaje de marca completo: las 9 secciones del framework.

        {$method}

        Reglas:
        {$rules}
        PROMPT;
    }

    private function ideaSystemPrompt(int $count): string
    {
        $role = (string) config('ai.idea.system.role');
        $rules = $this->bullets((array) config('ai.idea.system.rules', []));

        return <<<PROMPT
        {$role}

        Tu tarea: a partir de las preguntas de la audiencia y los mitos/verdades que te da el creador, proponer
        {$count} ideas ganadoras de contenido, claramente distintas en ángulo. Cada idea tiene un título, un
        concepto y un mecanismo de viralidad.

        Reglas:
        {$rules}
        PROMPT;
    }

    private function kickstartSystemPrompt(int $count): string
    {
        $role = (string) config('ai.kickstart.system.role');
        $rules = $this->bullets((array) config('ai.kickstart.system.rules', []));
        $awareness = (string) config('ai.kickstart.awareness');
        $examples = (string) config('ai.kickstart.examples');

        return <<<PROMPT
        {$role}

        Tu tarea: propón {$count} hipótesis de seguidor ideal para la marca dada, distintas entre sí.

        {$awareness}

        {$examples}

        Reglas:
        {$rules}
        PROMPT;
    }

    /**
     * @param  array<int|string, string>  $items
     */
    private function bullets(array $items): string
    {
        return collect($items)->map(fn (string $item): string => "- {$item}")->implode("\n");
    }
}
