<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

class CartService
{
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['delivery_fee' => 15.00]
        );
    }

    public function addItem(User $user, int $productId, int $quantity = 1, ?string $size = null): Cart
    {
        $cart = $this->getOrCreateCart($user);
        $product = Product::findOrFail($productId);

        $existing = $cart->items()
            ->where('product_id', $productId)
            ->where('size', $size)
            ->first();

        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $quantity]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'size' => $size,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        $cart->load('items.product');
        $cart->recalculate();

        return $cart->fresh(['items.product']);
    }

    public function updateQuantity(Cart $cart, int $itemId, int $quantity): Cart
    {
        $item = $cart->items()->findOrFail($itemId);

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        $cart->load('items.product');
        $cart->recalculate();

        return $cart->fresh(['items.product']);
    }

    public function removeItem(Cart $cart, int $itemId): Cart
    {
        $cart->items()->where('id', $itemId)->delete();
        $cart->load('items.product');
        $cart->recalculate();

        return $cart->fresh(['items.product']);
    }

    public function clear(Cart $cart): Cart
    {
        $cart->items()->delete();
        $cart->recalculate();

        return $cart->fresh(['items.product']);
    }
}
