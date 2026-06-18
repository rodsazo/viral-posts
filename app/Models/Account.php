<?php

namespace App\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Account $account) {
            if (blank($account->slug)) {
                $account->slug = static::uniqueSlug($account->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'marca';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(AccountInvitation::class);
    }

    public function idealFollowers(): HasMany
    {
        return $this->hasMany(IdealFollower::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function beliefs(): HasMany
    {
        return $this->hasMany(Belief::class);
    }

    public function winningIdeas(): HasMany
    {
        return $this->hasMany(WinningIdea::class);
    }

    public function contentPieces(): HasMany
    {
        return $this->hasMany(ContentPiece::class);
    }

    public function captures(): HasMany
    {
        return $this->hasMany(Capture::class);
    }
}
