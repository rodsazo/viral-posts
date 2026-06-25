<?php

namespace App\Models;

use Database\Factories\HookTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Plantilla de gancho (hook): la primera parte de una pieza de contenido.
 * `account_id` nulo = gancho GLOBAL de referencia (admin/super admin); con marca =
 * gancho propio de esa marca (editable en el Estudio por su equipo).
 */
class HookTemplate extends Model
{
    /** @use HasFactory<HookTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'viral_referent_id',
        'name',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function viralReferent(): BelongsTo
    {
        return $this->belongsTo(ViralReferent::class);
    }

    /** ¿Es un gancho global de referencia (sin marca)? */
    public function isGlobal(): bool
    {
        return $this->account_id === null;
    }
}
