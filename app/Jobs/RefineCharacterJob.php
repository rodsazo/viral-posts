<?php

namespace App\Jobs;

use App\Models\AiGeneration;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\RefineCharacterContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Ejecuta un turno de refinamiento de personaje en segundo plano y guarda el resultado
 * (nota + versión propuesta) en `AiGeneration`. El editor hace polling y crea el mensaje
 * del asistente en el hilo al terminar.
 */
class RefineCharacterJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 240;

    public int $tries = 1;

    public function __construct(
        public int $generationId,
        public RefineCharacterContext $context,
    ) {}

    public function handle(ContentAssistant $assistant): void
    {
        $generation = AiGeneration::find($this->generationId);

        if ($generation === null) {
            return;
        }

        try {
            $result = $assistant->refineCharacter($this->context);

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
            'error' => 'No se pudo refinar. Inténtalo de nuevo.',
        ]);
    }
}
