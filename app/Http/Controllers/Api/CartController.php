<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(private CartService $cartService) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $cart->load('items.product.images');

        return $this->success($cart);
    }

    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
            'size' => 'nullable|string',
        ]);

        $cart = $this->cartService->addItem(
            $request->user(),
            $data['product_id'],
            $data['quantity'] ?? 1,
            $data['size'] ?? null
        );

        return $this->success($cart, 'Item added to cart');
    }

    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:0']);

        $cart = $this->cartService->getOrCreateCart($request->user());
        $cart = $this->cartService->updateQuantity($cart, $itemId, $request->quantity);

        return $this->success($cart, 'Cart updated');
    }

    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $cart = $this->cartService->removeItem($cart, $itemId);

        return $this->success($cart, 'Item removed');
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $cart = $this->cartService->clear($cart);

        return $this->success($cart, 'Cart cleared');
    }
}
