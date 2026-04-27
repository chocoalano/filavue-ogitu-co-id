<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'qty' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'qty.required' => 'Jumlah produk wajib diisi.',
            'qty.integer'  => 'Jumlah produk harus berupa angka.',
            'qty.min'      => 'Jumlah produk minimal 1.',
            'qty.max'      => 'Jumlah produk terlalu banyak.',
        ];
    }

    public function qty(): int
    {
        return (int) $this->validated('qty');
    }
}
