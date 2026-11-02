<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'vendor_id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.discount_price' => 'nullable|numeric|min:0|lte:variants.*.price',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.SKU' => 'required|string|max:255',
            'variants.*.barcode' => 'nullable|string|max:255',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.dimensions' => 'nullable|string|max:255',
            'variants.*.size_id' => 'nullable|exists:attribute_values,id',
            'variants.*.color_id' => 'nullable|exists:attribute_values,id',
        ];
    }
}
