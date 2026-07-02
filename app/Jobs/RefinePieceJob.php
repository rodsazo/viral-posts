<?php

namespace App\Jobs;

use App\Models\AiGeneration;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\RefineContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Ejecuta un turno de refinamiento conversacional en segundo plano y guarda el
 * resultado (nota + guión propuesto) en el registro `AiGeneration`. El Composer hace
 * polling de ese registro y, al terminar, crea el mensaje del asistente en el hilo.
 */
class RefinePieceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** El razonamiento puede tardar; damos margen (mayor que el timeout HTTP). */
    public int $timeout = 180;

    public int $tries = 1;

    public function __construct(
        public int $generationId,
        public RefineContext $context,
    ) {}

    public function handle(ContentAssistant $assistant): void
    {
        $generation = AiGeneration::find($this->generationId);

        if ($generation === null) {
            return;
        }

        try {
            $result = $assistant->refineScript($this->context);

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
