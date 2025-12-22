<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'reference',
        'description',
        'status',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function business()
    {
        return $this->hasOneThrough(
            Business::class,
            Wallet::class,
            'id',
            'id',
            'wallet_id',
            'business_id'
        );
    }
}
