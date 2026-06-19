<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de una generación de IA (asíncrona, vía cola). El Estudio crea el registro
 * en estado `processing`, encola el Job, y hace polling hasta `done`/`failed`.
 */
class AiGeneration extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_DONE = 'done';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'account_id',
        'user_id',
        'kind',
        'status',
        'result',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }
}
