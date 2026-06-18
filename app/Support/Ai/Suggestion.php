<?php

namespace App\Support\Ai;

/**
 * Una sugerencia de IA, agnóstica del caso de uso. Es lo que consume el patrón
 * de UX reutilizable: una sugerencia puede afectar **uno o varios** campos (p. ej.
 * un guión afecta gancho/historia/moraleja/CTA a la vez).
 *
 * - `label`   título corto para la tarjeta/opción.
 * - `fields`  mapa campo => valor que se aplica SOLO si el usuario elige esta sugerencia.
 * - `preview` texto multilínea legible para que el usuario juzgue antes de elegir.
 */
class Suggestion
{
    /**
     * @param  array<string, string>  $fields
     */
    public function __construct(
        public string $label,
        public array $fields,
        public string $preview = '',
    ) {}

    /**
     * Forma serializable para llevarla a la capa de UI (Livewire / Filament).
     *
     * @return array{label: string, fields: array<string, string>, preview: string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'fields' => $this->fields,
            'preview' => $this->preview,
        ];
    }
}
