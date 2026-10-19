<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Category\Models\Category;
use Modules\Product\Http\Requests\ProductFilterRequest;
use Modules\Product\Http\Requests\SaveProductRequest;
use Modules\Product\Models\Product;

class ProductController extends Controller
{
    public function index(ProductFilterRequest $request): View
    {
        $filters = $request->validated();
        $query = Product::query()->with('category');

        if ($request->filled('keyword')) {
            $keyword = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['keyword']);
            $query->whereRaw("name LIKE ? ESCAPE '!'", ['%'.$keyword.'%']);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $filters['category_id']);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $filters['max_price']);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderByDesc('created_at'),
        };
        $products = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return view('product::index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('product::create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(SaveProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return to_route('products.index')->with('success', 'Đã tạo sản phẩm.');
    }

    public function edit(Product $product): View
    {
        return view('product::edit', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(SaveProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return to_route('products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return to_route('products.index')->with('success', 'Đã xóa sản phẩm.');
    }
}
