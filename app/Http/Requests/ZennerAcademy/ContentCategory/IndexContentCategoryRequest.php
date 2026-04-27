<?php

namespace App\Http\Requests\ZennerAcademy\ContentCategory;

use Illuminate\Foundation\Http\FormRequest;

class IndexContentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'parents_only' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'parents_only.boolean' => 'Parameter parents_only harus bernilai boolean.',
        ];
    }

    /**
     * @return array{parents_only: bool}
     */
    public function payload(): array
    {
        return [
            'parents_only' => $this->boolean('parents_only', false),
        ];
    }
}
