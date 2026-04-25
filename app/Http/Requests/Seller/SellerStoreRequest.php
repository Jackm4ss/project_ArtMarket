<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['seller', 'admin']) ?? false;
    }

    public function rules(): array
    {
        $sellerId = $this->user()?->seller?->id;

        return [
            'store_name' => ['required', 'string', 'max:255', Rule::unique('sellers', 'store_name')->ignore($sellerId)],
            'bio' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:80'],
        ];
    }
}
