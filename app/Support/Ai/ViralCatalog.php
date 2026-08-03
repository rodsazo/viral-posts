<?php

namespace App\Support\Ai;

use App\Enums\ContentFormat;

/**
 * Lee el catálogo de conocimiento viral (config/viral.php): principios rectores
 * (versionables) y formatos/subformatos (indexados por el valor de ContentFormat).
 * Expone las opciones para los selects y los fragmentos de instrucciones que se
 * inyectan en el prompt de generación/refinamiento de guiones.
 */
class ViralCatalog
{
    // ── Principios rectores ─────────────────────────────────────────────────────

    /**
     * Opciones para el select de principios rectores.
     *
     * @return array<string, string> clave => etiqueta
     */
    public function principlesOptions(): array
    {
        return collect((array) config('viral.principles.guides', []))
            ->map(fn ($guide): string => (string) ($guide['label'] ?? ''))
            ->filter()
            ->all();
    }

    public function isValidPrinciples(?string $key): bool
    {
        return $key !== null && array_key_exists($key, (array) config('viral.principles.guides', []));
    }

    /** Instrucciones de la guía de principios elegida (null si no hay o no existe). */
    public function principlesInstructions(?string $key): ?string
    {
        if (! $this->isValidPrinciples($key)) {
            return null;
        }

        $text = trim((string) config("viral.principles.guides.{$key}.instructions", ''));

        return $text !== '' ? $text : null;
    }

    // ── Formatos y subformatos ──────────────────────────────────────────────────

    private function formatExists(?string $formatValue): bool
    {
        return $formatValue !== null
            && ContentFormat::tryFrom($formatValue) !== null
            && is_array(config("viral.formats.{$formatValue}"));
    }

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
        if (! $this->formatExists($formatValue)) {
            return [];
        }

        return collect((array) config("viral.formats.{$formatValue}.subformats", []))
            ->map(fn ($sub): string => (string) ($sub['label'] ?? ''))
            ->filter()
            ->all();
    }

    public function isValidSubformat(?string $formatValue, ?string $subformatKey): bool
    {
        return $subformatKey !== null && array_key_exists($subformatKey, $this->subformatOptions($formatValue));
    }

    /**
     * Guía de formato para el prompt: instrucciones del formato principal + (si aplica)
     * las del subformato elegido. Null si no hay formato con instrucciones.
     */
    public function formatGuide(?string $formatValue, ?string $subformatKey = null): ?string
    {
        if (! $this->formatExists($formatValue)) {
            return null;
        }

        $parts = [];

        $main = trim((string) config("viral.formats.{$formatValue}.instructions", ''));
        if ($main !== '') {
            $parts[] = $main;
        }

        if ($this->isValidSubformat($formatValue, $subformatKey)) {
            $sub = trim((string) config("viral.formats.{$formatValue}.subformats.{$subformatKey}.instructions", ''));
            if ($sub !== '') {
                $parts[] = $sub;
            }
        }

        return filled($parts) ? implode("\n\n", $parts) : null;
    }
}
