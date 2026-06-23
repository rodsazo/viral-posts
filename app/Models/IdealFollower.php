<?php

namespace App\Models;

use App\Enums\AwarenessLevel;
use Database\Factories\IdealFollowerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdealFollower extends Model
{
    /** @use HasFactory<IdealFollowerFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'awareness_level',
    ];

    protected function casts(): array
    {
        return [
            'awareness_level' => AwarenessLevel::class,
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function beliefs(): HasMany
    {
        return $this->hasMany(Belief::class);
    }

    public function pains(): HasMany
    {
        return $this->hasMany(Pain::class);
    }
}
