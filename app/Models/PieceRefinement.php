<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un mensaje del hilo de refinamiento de una pieza (conversación estilo chat con la IA).
 *
 * - role = user       → una instrucción del creador ("más cálido", "más corto"). `body` la contiene.
 * - role = assistant  → la respuesta de la IA: `body` es una nota breve de los cambios y
 *   `proposal` es la versión propuesta del guión ({hook, story, moral, cta}). La propuesta NO
 *   toca la pieza: solo se aplica cuando el usuario pulsa "Usar esta versión".
 */
class PieceRefinement extends Model
{
    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'content_piece_id',
        'user_id',
        'role',
        'body',
        'proposal',
    ];

    protected function casts(): array
    {
        return [
            'proposal' => 'array',
        ];
    }

    public function contentPiece(): BelongsTo
    {
        return $this->belongsTo(ContentPiece::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
