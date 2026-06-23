<?php

namespace App\Models;

use Database\Factories\HookTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Plantilla de gancho (hook): la primera parte de una pieza de contenido.
 * Catálogo GLOBAL (no escopado a la marca), como HerasTemplate.
 */
class HookTemplate extends Model
{
    /** @use HasFactory<HookTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'viral_referent_id',
        'objective',
        'notes',
        'example_generic',
        'example_health',
        'example_sex',
        'example_money',
        'example_personal_dev',
        'reference_url',
        'real_examples',
        'icon',
    ];

    protected function casts(): array
    {
        return [
            'real_examples' => 'array',
        ];
    }

    public function viralReferent(): BelongsTo
    {
        return $this->belongsTo(ViralReferent::class);
    }
}
