<?php

namespace App\Http\Requests\Seller;

use App\Enums\AdsPlacement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->seller !== null;
    }

    public function rules(): array
    {
        $sellerId = $this->user()?->seller?->id;

        return [
            'product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')
                    ->where('seller_id', $sellerId)
                    ->whereNull('deleted_at'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'placement' => ['required', Rule::enum(AdsPlacement::class)],
            'budget' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
