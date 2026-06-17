<?php

namespace App\Models;

use App\Enums\TeamRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AccountInvitation extends Model
{
    public const EXPIRY_DAYS = 7;

    protected $fillable = [
        'account_id',
        'email',
        'role',
        'token',
        'accepted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => TeamRole::class,
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AccountInvitation $invitation): void {
            $invitation->token ??= Str::random(48);
            $invitation->expires_at ??= now()->addDays(self::EXPIRY_DAYS);
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function acceptanceUrl(): string
    {
        return route('invitations.show', $this->token);
    }
}
