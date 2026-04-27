<?php

namespace App\Http\Requests\ZennerAcademy\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', Rule::exists('contents_category', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:contents,slug'],
            'content' => ['nullable', 'string'],
            'file' => ['nullable', 'string', 'max:500'],
            'vlink' => ['nullable', 'string', 'url', 'max:500'],
            'status' => ['required', 'string', Rule::in(['published', 'draft'])],
            'created_by' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul konten wajib diisi.',
            'title.max' => 'Judul konten maksimal 255 karakter.',
            'slug.unique' => 'Slug sudah digunakan, gunakan yang lain.',
            'slug.max' => 'Slug maksimal 255 karakter.',
            'vlink.url' => 'Link video harus berupa URL yang valid.',
            'vlink.max' => 'Link video maksimal 500 karakter.',
            'status.required' => 'Status konten wajib diisi.',
            'status.in' => 'Status harus published atau draft.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'created_by.exists' => 'User pembuat tidak ditemukan.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'category_id' => $this->filled('category_id') ? (int) $this->input('category_id') : null,
            'title' => trim((string) $this->string('title')),
            'slug' => $this->filled('slug') ? trim((string) $this->string('slug')) : null,
            'content' => $this->input('content'),
            'file' => $this->filled('file') ? trim((string) $this->string('file')) : null,
            'vlink' => $this->filled('vlink') ? trim((string) $this->string('vlink')) : null,
            'status' => trim((string) $this->string('status')),
            'created_by' => $this->filled('created_by') ? (int) $this->input('created_by') : null,
        ];
    }
}
