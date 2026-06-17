<?php

namespace App\Models;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use App\Support\Rum;
use Database\Factories\ContentPieceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ContentPiece extends Model
{
    /** @use HasFactory<ContentPieceFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'winning_idea_id',
        'title',
        'objective',
        'format',
        'status',
        'hook',
        'story',
        'moral',
        'cta',
        'url',
        'preview_image_url',
        'published_at',
        'rating',
        'rum_factors',
        'rum',
    ];

    protected function casts(): array
    {
        return [
            'objective' => ContentObjective::class,
            'format' => ContentFormat::class,
            'status' => ContentStatus::class,
            'rating' => ContentRating::class,
            'published_at' => 'datetime',
            'rum_factors' => 'array',
            'rum' => 'float',
        ];
    }

    protected static function booted(): void
    {
        // El RUM siempre se recalcula a partir de sus factores al guardar.
        static::saving(function (ContentPiece $piece): void {
            $piece->rum = Rum::compute($piece->rum_factors);
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function winningIdea(): BelongsTo
    {
        return $this->belongsTo(WinningIdea::class);
    }

    /**
     * VISIBILIDAD MULTI-SALTO: preguntas que responde la pieza, a través de su
     * idea ganadora. Colección vacía si la pieza no tiene idea (decisión #5).
     *
     * @return Collection<int, Question>
     */
    public function derivedQuestions(): Collection
    {
        return $this->winningIdea?->questions ?? collect();
    }

    /**
     * VISIBILIDAD MULTI-SALTO: mitos y verdades derivados, a través de la idea
     * y sus preguntas (ContentPiece → WinningIdea → Questions → Beliefs).
     *
     * @return Collection<int, Belief>
     */
    public function derivedBeliefs(): Collection
    {
        return $this->winningIdea?->derivedBeliefs() ?? collect();
    }
}
