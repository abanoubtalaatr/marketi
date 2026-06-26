<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $brands = Brand::where('is_active', true)
            ->withCount('products')
            ->get();

        return $this->success($brands);
    }

    public function show(Brand $brand): JsonResponse
    {
        $brand->loadCount('products');

        return $this->success($brand);
    }

    public function products(Brand $brand): JsonResponse
    {
        $products = $brand->products()
            ->where('is_active', true)
            ->with(['category', 'images'])
            ->paginate(20);

        return $this->success($products);
    }
}
