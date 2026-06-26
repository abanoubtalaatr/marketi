<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with(['product.category', 'product.brand', 'product.images'])
            ->latest()
            ->paginate(20);

        return $this->success($favorites);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return $this->success($favorite->load('product'), 'Added to favorites', 201);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return $this->success(null, 'Removed from favorites');
    }
}
