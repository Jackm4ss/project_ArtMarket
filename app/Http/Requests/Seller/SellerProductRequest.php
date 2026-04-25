<?php

namespace App\Http\Requests\Seller;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['seller', 'admin']) ?? false;
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : null;

        return [
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:1000', 'max:999999999'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'product_type' => ['required', Rule::in(['ready', 'preorder'])],
            'material' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'weight_gram' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'location' => ['nullable', 'string', 'max:255'],
            'preorder_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productData(): array
    {
        return collect($this->validated())
            ->except(['image', 'remove_image'])
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();
    }
}
