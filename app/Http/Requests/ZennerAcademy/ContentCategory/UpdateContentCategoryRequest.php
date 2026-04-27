<?php

namespace App\Http\Requests\ZennerAcademy\ContentCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $categoryId = (int) $this->route('category');

        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('contents_category', 'id'), Rule::notIn([$categoryId])],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('contents_category', 'slug')->ignore($categoryId)],
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
            'parent_id.not_in' => 'Kategori tidak bisa menjadi induk dirinya sendiri.',
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $data = [];

        if ($this->has('parent_id')) {
            $data['parent_id'] = $this->filled('parent_id') ? (int) $this->input('parent_id') : null;
        }

        if ($this->has('name')) {
            $data['name'] = trim((string) $this->string('name'));
        }

        if ($this->has('slug')) {
            $data['slug'] = $this->filled('slug') ? trim((string) $this->string('slug')) : null;
        }

        return $data;
    }
}
