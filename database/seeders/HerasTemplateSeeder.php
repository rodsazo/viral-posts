<?php

namespace Database\Seeders;

use App\Models\HerasTemplate;
use App\Models\Niche;
use App\Models\ViralReferent;
use Illuminate\Database\Seeder;

class HerasTemplateSeeder extends Seeder
{
    /**
     * Siembra el referente "Víctor Heras" y sus 30 plantillas como marcadores.
     *
     * El contenido real (nombre, estructura, formato sugerido y mecanismo) se
     * rellena después desde el panel (catálogo editable). Idempotente: no
     * sobrescribe filas ya editadas.
     */
    public function run(): void
    {
        $niche = Niche::firstOrCreate(
            ['name' => 'Creación de contenido'],
            ['description' => 'Referentes de metodología y crecimiento en redes.', 'color' => '#8b5cf6'],
        );

        $heras = ViralReferent::firstOrCreate(
            ['name' => 'Víctor Heras'],
            ['niche_id' => $niche->id, 'instagram_url' => 'https://instagram.com/victorheras'],
        );

        for ($number = 1; $number <= 30; $number++) {
            HerasTemplate::firstOrCreate(
                ['number' => $number],
                [
                    'viral_referent_id' => $heras->id,
                    'name' => "Plantilla #{$number} (por definir)",
                    'structure' => null,
                    'suggested_format' => null,
                    'viral_mechanism' => null,
                ],
            );
        }
    }
}
