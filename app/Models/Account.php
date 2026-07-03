<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use Database\Factories\AccountFactory;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Account extends Model implements HasAvatar
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'description',
        'brand_promise',
        'main_offers',
        'ideal_customer_profile',
        'is_active',
    ];

    /** URL pública del logo de la marca, o null si no tiene. */
    public function logoUrl(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        $disk = config('filesystems.brand_disk', 'public');
        $url = Storage::disk($disk)->url($this->logo_path);

        // En el disco público local devolvemos una URL relativa a la raíz (p. ej.
        // "/storage/brand-logos/x.png"): se sirve desde el mismo host:puerto que la app y
        // evita el desajuste de origen cuando APP_URL no coincide con el puerto (localhost
        // vs :8000). En S3 (producción) devolvemos la URL absoluta del bucket tal cual.
        if ($disk === 'public') {
            return preg_replace('#^https?://[^/]+#', '', $url) ?: $url;
        }

        return $url;
    }

    /** Avatar de la marca para el selector de marca de Filament (admin). */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logoUrl();
    }

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

            // Token inadivinable para el tablero público del cliente (/m/{token}).
            $account->public_token ??= Str::random(40);
        });
    }

    /** URL pública (sin login) del tablero del cliente para esta marca. */
    public function publicBoardUrl(): string
    {
        return route('brand.public', $this->public_token);
    }

    /** Último periodo "Publicado" de la marca (el que ve el cliente), o null. */
    public function latestPublishedPeriod(): ?Period
    {
        return $this->periods()
            ->where('status', PeriodStatus::Publicado)
            ->latest('id')
            ->first();
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

    public function pains(): HasMany
    {
        return $this->hasMany(Pain::class);
    }

    public function winningIdeas(): HasMany
    {
        return $this->hasMany(WinningIdea::class);
    }

    public function contentPieces(): HasMany
    {
        return $this->hasMany(ContentPiece::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class);
    }

    public function brandCharacters(): HasMany
    {
        return $this->hasMany(BrandCharacter::class);
    }

    public function aiGenerations(): HasMany
    {
        return $this->hasMany(AiGeneration::class);
    }

    public function captures(): HasMany
    {
        return $this->hasMany(Capture::class);
    }

    public function contentCtas(): HasMany
    {
        return $this->hasMany(ContentCta::class);
    }

    /** Ganchos propios de la marca (los globales tienen account_id nulo). */
    public function hookTemplates(): HasMany
    {
        return $this->hasMany(HookTemplate::class);
    }
}
