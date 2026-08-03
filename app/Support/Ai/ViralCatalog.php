<?php

namespace App\Support\Ai;

use App\Viral\Format;
use App\Viral\Formats;
use App\Viral\Principles\VictorHeras2026;
use App\Viral\PrinciplesGuide;
use App\Viral\Subformat;

/**
 * Catálogo de conocimiento viral: principios rectores (guías versionables) y formatos/
 * subformatos, definidos como CLASES independientes en app/Viral (una por archivo).
 * Añadir una guía o formato = crear la clase + registrarla aquí. Expone las opciones
 * para los selects y los fragmentos de instrucciones que se inyectan en el prompt.
 */
class ViralCatalog
{
    /** @var array<int, class-string<PrinciplesGuide>> */
    private const GUIDES = [
        VictorHeras2026::class,
    ];

    /** @var array<int, class-string<Format>> */
    private const FORMATS = [
        Formats\Personajes::class,
        Formats\Rankings::class,
        Formats\Selfie::class,
        Formats\HablandoACamara::class,
        Formats\HablandoACamaraVisual::class,
        Formats\Pov::class,
        Formats\Podcast::class,
        Formats\Puv::class,
        Formats\Entrevista::class,
        Formats\Vlog::class,
        Formats\DocumentalReto::class,
    ];

    // ── Principios rectores ─────────────────────────────────────────────────────

    /**
     * Opciones para el select de principios rectores.
     *
     * @return array<string, string> clave => etiqueta
     */
    public function principlesOptions(): array
    {
        return collect($this->guides())->map(fn (PrinciplesGuide $g): string => $g->label())->all();
    }

    public function isValidPrinciples(?string $key): bool
    {
        return $key !== null && array_key_exists($key, $this->guides());
    }

    /** Instrucciones de la guía de principios elegida (null si no hay o no existe). */
    public function principlesInstructions(?string $key): ?string
    {
        $guide = $this->guides()[$key] ?? null;

        if ($guide === null) {
            return null;
        }

        $text = trim($guide->instructions());

        return $text !== '' ? $text : null;
    }

    // ── Formatos y subformatos ──────────────────────────────────────────────────

    public function hasSubformats(?string $formatValue): bool
    {
        return filled($this->subformatOptions($formatValue));
    }

    /**
     * Subformatos del formato dado.
     *
     * @return array<string, string> clave => etiqueta
     */
    public function subformatOptions(?string $formatValue): array
    {
        return collect($this->subformatsOf($formatValue))
            ->map(fn (Subformat $s): string => $s->label())
            ->all();
    }

    public function isValidSubformat(?string $formatValue, ?string $subformatKey): bool
    {
        return $subformatKey !== null && array_key_exists($subformatKey, $this->subformatsOf($formatValue));
    }

    /**
     * Guía de formato para el prompt: instrucciones del formato principal + (si aplica)
     * las del subformato elegido. Null si el formato no está en el catálogo.
     */
    public function formatGuide(?string $formatValue, ?string $subformatKey = null): ?string
    {
        $format = $formatValue !== null ? ($this->formats()[$formatValue] ?? null) : null;

        if ($format === null) {
            return null;
        }

        $parts = [trim($format->instructions())];

        $subformat = $this->subformatsOf($formatValue)[$subformatKey] ?? null;
        if ($subformat !== null) {
            $parts[] = trim($subformat->instructions());
        }

        $parts = array_values(array_filter($parts, fn (string $p): bool => $p !== ''));

        return filled($parts) ? implode("\n\n", $parts) : null;
    }

    // ── Registro ────────────────────────────────────────────────────────────────

    /** @return array<string, PrinciplesGuide> */
    private function guides(): array
    {
        $out = [];
        foreach (self::GUIDES as $class) {
            $guide = new $class;
            $out[$guide->key()] = $guide;
        }

        return $out;
    }

    /** @return array<string, Format> */
    private function formats(): array
    {
        $out = [];
        foreach (self::FORMATS as $class) {
            $format = new $class;
            $out[$format->key()] = $format;
        }

        return $out;
    }

    /** @return array<string, Subformat> */
    private function subformatsOf(?string $formatValue): array
    {
        $format = $formatValue !== null ? ($this->formats()[$formatValue] ?? null) : null;

        if ($format === null) {
            return [];
        }

        $out = [];
        foreach ($format->subformats() as $subformat) {
            $out[$subformat->key()] = $subformat;
        }

        return $out;
    }
}
