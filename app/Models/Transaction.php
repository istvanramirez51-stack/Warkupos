<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'order_id',
        'user_id',
        'amount',
        'paid_amount',
        'change',
        'payment_method',
        'receipt_type',
    ];

    protected $casts = [
        'amount'      => 'integer',
        'paid_amount' => 'integer',
        'change'      => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}