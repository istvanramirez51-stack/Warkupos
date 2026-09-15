<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{   
        // pending → diproses → selesai → paid
    public const STATUS_PENDING  = 'pending';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI  = 'selesai';
    public const STATUS_PAID     = 'paid';
    
    protected $fillable = [
        'table_id',
        'user_id',
        'status',
        'source',
        'total',
        'notes',
    ];

    // Relasi
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    public function transaction()
    {
    return $this->hasOne(Transaction::class);
    }
}