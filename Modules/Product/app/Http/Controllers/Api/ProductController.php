<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Product\Http\Requests\SaveProductRequest;
use Modules\Product\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['per_page' => 'sometimes|integer|min:1|max:100']);

        return ProductResource::collection(Product::with('category')->orderBy('id')->paginate($data['per_page'] ?? 15));
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('category'));
    }

    public function store(SaveProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return (new ProductResource($product->load('category')))->additional(['message' => 'Product created.'])->response()->setStatusCode(201);
    }

    public function update(SaveProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return (new ProductResource($product->load('category')))->additional(['message' => 'Product updated.']);
    }

    public function destroy(Product $product): JsonResponse
    {

        $product->delete();

        return response()->json(['data' => null, 'message' => 'Product deleted.']);
    }
}
