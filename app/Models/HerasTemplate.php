<?php

namespace App\Models;

use Database\Factories\HerasTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HerasTemplate extends Model
{
    /** @use HasFactory<HerasTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'number',
        'viral_referent_id',
        'name',
        'structure',
        'suggested_format',
        'viral_mechanism',
        'reference_url',
        'preview_image_url',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
        ];
    }

    public function viralReferent(): BelongsTo
    {
        return $this->belongsTo(ViralReferent::class);
    }

    public function winningIdeas(): HasMany
    {
        return $this->hasMany(WinningIdea::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return "#{$this->number} · {$this->name}";
    }
}
