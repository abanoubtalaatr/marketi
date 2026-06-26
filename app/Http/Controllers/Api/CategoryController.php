<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->get();

        return $this->success($categories);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');

        return $this->success($category);
    }

    public function products(Category $category): JsonResponse
    {
        $products = $category->products()
            ->where('is_active', true)
            ->with(['brand', 'images'])
            ->paginate(20);

        return $this->success($products);
    }
}
