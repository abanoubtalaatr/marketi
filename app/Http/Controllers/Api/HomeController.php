<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SearchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'categories' => Category::where('is_active', true)->limit(10)->get(),
            'popular_products' => Product::where('is_active', true)
                ->orderByDesc('rating_count')
                ->limit(10)
                ->with(['category', 'brand'])
                ->get(),
            'best_for_you' => Product::where('is_active', true)
                ->orderByDesc('rating')
                ->limit(10)
                ->with(['category', 'brand'])
                ->get(),
            'brands' => Brand::where('is_active', true)->limit(10)->get(),
        ];

        if ($user) {
            $data['search_history'] = SearchHistory::where('user_id', $user->id)
                ->latest()
                ->limit(10)
                ->pluck('query');

            $orderedProductIds = $user->orders()
                ->with('items')
                ->get()
                ->flatMap(fn ($order) => $order->items->pluck('product_id'))
                ->unique()
                ->take(10);

            $data['buy_again'] = Product::whereIn('id', $orderedProductIds)
                ->where('is_active', true)
                ->with(['category', 'brand'])
                ->get();
        }

        return $this->success($data);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1']);

        if ($request->user()) {
            SearchHistory::create([
                'user_id' => $request->user()->id,
                'query' => $request->q,
            ]);
        }

        $products = Product::where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->q}%")
                    ->orWhere('description', 'like', "%{$request->q}%");
            })
            ->with(['category', 'brand'])
            ->paginate(20);

        return $this->success($products);
    }
}
