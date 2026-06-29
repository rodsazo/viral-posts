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
        'viral_referent_id',
        'name',
        'structure',
        'suggested_format',
        'viral_mechanism',
        'reference_url',
        'reference_urls',
        'preview_image_url',
    ];

    protected function casts(): array
    {
        return [
            'reference_urls' => 'array',
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
        return (string) $this->name;
    }
}
