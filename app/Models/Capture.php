<?php

namespace App\Models;

use Database\Factories\CaptureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Capture extends Model
{
    /** @use HasFactory<CaptureFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'body',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
