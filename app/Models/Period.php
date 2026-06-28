<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use Database\Factories\PeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Periodo de planificación de contenido por marca (p. ej. "Julio 2026").
 * Estado "publicado" o "borrador": junto con el estado de la pieza, decide si esta
 * es accesible desde la URL pública.
 */
class Period extends Model
{
    /** @use HasFactory<PeriodFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PeriodStatus::class,
        ];
    }

    public function isPublished(): bool
    {
        return $this->status === PeriodStatus::Publicado;
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contentPieces(): HasMany
    {
        return $this->hasMany(ContentPiece::class);
    }
}
