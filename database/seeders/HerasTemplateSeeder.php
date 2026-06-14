<?php

namespace Database\Seeders;

use App\Models\HerasTemplate;
use Illuminate\Database\Seeder;

class HerasTemplateSeeder extends Seeder
{
    /**
     * Siembra las 30 plantillas Heras como marcadores de posición.
     *
     * El contenido real (nombre, estructura, formato sugerido y mecanismo) se
     * rellena después desde el panel (catálogo editable). Idempotente: no
     * sobrescribe filas ya editadas.
     */
    public function run(): void
    {
        for ($number = 1; $number <= 30; $number++) {
            HerasTemplate::firstOrCreate(
                ['number' => $number],
                [
                    'name' => "Plantilla #{$number} (por definir)",
                    'structure' => null,
                    'suggested_format' => null,
                    'viral_mechanism' => null,
                ],
            );
        }
    }
}
