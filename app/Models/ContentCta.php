<?php

namespace App\Models;

use App\Enums\CtaCategory;
use Database\Factories\ContentCtaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Llamada a la acción (CTA) de la marca. Cada marca puede tener una o varias; el
 * generador de piezas permite elegir una para que el guión fluya hacia ella.
 */
class ContentCta extends Model
{
    /** @use HasFactory<ContentCtaFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'category',
        'text',
    ];

    protected function casts(): array
    {
        return [
            'category' => CtaCategory::class,
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
