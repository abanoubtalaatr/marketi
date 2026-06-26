<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'subtotal', 'delivery_fee', 'discount', 'total'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items->sum(fn (CartItem $item) => $item->price * $item->quantity);
        $this->update([
            'subtotal' => $subtotal,
            'total' => $subtotal + $this->delivery_fee - $this->discount,
        ]);
    }
}
