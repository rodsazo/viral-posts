<?php

namespace App\Viral;

/**
 * Un subformato dentro de un Format (p. ej. "Diálogo escéptico ↔ convencido" dentro de
 * "Personajes"). Sin versiones. Sus instrucciones se añaden al prompt DESPUÉS de las del
 * formato principal.
 */
abstract class Subformat
{
    /** Clave estable que se persiste en las piezas (no cambiarla una vez en uso). */
    abstract public function key(): string;

    /** Etiqueta para el select del Estudio. */
    abstract public function label(): string;

    /** Instrucciones que se añaden al prompt. */
    abstract public function instructions(): string;

    /**
     * Referencias virales del subformato (posts reales de ejemplo). Se suman a las del
     * formato principal en el botón "Ver ejemplo" del Estudio.
     *
     * @return array<int, Reference>
     */
    public function references(): array
    {
        return [];
    }
}
