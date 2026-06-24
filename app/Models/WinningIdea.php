<?php

namespace App\Models;

use App\Enums\ValidationStatus;
use App\Enums\ViralMechanism;
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
        'heras_template_id',
        'title',
        'concept',
        'viral_mechanism',
        'example_urls',
    ];

    protected function casts(): array
    {
        return [
            'viral_mechanism' => ViralMechanism::class,
            'example_urls' => 'array',
        ];
    }

    /**
     * Una idea está "Validada" si tiene al menos un ejemplo real de viralidad
     * (URLs de posts de otros creadores); si no, queda pendiente de validación.
     */
    public function isValidated(): bool
    {
        return filled($this->example_urls);
    }

    public function validationStatus(): ValidationStatus
    {
        return $this->isValidated() ? ValidationStatus::Validated : ValidationStatus::Pending;
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function herasTemplate(): BelongsTo
    {
        return $this->belongsTo(HerasTemplate::class);
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
