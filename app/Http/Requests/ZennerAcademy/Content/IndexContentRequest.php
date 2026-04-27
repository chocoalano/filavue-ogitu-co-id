<?php

namespace App\Http\Requests\ZennerAcademy\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in(['published', 'draft'])],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'search.max' => 'Pencarian maksimal 120 karakter.',
            'status.in' => 'Status harus published atau draft.',
            'category_id.integer' => 'ID kategori tidak valid.',
            'per_page.max' => 'Per halaman maksimal 100 item.',
        ];
    }

    /**
     * @return array{
     *   search: string|null,
     *   status: string|null,
     *   category_id: int|null,
     *   per_page: int,
     *   page: int
     * }
     */
    public function payload(): array
    {
        return [
            'search' => $this->filled('search') ? trim((string) $this->string('search')) : null,
            'status' => $this->filled('status') ? trim((string) $this->string('status')) : null,
            'category_id' => $this->filled('category_id') ? (int) $this->input('category_id') : null,
            'per_page' => max(1, min(100, (int) $this->integer('per_page', 15))),
            'page' => max(1, (int) $this->integer('page', 1)),
        ];
    }
}
