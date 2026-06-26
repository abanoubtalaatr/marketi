<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $products = Product::with(['category', 'brand'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20);

        return $this->success($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'main_image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'sizes' => 'nullable|array',
            'sizes.*.size' => 'required|string',
            'sizes.*.stock_quantity' => 'required|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('main_image')) {
            $data['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($request->has('sizes')) {
            foreach ($request->sizes as $size) {
                $product->sizes()->create($size);
            }
        }

        return $this->success($product->load(['category', 'brand', 'sizes']), 'Product created', 201);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->success($product->load(['category', 'brand', 'images', 'sizes']));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'main_image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('main_image')) {
            $data['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        $product->update($data);

        return $this->success($product->fresh(['category', 'brand', 'sizes']), 'Product updated');
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->success(null, 'Product deleted');
    }
}
