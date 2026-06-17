<?php

namespace App\Models;

use Database\Factories\NicheFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Niche extends Model
{
    /** @use HasFactory<NicheFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    public function viralReferents(): HasMany
    {
        return $this->hasMany(ViralReferent::class);
    }
}
