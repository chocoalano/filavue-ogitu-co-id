<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty'        => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists'   => 'Produk tidak ditemukan.',
            'qty.required'        => 'Jumlah produk wajib diisi.',
            'qty.integer'         => 'Jumlah produk harus berupa angka.',
            'qty.min'             => 'Jumlah produk minimal 1.',
            'qty.max'             => 'Jumlah produk terlalu banyak.',
        ];
    }

    public function productId(): int
    {
        return (int) $this->validated('product_id');
    }

    public function qty(): int
    {
        return (int) $this->validated('qty');
    }
}
