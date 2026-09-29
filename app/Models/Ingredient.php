<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'stock_qty',
        'min_stock',
    ];

    protected $casts = [
        'stock_qty' => 'decimal:2',
        'min_stock' => 'decimal:2',
    ];

    /**
     * Cek apakah stok di bawah / sama dengan threshold (STK-03).
     */
    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->min_stock;
    }

    // Relasi resep — dipakai STK-04 (P2): auto-kurangi stok saat menu terjual
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_ingredients')
            ->withPivot('qty_used');
    }
}