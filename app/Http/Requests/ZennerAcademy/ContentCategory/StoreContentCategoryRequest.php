<?php

namespace App\Http\Requests\ZennerAcademy\ContentCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('contents_category', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:contents_category,slug'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'slug.unique' => 'Slug sudah digunakan, gunakan yang lain.',
            'slug.max' => 'Slug maksimal 255 karakter.',
            'parent_id.exists' => 'Kategori induk tidak ditemukan.',
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return [
            'parent_id' => $this->filled('parent_id') ? (int) $this->input('parent_id') : null,
            'name' => trim((string) $this->string('name')),
            'slug' => $this->filled('slug') ? trim((string) $this->string('slug')) : null,
        ];
    }
}
