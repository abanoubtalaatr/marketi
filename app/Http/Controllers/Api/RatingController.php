<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    use ApiResponse;

    public function index(Product $product): JsonResponse
    {
        $ratings = $product->ratings()->with('user:id,name')->latest()->paginate(20);

        return $this->success($ratings);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'stars' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $rating = Rating::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            ['stars' => $data['stars'], 'review' => $data['review'] ?? null]
        );

        $this->updateProductRating($product);

        return $this->success($rating, 'Rating saved', 201);
    }

    public function update(Request $request, Rating $rating): JsonResponse
    {
        if ($rating->user_id !== $request->user()->id) {
            return $this->error('Rating not found', 404);
        }

        $data = $request->validate([
            'stars' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $rating->update($data);
        $this->updateProductRating($rating->product);

        return $this->success($rating, 'Rating updated');
    }

    public function destroy(Request $request, Rating $rating): JsonResponse
    {
        if ($rating->user_id !== $request->user()->id) {
            return $this->error('Rating not found', 404);
        }

        $product = $rating->product;
        $rating->delete();
        $this->updateProductRating($product);

        return $this->success(null, 'Rating deleted');
    }

    private function updateProductRating(Product $product): void
    {
        $avg = $product->ratings()->avg('stars') ?? 0;
        $count = $product->ratings()->count();
        $product->update(['rating' => round($avg, 2), 'rating_count' => $count]);
    }
}
