<?php

namespace App\Support;

/**
 * RUM — Relevancia Única de Mercado (metodología Víctor Heras).
 *
 * RUM = amplitud × intensidad × universalidad × inmediatez × independencia
 * Cada factor vale 1, 1.3 ó 1.5848. El producto se redondea a 1 decimal.
 * Un RUM alto → más probabilidad de viralidad.
 */
class Rum
{
    /**
     * Definición de los 5 factores: etiqueta, ayuda y opciones (valor => etiqueta).
     * Los valores van como string porque las claves de array PHP no admiten float.
     *
     * @var array<string, array{label: string, help: string, options: array<string, string>}>
     */
    public const FACTORS = [
        'amplitud' => [
            'label' => 'Amplitud poblacional',
            'help' => 'Cuántas personas pueden verse afectadas.',
            'options' => [
                '1' => 'Muy poca. Muy específico.',
                '1.3' => 'Media. Afecta a algunos.',
                '1.5848' => 'Alta. Casi a cualquier persona',
            ],
        ],
        'intensidad' => [
            'label' => 'Intensidad percibida',
            'help' => 'Cuánto importa el tema cuando aparece (supervivencia económica, estatus, salud, relaciones).',
            'options' => [
                '1' => 'Nada. No resuelve un problema',
                '1.3' => 'Media. Resuelve una molestia.',
                '1.5848' => 'Alta. Fuerte molestia/deseo.',
            ],
        ],
        'universalidad' => [
            'label' => 'Universalidad contextual',
            'help' => 'Cuánto contexto (cultura, profesión, nivel técnico, situación) se necesita.',
            'options' => [
                '1' => 'Requiere mucho contexto.',
                '1.3' => 'Requiere poco contexto.',
                '1.5848' => 'No requiere contexto.',
            ],
        ],
        'inmediatez' => [
            'label' => 'Inmediatez de aplicabilidad',
            'help' => 'Cuándo podría aplicar esto.',
            'options' => [
                '1' => 'No se aplica pronto.',
                '1.3' => 'Puede aplicarse pronto.',
                '1.5848' => 'Puede aplicarse hoy o en cualquier momento',
            ],
        ],
        'independencia' => [
            'label' => 'Independencia del nicho',
            'help' => 'Resuena en personas de cualquier nicho, profesión o interés.',
            'options' => [
                '1' => 'Fuertemente dependiente.',
                '1.3' => 'Depende de industria / sector.',
                '1.5848' => 'No depende de nicho.',
            ],
        ],
    ];

    /**
     * @return array<string, string>
     */
    public static function optionsFor(string $key): array
    {
        return self::FACTORS[$key]['options'];
    }

    /**
     * Producto de los 5 factores, redondeado a 1 decimal. Null si falta alguno.
     *
     * @param  array<string, mixed>|null  $factors
     */
    public static function compute(?array $factors): ?float
    {
        if (! is_array($factors)) {
            return null;
        }

        $product = 1.0;

        foreach (array_keys(self::FACTORS) as $key) {
            $value = $factors[$key] ?? null;

            if ($value === null || $value === '') {
                return null;
            }

            $product *= (float) $value;
        }

        return round($product, 1);
    }

    /** Color semántico de Filament según el RUM. */
    public static function color(?float $rum): string
    {
        if ($rum === null) {
            return 'gray';
        }

        return match (true) {
            $rum <= 5 => 'danger',
            $rum <= 7 => 'warning',
            default => 'success',
        };
    }

    /** Color de Flux (frontend del Estudio) según el RUM. */
    public static function fluxColor(?float $rum): string
    {
        return match (self::color($rum)) {
            'danger' => 'red',
            'warning' => 'yellow',
            'success' => 'green',
            default => 'zinc',
        };
    }
}
