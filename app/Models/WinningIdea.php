<?php

namespace App\Models;

use Database\Factories\WinningIdeaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class WinningIdea extends Model
{
    /** @use HasFactory<WinningIdeaFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'title',
        'concept',
        'viral_mechanism',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class);
    }

    public function contentPieces(): HasMany
    {
        return $this->hasMany(ContentPiece::class);
    }

    /**
     * VISIBILIDAD MULTI-SALTO: mitos y verdades derivados a través de las
     * preguntas relacionadas (WinningIdea → Questions → Beliefs), sin duplicados.
     *
     * @return Collection<int, Belief>
     */
    public function derivedBeliefs(): Collection
    {
        return $this->questions
            ->loadMissing('beliefs')
            ->flatMap->beliefs
            ->unique('id')
            ->values();
    }
}
