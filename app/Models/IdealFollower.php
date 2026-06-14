<?php

namespace App\Models;

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
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
