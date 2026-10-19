<?php

namespace Modules\Category\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Modules\Category\Http\Requests\SaveCategoryRequest;
use Modules\Category\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['per_page' => 'sometimes|integer|min:1|max:100']);

        return CategoryResource::collection(Category::orderBy('id')->paginate($data['per_page'] ?? 15));
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function store(SaveCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))->additional(['message' => 'Category created.'])->response()->setStatusCode(201);
    }

    public function update(SaveCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return (new CategoryResource($category))->additional(['message' => 'Category updated.']);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages(['category' => 'Category has products.']);
        }
        $category->delete();

        return response()->json(['data' => null, 'message' => 'Category deleted.']);
    }
}
