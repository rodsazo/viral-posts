<?php

namespace App\Models;

use App\Enums\BeliefType;
use Database\Factories\BeliefFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Belief extends Model
{
    /** @use HasFactory<BeliefFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'ideal_follower_id',
        'type',
        'statement',
        'stance',
    ];

    protected function casts(): array
    {
        return [
            'type' => BeliefType::class,
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
