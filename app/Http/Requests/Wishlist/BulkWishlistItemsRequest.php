<?php

namespace App\Http\Requests\Wishlist;

use Illuminate\Foundation\Http\FormRequest;

class BulkWishlistItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'ids.required' => 'Pilih setidaknya satu item.',
            'ids.min'      => 'Pilih setidaknya satu item.',
        ];
    }

    /** @return array<int> */
    public function itemIds(): array
    {
        return $this->validated('ids');
    }
}
