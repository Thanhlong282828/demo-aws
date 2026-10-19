<?php

namespace Modules\Category\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Category\Http\Requests\SaveCategoryRequest;
use Modules\Category\Models\Category;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('category::index', ['categories' => Category::withCount('products')->orderBy('name')->paginate(10)]);
    }

    public function create(): View
    {
        return view('category::create');
    }

    public function store(SaveCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return to_route('categories.index')->with('success', 'Đã tạo danh mục.');
    }

    public function show(Category $category): View
    {
        return view('category::show', ['category' => $category, 'products' => $category->products()->orderBy('name')->paginate(10)]);
    }

    public function edit(Category $category): View
    {
        return view('category::edit', compact('category'));
    }

    public function update(SaveCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return to_route('categories.index')->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $message = 'Không thể xóa danh mục vì đang có sản phẩm.';
        if ($category->products()->exists()) {
            return to_route('categories.index')->with('error', $message);
        }
        try {
            $category->delete();
        } catch (QueryException $exception) {
            if (! in_array((int) ($exception->errorInfo[1] ?? 0), [1451, 19], true)) {
                throw $exception;
            }

            return to_route('categories.index')->with('error', $message);
        }

        return to_route('categories.index')->with('success', 'Đã xóa danh mục.');
    }
}
