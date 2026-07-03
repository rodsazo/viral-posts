<?php

namespace App\Models;

use App\Enums\ClientReviewStatus;
use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use App\Support\Rum;
use Database\Factories\ContentPieceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContentPiece extends Model
{
    /** @use HasFactory<ContentPieceFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'period_id',
        'ideal_follower_id',
        'winning_idea_id',
        'brand_character_id',
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
        'location',
        'equipment',
        'people',
        'client_notes',
        'client_review_status',
        'client_review_notes',
        'client_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'objective' => ContentObjective::class,
            'format' => ContentFormat::class,
            'status' => ContentStatus::class,
            'rating' => ContentRating::class,
            'client_review_status' => ClientReviewStatus::class,
            'published_at' => 'datetime',
            'client_reviewed_at' => 'datetime',
            'rum_factors' => 'array',
            'rum' => 'float',
        ];
    }

    protected static function booted(): void
    {
        // Toda pieza nace con un token público inadivinable para su vista de cliente.
        static::creating(function (ContentPiece $piece): void {
            $piece->public_token ??= Str::random(40);
        });

        // El RUM siempre se recalcula a partir de sus factores al guardar.
        static::saving(function (ContentPiece $piece): void {
            $piece->rum = Rum::compute($piece->rum_factors);
        });
    }

    /**
     * URL pública (sin login) para que un cliente externo previsualice y valide la pieza.
     */
    public function publicUrl(): string
    {
        return route('piece.public', $this->public_token);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * ¿Es accesible desde la URL pública? Dos condiciones: el estado de la pieza está
     * "de Lista para grabación en adelante" Y su periodo existe y está "Publicado".
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status->isReadyForClient()
            && $this->period !== null
            && $this->period->isPublished();
    }

    public function winningIdea(): BelongsTo
    {
        return $this->belongsTo(WinningIdea::class);
    }

    public function brandCharacter(): BelongsTo
    {
        return $this->belongsTo(BrandCharacter::class);
    }

    public function idealFollower(): BelongsTo
    {
        return $this->belongsTo(IdealFollower::class);
    }

    /**
     * Hilo de refinamiento (conversación con la IA) de la pieza, en orden cronológico.
     *
     * @return HasMany<PieceRefinement, $this>
     */
    public function refinements(): HasMany
    {
        return $this->hasMany(PieceRefinement::class)->orderBy('id');
    }

    /**
     * Preguntas que responde la pieza: ahora salen del SEGUIDOR IDEAL de la pieza
     * (la idea ganadora ya no cura preguntas). Colección vacía si no hay seguidor.
     *
     * @return Collection<int, Question>
     */
    public function derivedQuestions(): Collection
    {
        return $this->idealFollower?->questions()->orderBy('body')->get() ?? collect();
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
