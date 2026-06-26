<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true)
            ->with(['category', 'brand', 'images']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->has('sort')) {
            match ($request->sort) {
                'price_asc' => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'rating' => $query->orderByDesc('rating'),
                default => $query->latest(),
            };
        } else {
            $query->latest();
        }

        return $this->success($query->paginate(20));
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'brand', 'images', 'sizes', 'ratings.user']);

        return $this->success($product);
    }
}
