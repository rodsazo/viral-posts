<?php

namespace App\Support\Ai;

use Anthropic\Client;
use Anthropic\Messages\ThinkingConfigAdaptive;
use App\Enums\ViralMechanism;
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
            userPrompt: $context->toPrompt(),
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
    public function suggestIdeas(IdeaContext $context, int $max = 3): array
    {
        $set = $this->generate(
            system: $this->ideaSystemPrompt(),
            userPrompt: $context->toPrompt(),
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
     * Llamada genérica con structured output. `$format` es una clase StructuredOutputModel.
     *
     * @template T of object
     *
     * @param  class-string<T>  $format
     * @return T
     */
    private function generate(string $system, string $userPrompt, string $format): object
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Falta configurar ANTHROPIC_API_KEY para usar el asistente de IA.');
        }

        try {
            $message = $this->client()->messages->create(
                maxTokens: 4096,
                model: (string) config('services.anthropic.model'),
                system: $system,
                thinking: ThinkingConfigAdaptive::with(),
                messages: [['role' => 'user', 'content' => $userPrompt]],
                outputConfig: ['format' => $format],
            );

            $parsed = $message->parsedOutput();
        } catch (Throwable $e) {
            report($e);

            throw new RuntimeException('No se pudo obtener una sugerencia de la IA. Inténtalo de nuevo en un momento.', previous: $e);
        }

        if (! $parsed instanceof $format) {
            throw new RuntimeException('La IA devolvió una respuesta inesperada. Inténtalo de nuevo.');
        }

        return $parsed;
    }

    private function client(): Client
    {
        return new Client(apiKey: (string) config('services.anthropic.key'));
    }

    private function scriptSystemPrompt(int $count): string
    {
        $formula = collect((array) config('ai.script.formula', []))
            ->map(fn (string $desc): string => "- {$desc}")
            ->implode("\n");

        return <<<PROMPT
        Eres un guionista experto en contenido viral de redes sociales, formado en la metodología de Víctor Heras.

        Tu tarea: a partir del contexto que te da el creador (idea, preguntas de la audiencia y mitos/verdades),
        proponer {$count} variantes de guión claramente distintas en ángulo y tono. Cada variante sigue esta
        fórmula (estructura del guión):
        {$formula}

        Reglas:
        - Responde en español, en el tono cercano y directo de redes sociales.
        - Sigue la fórmula anterior en cada variante.
        - La historia debe ser concreta y específica, no genérica.
        - Refuerza las verdades y desmiente los mitos indicados; responde a las preguntas de la audiencia.
        - Si se dan fórmulas virales de referencia (Heras), úsalas como guía de estructura.
        - No inventes datos, cifras ni hechos que no se deriven del contexto.
        - Las variantes deben diferenciarse entre sí (distinto ángulo, gancho o estructura), no ser parafraseos.
        - Son sugerencias: ofrece opciones de calidad, el creador elegirá.
        PROMPT;
    }

    private function ideaSystemPrompt(): string
    {
        return <<<'PROMPT'
        Eres un estratega de contenido viral para redes sociales, formado en la metodología de Víctor Heras.

        Tu tarea: a partir de las preguntas de la audiencia y los mitos/verdades que te da el creador, proponer
        entre 1 y 3 ideas ganadoras de contenido, claramente distintas en ángulo. Cada idea tiene un título, un
        concepto y un mecanismo de viralidad.

        Reglas:
        - Responde en español.
        - El título es corto y detiene el scroll; el concepto explica el ángulo en 2-4 frases.
        - Cada idea debe responder a las preguntas de la audiencia y apoyarse en los mitos/verdades dados.
        - No inventes datos ni hechos que no se deriven del contexto.
        - Las ideas deben diferenciarse entre sí (distinto enfoque), no ser parafraseos.
        - El mecanismo de viralidad debe ser exactamente uno de los valores permitidos.
        - Son sugerencias: ofrece opciones de calidad, el creador elegirá.
        PROMPT;
    }
}
