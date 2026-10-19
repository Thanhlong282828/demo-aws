<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99',
                Rule::when($this->filled('min_price') && is_numeric($this->input('min_price')), ['gte:min_price'])],
            'sort' => ['nullable', Rule::in(['price_asc', 'price_desc', 'newest'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('products.index');
    }

    public function messages(): array
    {
        return ['max_price.gte' => 'Giá tối đa phải lớn hơn hoặc bằng giá tối thiểu.'];
    }
}
