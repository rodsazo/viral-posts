<?php

namespace App\Models;

use App\Enums\PainType;
use Database\Factories\PainFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dolor / Problema / Deseo de un seguidor ideal. Hermano de Question/Belief.
 */
class Pain extends Model
{
    /** @use HasFactory<PainFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'ideal_follower_id',
        'type',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'type' => PainType::class,
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function idealFollower(): BelongsTo
    {
        return $this->belongsTo(IdealFollower::class);
    }
}
