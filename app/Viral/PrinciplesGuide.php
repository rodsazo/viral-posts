<?php

namespace App\Viral;

/**
 * Una guía de principios rectores de viralidad (versionable): "Víctor Heras 2026",
 * "Víctor Heras 2025", "Álvaro Guijón 2026"… Sus instrucciones se inyectan al prompt
 * de generación/refinamiento cuando el creador la elige. Una guía nueva = una clase
 * nueva en app/Viral/Principles + registrarla en ViralCatalog::GUIDES.
 */
abstract class PrinciplesGuide
{
    /** Clave estable que se persiste en las piezas (no cambiarla una vez en uso). */
    abstract public function key(): string;

    /** Etiqueta para el select del Estudio. */
    abstract public function label(): string;

    /** Instrucciones que se añaden al prompt. */
    abstract public function instructions(): string;
}
