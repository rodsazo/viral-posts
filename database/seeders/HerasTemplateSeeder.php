<?php

namespace Database\Seeders;

use App\Models\HerasTemplate;
use App\Models\Niche;
use App\Models\ViralReferent;
use Illuminate\Database\Seeder;

class HerasTemplateSeeder extends Seeder
{
    /**
     * Siembra el referente "Víctor Heras" y sus 30 plantillas de ideas ganadoras
     * (fuente: sources/PlantillasVictorHeras.md).
     *
     * Idempotente y NO destructivo: rellena solo las plantillas que aún están vacías
     * (sin `structure`); si una fila ya tiene contenido editado, la respeta.
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

        foreach ($this->templates() as $data) {
            // Identidad por nombre dentro del referente Heras (ya no hay "número").
            $key = ['viral_referent_id' => $heras->id, 'name' => $data['name']];
            $existing = HerasTemplate::where($key)->first();

            // No pisar plantillas ya editadas con contenido real.
            if ($existing !== null && filled($existing->structure)) {
                continue;
            }

            HerasTemplate::updateOrCreate($key, [...$data, 'viral_referent_id' => $heras->id]);
        }
    }

    /**
     * @return array<int, array{name: string, structure: string, suggested_format: ?string, viral_mechanism: ?string}>
     */
    private function templates(): array
    {
        return [
            1 => [
                'name' => 'Quiero X pero no tengo Y',
                'structure' => 'Alguien expresa un deseo. El creador revela cómo lograrlo sin el recurso que creen necesitar.',
                'suggested_format' => 'PUV o Entrevista',
                'viral_mechanism' => 'Problema reconocible + solución desconocida + oportunidad de ganar/ahorrar dinero',
            ],
            2 => [
                'name' => 'Estas X tienes que tenerlas/hacerlas sí o sí',
                'structure' => 'Lista corta de herramientas, hábitos o recursos que el creador recomienda con autoridad.',
                'suggested_format' => 'Hablando a cámara',
                'viral_mechanism' => 'Prueba social del creador + solución directa + tono de urgencia',
            ],
            3 => [
                'name' => '¿Cuánto dinero / tiempo / esfuerzo inviertes en X?',
                'structure' => 'Se le pregunta a alguien sobre una cifra relacionada con su estilo de vida o resultados.',
                'suggested_format' => 'Entrevista estilo podcast casual',
                'viral_mechanism' => 'Contraste de expectativas + humaniza figura aspiracional + respuesta sorprendente',
            ],
            4 => [
                'name' => '¿Qué es mejor, X o Y? (debate emocional)',
                'structure' => 'Se plantea una pregunta que la gente ya se hace. La respuesta desafía el sentido común. Regla clave: el tema debe ser algo que la gente ya debate en la vida real.',
                'suggested_format' => 'Entrevista o Hablando a cámara',
                'viral_mechanism' => 'Tema polémico + respuesta contraintuitiva + genera comentarios divisivos',
            ],
            5 => [
                'name' => 'El X% de [grupo] falla por esto',
                'structure' => 'Se afirma que la mayoría comete un error. Se explica cuál es y cómo evitarlo.',
                'suggested_format' => 'Selfie o Hablando a cámara',
                'viral_mechanism' => 'Narrativa personal + estadística que impacta + rompe una creencia establecida',
            ],
            6 => [
                'name' => "Resultado tangible que 'cae del cielo'",
                'structure' => 'Se muestra visualmente un resultado (dinero, notificación, cifra) de forma simple y cotidiana.',
                'suggested_format' => 'Personajes o Hablando a cámara',
                'viral_mechanism' => 'Presenta algo complejo como algo simple y accesible + escena cotidiana reconocible',
            ],
            7 => [
                'name' => 'Preguntas frecuentes del nicho con respuestas que sorprenden',
                'structure' => 'Se responden preguntas típicas del sector de forma directa y con respuestas contraintuitivas.',
                'suggested_format' => 'Entrevista solo (el creador se hace las preguntas)',
                'viral_mechanism' => 'Revela un "secreto" + formato orgánico y natural + desmonta mitos del sector',
            ],
            8 => [
                'name' => 'Señales de que X',
                'structure' => 'Lista de señales o síntomas que indican que algo está ocurriendo (bueno o malo). Regla clave: terminar siempre pidiendo que comenten su experiencia.',
                'suggested_format' => 'Hablando a cámara + llamada a la acción en comentarios al final',
                'viral_mechanism' => 'Toca una inseguridad compartida + el CTA activa el algoritmo por comentarios',
            ],
            9 => [
                'name' => 'Esta X funciona 9 de cada 10 veces',
                'structure' => 'Se presenta una solución específica con una promesa de efectividad muy alta.',
                'suggested_format' => 'Hablando a cámara',
                'viral_mechanism' => 'Promesa concreta + solución inesperada + deseo universal + tono seguro y directo',
            ],
            10 => [
                'name' => 'Afirmación + Vehículo inesperado para lograrlo',
                'structure' => 'Se hace una afirmación que suena a exageración. Luego se explica el mecanismo simple detrás. Regla clave: cuanto más simple sea el vehículo y mayor el resultado prometido, mejor.',
                'suggested_format' => 'Hablando a cámara',
                'viral_mechanism' => 'Solución simple a problema grande + fácil de ejecutar + resultado claro',
            ],
            11 => [
                'name' => '¿A qué te dedicas? (con respuesta aspiracional)',
                'structure' => 'Alguien le pregunta al creador a qué se dedica. La respuesta genera curiosidad e inspiración. Funciona especialmente bien cuando la profesión es inusual o genera curiosidad.',
                'suggested_format' => 'Entrevista + PUV',
                'viral_mechanism' => 'Genera identificación inconsciente: "si hago lo mismo, consigo sus resultados"',
            ],
            12 => [
                'name' => 'Crear necesidad de X (a través de inseguridad social)',
                'structure' => 'Se escenifica una situación social tensa. La solución del creador resuelve esa tensión. Funciona muy bien cuando hay un conflicto cotidiano con una resolución no obvia.',
                'suggested_format' => 'Personajes + Hablando a cámara',
                'viral_mechanism' => 'Toca una inseguridad social reconocible + solución presentada de forma indirecta',
            ],
            13 => [
                'name' => 'Cómo tener X (con congruencia visible del creador)',
                'structure' => 'El creador explica cómo conseguir algo que él mismo ya tiene (cuerpo, dinero, habilidad). Regla clave: el creador debe ser prueba visible de lo que enseña.',
                'suggested_format' => 'Hablando a cámara + elemento visual (pizarra, gráfico, objeto)',
                'viral_mechanism' => 'Contenido educativo + congruencia del creador como prueba viva',
            ],
            14 => [
                'name' => '¿Cuánto cuesta realmente X?',
                'structure' => 'Se desmonta un mito sobre el costo de algo cotidiano. La respuesta suele sorprender.',
                'suggested_format' => 'Hablando a cámara dinámico',
                'viral_mechanism' => 'Resuelve una duda práctica que genera debates familiares o sociales',
            ],
            15 => [
                'name' => '¿Qué es mejor, X o Y? (debate popular / tecnología / cultura)',
                'structure' => 'Se toma partido en un debate conocido. Se dan razones claras y se invita a opinar. A diferencia de la plantilla 4, esta es más de cultura pop / tecnología, menos emocional.',
                'suggested_format' => 'Hablando a cámara dinámico',
                'viral_mechanism' => 'Genera comentarios y debate + razones fáciles de entender + polariza la audiencia',
            ],
            16 => [
                'name' => 'Pierde todo al dejar X',
                'structure' => 'Se muestra (con personajes o de forma visual) todo lo que alguien pierde cuando abandona un hábito, herramienta o práctica.',
                'suggested_format' => 'Personajes',
                'viral_mechanism' => 'Muestra resultados tangibles de no actuar + mensaje simple con alto impacto emocional',
            ],
            17 => [
                'name' => 'Papá/Mamá, ¿me das / me prestas X?',
                'structure' => 'Un hijo pide algo. El padre/madre usa ese momento para dar una lección financiera o de vida. Funciona muy bien en finanzas, negocios, inversión y crianza.',
                'suggested_format' => 'Personajes + Hablando a cámara',
                'viral_mechanism' => 'Situación familiar universal + toque aspiracional + enseñanza con ejemplo tangible',
            ],
            18 => [
                'name' => 'Misma idea, enfoque distinto',
                'structure' => 'Se toma una pregunta conocida (¿X o Y?) y se da una tercera respuesta que nadie esperaba. Principio clave: una misma idea ganadora puede publicarse en 2 formatos distintos para doble alcance.',
                'suggested_format' => 'POV + Entrevista',
                'viral_mechanism' => 'La respuesta inesperada rompe el patrón mental del espectador',
            ],
            19 => [
                'name' => '¿Cuál es tu X favorito?',
                'structure' => 'Pregunta abierta sobre algo que todos conocen y tienen una opinión.',
                'suggested_format' => 'Entrevista',
                'viral_mechanism' => 'Alta identificación + invita a comentar + tema universal y reconocible',
            ],
            20 => [
                'name' => 'Reutilización de formato (misma idea, distinto formato)',
                'structure' => 'Principio: si una idea funciona en formato entrevista, grabarla también en POV o hablando a cámara. Cada formato llega a un segmento distinto de audiencia. Regla de uso: solo aplicar a ideas que ya han demostrado tracción.',
                'suggested_format' => null,
                'viral_mechanism' => 'Multiplica el alcance llegando a segmentos distintos con la misma idea probada',
            ],
            21 => [
                'name' => 'Voy a hacer que este desconocido logre X en N días',
                'structure' => 'El creador toma a alguien sin experiencia y lo lleva a un resultado concreto en un plazo definido.',
                'suggested_format' => 'Vlog / Documental / Reto',
                'viral_mechanism' => 'Genera expectativa + la audiencia quiere ver si funciona + alta retención por episodios',
            ],
            22 => [
                'name' => 'Estas son las X cosas más importantes de tu vida',
                'structure' => 'Se usa un gancho de alta expectativa ("hoyo") que obliga al espectador a quedarse. Regla clave: el contenido debe estar a la altura del gancho o se pierde credibilidad.',
                'suggested_format' => 'Hablando a cámara',
                'viral_mechanism' => 'El gancho genera una promesa tan grande que la audiencia no puede no escuchar',
            ],
            23 => [
                'name' => 'Esto se hizo viral por X razón (análisis de viralidad)',
                'structure' => 'Se toma algo que ya es viral (persona, video, tendencia) y se explica el mecanismo detrás. Regla clave: usar figuras o eventos virales en su momento de máxima relevancia.',
                'suggested_format' => 'Hablando a cámara',
                'viral_mechanism' => 'Doble curiosidad (el fenómeno viral + la explicación) + efecto palanca de la fama ajena',
            ],
            24 => [
                'name' => 'Road Rage / Situación extrema con moraleja',
                'structure' => 'Se escenifica una situación de conflicto o tensión alta. La resolución aplica la metodología del creador.',
                'suggested_format' => 'Personajes / Escena dramática',
                'viral_mechanism' => 'Alta carga emocional + situación reconocible + mensaje universal (ego vs. amor, reacción vs. respuesta)',
            ],
            25 => [
                'name' => 'Facto controversial que todos piensan pero nadie dice',
                'structure' => 'El creador dice en voz alta algo que la mayoría piensa pero no se atreve a expresar. Regla clave: debe ser algo que el creador realmente piense y pueda defender; la autenticidad es clave.',
                'suggested_format' => 'Hablando a cámara / Conversación directa',
                'viral_mechanism' => 'Honestidad brutal + tabú social roto + genera polarización y comentarios',
            ],
            26 => [
                'name' => 'Tengo X para Y',
                'structure' => 'El creador muestra que tiene un recurso importante y lo va a usar para algo concreto.',
                'suggested_format' => 'Vlog dinámico',
                'viral_mechanism' => 'Genera curiosidad inmediata sobre el origen del recurso + aspiracionalidad',
            ],
            27 => [
                'name' => 'Transformación interior (historia cotidiana con moraleja profunda)',
                'structure' => 'Un hecho pequeño y ordinario se convierte en la puerta de entrada a una lección de vida profunda. Regla clave: la historia debe ser específica y personal, no genérica; la especificidad genera credibilidad.',
                'suggested_format' => 'Hablando a cámara (simple, sin efectos)',
                'viral_mechanism' => 'Alta identificación con el hecho cotidiano + moraleja que solo puede dar ese creador',
            ],
            28 => [
                'name' => 'Yo ya tengo X (manifestación o mentalidad explicada)',
                'structure' => 'El creador afirma que ya posee algo que aparentemente aún no tiene. Explica la mentalidad detrás.',
                'suggested_format' => 'Entrevista',
                'viral_mechanism' => 'Alta tasa de repetición (se ve varias veces para entenderlo) + concepto que desafía la lógica convencional',
            ],
            29 => [
                'name' => '¿Cuánto dinero / cuánto lograste este año?',
                'structure' => 'Se le pide a alguien con resultados que revele sus cifras reales, paso a paso. A diferencia de la plantilla 3, esta es más específica, con desglose temporal (mes a mes, trimestre a trimestre).',
                'suggested_format' => 'Entrevista',
                'viral_mechanism' => 'Contraste de expectativas + aspiracionalidad + cifras que impactan',
            ],
            30 => [
                'name' => 'Así es como X lo hacen',
                'structure' => 'Se explica el mecanismo real detrás de un resultado que parece inalcanzable o misterioso.',
                'suggested_format' => 'Hablando a cámara con visuales (gráfico, diagrama, objeto)',
                'viral_mechanism' => 'Democratiza un conocimiento que parecía exclusivo + presentado de forma simple y visual',
            ],
        ];
    }
}
