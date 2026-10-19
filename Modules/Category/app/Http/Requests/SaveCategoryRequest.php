<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($this->route('category'))]];
    }

    public function messages(): array
    {
        return ['name.required' => 'Vui lòng nhập tên danh mục.', 'name.unique' => 'Tên danh mục đã tồn tại.'];
    }
}
