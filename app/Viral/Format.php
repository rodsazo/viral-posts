<?php

namespace App\Viral;

/**
 * Un formato viral: fórmula concreta con estructura establecida que se "rellena" con
 * el contenido. Corresponde 1:1 con un caso del enum ContentFormat (el "Formato
 * principal" de la pieza). Puede tener SUBFORMATOS (sin versiones). Un formato nuevo =
 * una clase nueva en app/Viral/Formats + registrarla en ViralCatalog::FORMATS.
 */
abstract class Format
{
    /** Valor del enum ContentFormat al que corresponde (p. ej. 'personajes'). */
    abstract public function key(): string;

    /** Instrucciones del formato que se añaden al prompt. */
    abstract public function instructions(): string;

    /**
     * Subformatos disponibles dentro de este formato.
     *
     * @return array<int, Subformat>
     */
    public function subformats(): array
    {
        return [];
    }
}
