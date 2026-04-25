<?php

namespace App\Http\Requests\Cart;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('status', ProductStatus::Published->value)],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}
