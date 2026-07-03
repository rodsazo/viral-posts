<?php

namespace App\Support\Ai;

/**
 * Contexto de un turno de refinamiento conversacional de un Personaje de Marca. El bloque
 * de sistema estable (metodología + documento actual del personaje) se cachea; la
 * conversación (instrucciones + notas) viaja en messages. Refina sobre la versión GUARDADA
 * del personaje (el documento base); "aplicar" la actualiza y la siguiente vuelta parte de ahí.
 */
class RefineCharacterContext
{
    /**
     * @param  array<int, array{role: string, body: ?string}>  $history  turnos previos (sin la nueva instrucción)
     */
    public function __construct(
        public string $instruction,
        public string $characterDocument,
        public array $history = [],
    ) {}

    public function toSystem(): string
    {
        $role = (string) config('ai.character.system.role');
        $method = (string) config('ai.character.system.method');

        $lines = [];
        $lines[] = $role;
        $lines[] = '';
        $lines[] = 'Trabajas en una conversación de refinamiento de un personaje de marca ya construido. '
            .'El creador pedirá ajustes ("cambia el enemigo", "haz el arquetipo más cercano", "otra firma verbal"…). '
            .'Devuelves SIEMPRE el personaje COMPLETO (las 9 secciones) con SOLO ese cambio aplicado, conservando el '
            .'resto, más una nota breve de qué cambiaste. Mantén la coherencia del framework:';
        $lines[] = '';
        $lines[] = $method;
        $lines[] = '';
        $lines[] = 'DOCUMENTO ACTUAL DEL PERSONAJE (punto de partida — modifícalo, no lo reinventes):';
        $lines[] = $this->characterDocument;

        return implode("\n", $lines);
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    public function toMessages(): array
    {
        $messages = [];

        foreach ($this->history as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $content = trim((string) ($turn['body'] ?? ''));

            if ($content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $this->instruction];

        return $messages;
    }
}
