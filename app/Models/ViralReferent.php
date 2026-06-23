<?php

namespace App\Models;

use Database\Factories\ViralReferentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViralReferent extends Model
{
    /** @use HasFactory<ViralReferentFactory> */
    use HasFactory;

    protected $fillable = [
        'niche_id',
        'name',
        'notes',
        'instagram_url',
    ];

    public function niche(): BelongsTo
    {
        return $this->belongsTo(Niche::class);
    }

    public function herasTemplates(): HasMany
    {
        return $this->hasMany(HerasTemplate::class);
    }

    public function hookTemplates(): HasMany
    {
        return $this->hasMany(HookTemplate::class);
    }
}
