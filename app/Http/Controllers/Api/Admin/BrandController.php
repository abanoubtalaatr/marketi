<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(Brand::withCount('products')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        return $this->success($brand, 'Brand created', 201);
    }

    public function show(Brand $brand): JsonResponse
    {
        return $this->success($brand->loadCount('products'));
    }

    public function update(Request $request, Brand $brand): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($data);

        return $this->success($brand, 'Brand updated');
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $brand->delete();

        return $this->success(null, 'Brand deleted');
    }
}
