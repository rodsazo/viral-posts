<?php

namespace App\Jobs;

use App\Models\AiGeneration;
use App\Support\Ai\BrandContext;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\IdeaContext;
use App\Support\Ai\ScriptContext;
use App\Support\Ai\Suggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Ejecuta una generación de IA en segundo plano y guarda el resultado en el registro
 * `AiGeneration`. El Estudio hace polling de ese registro.
 */
class GenerateSuggestionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** El razonamiento puede tardar; damos margen al job (mayor que el timeout HTTP). */
    public int $timeout = 180;

    public int $tries = 1;

    public function __construct(
        public int $generationId,
        public ScriptContext|IdeaContext|BrandContext $context,
        public int $max,
    ) {}

    public function handle(ContentAssistant $assistant): void
    {
        $generation = AiGeneration::find($this->generationId);

        if ($generation === null) {
            return;
        }

        try {
            $result = match (true) {
                // Kickstart: array anidado de hipótesis (ya en forma serializable).
                $this->context instanceof BrandContext => $assistant->suggestIdealFollowers($this->context, $this->max),
                $this->context instanceof ScriptContext => $this->serialize($assistant->suggestScripts($this->context, $this->max)),
                default => $this->serialize($assistant->suggestIdeas($this->context, $this->max)),
            };

            $generation->update([
                'status' => AiGeneration::STATUS_DONE,
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            report($e);

            $generation->update([
                'status' => AiGeneration::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(?Throwable $e): void
    {
        AiGeneration::where('id', $this->generationId)->update([
            'status' => AiGeneration::STATUS_FAILED,
            'error' => 'No se pudo generar. Inténtalo de nuevo.',
        ]);
    }

    /**
     * @param  array<int, Suggestion>  $suggestions
     * @return array<int, array<string, mixed>>
     */
    private function serialize(array $suggestions): array
    {
        return array_map(fn (Suggestion $s): array => $s->toArray(), $suggestions);
    }
}
