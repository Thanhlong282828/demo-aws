<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'price.required' => 'Vui lòng nhập giá.',
            'price.numeric' => 'Giá phải là số.',
            'price.gt' => 'Giá phải lớn hơn 0.',
            'price.decimal' => 'Giá có tối đa 2 chữ số thập phân.',
            'stock.required' => 'Vui lòng nhập tồn kho.',
            'stock.integer' => 'Tồn kho phải là số nguyên.',
            'stock.min' => 'Tồn kho không được âm.',
        ];
    }
}
