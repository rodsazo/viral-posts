<?php

namespace App\Models;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
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
        'published_at',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'objective' => ContentObjective::class,
            'format' => ContentFormat::class,
            'status' => ContentStatus::class,
            'rating' => ContentRating::class,
            'published_at' => 'datetime',
        ];
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
