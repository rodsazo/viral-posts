<?php

namespace App\Jobs;

use App\Models\AiGeneration;
use App\Support\Ai\CharacterContext;
use App\Support\Ai\ContentAssistant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Genera un Personaje de Marca completo en segundo plano y guarda el resultado (las 9
 * secciones) en el registro `AiGeneration`. El generador del Estudio hace polling y, al
 * terminar, crea la entidad BrandCharacter con ese resultado.
 */
class GenerateCharacterJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Razonar 9 secciones puede tardar; damos margen amplio. */
    public int $timeout = 240;

    public int $tries = 1;

    public function __construct(
        public int $generationId,
        public CharacterContext $context,
    ) {}

    public function handle(ContentAssistant $assistant): void
    {
        $generation = AiGeneration::find($this->generationId);

        if ($generation === null) {
            return;
        }

        try {
            $result = $assistant->generateCharacter($this->context);

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
            'error' => 'No se pudo generar el personaje. Inténtalo de nuevo.',
        ]);
    }
}
