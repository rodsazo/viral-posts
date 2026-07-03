<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un mensaje del hilo de refinamiento de un Personaje de Marca.
 *
 * - role = user       → instrucción del creador. `body` la contiene.
 * - role = assistant  → `body` es una nota breve de los cambios y `proposal` la versión
 *   propuesta del personaje (los campos). Solo se aplica al pulsar "Usar esta versión".
 */
class CharacterRefinement extends Model
{
    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'brand_character_id',
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

    public function brandCharacter(): BelongsTo
    {
        return $this->belongsTo(BrandCharacter::class);
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
