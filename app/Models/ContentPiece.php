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
        'ideal_follower_id',
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

    public function idealFollower(): BelongsTo
    {
        return $this->belongsTo(IdealFollower::class);
    }

    /**
     * Preguntas que responde la pieza, a través de su idea ganadora (la idea cura
     * las preguntas). Colección vacía si la pieza no tiene idea.
     *
     * @return Collection<int, Question>
     */
    public function derivedQuestions(): Collection
    {
        return $this->winningIdea?->questions ?? collect();
    }

    /**
     * Mitos y verdades a tratar: ahora salen del SEGUIDOR IDEAL de la pieza
     * (el seguidor es el centro), no de las preguntas.
     *
     * @return Collection<int, Belief>
     */
    public function derivedBeliefs(): Collection
    {
        return $this->idealFollower?->beliefs()->orderBy('statement')->get() ?? collect();
    }

    /**
     * Dolores / problemas / deseos del seguidor ideal de la pieza.
     *
     * @return Collection<int, Pain>
     */
    public function derivedPains(): Collection
    {
        return $this->idealFollower?->pains()->orderBy('type')->orderBy('body')->get() ?? collect();
    }
}
