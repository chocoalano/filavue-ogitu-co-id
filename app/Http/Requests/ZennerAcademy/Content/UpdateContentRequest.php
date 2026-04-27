<?php

namespace App\Http\Requests\ZennerAcademy\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $contentId = (int) $this->route('content');

        return [
            'category_id' => ['nullable', 'integer', Rule::exists('contents_category', 'id')],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('contents', 'slug')->ignore($contentId)],
            'content' => ['nullable', 'string'],
            'file' => ['nullable', 'string', 'max:500'],
            'vlink' => ['nullable', 'string', 'url', 'max:500'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['published', 'draft'])],
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

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $data = [];

        if ($this->has('category_id')) {
            $data['category_id'] = $this->filled('category_id') ? (int) $this->input('category_id') : null;
        }

        if ($this->has('title')) {
            $data['title'] = trim((string) $this->string('title'));
        }

        if ($this->has('slug')) {
            $data['slug'] = $this->filled('slug') ? trim((string) $this->string('slug')) : null;
        }

        if ($this->has('content')) {
            $data['content'] = $this->input('content');
        }

        if ($this->has('file')) {
            $data['file'] = $this->filled('file') ? trim((string) $this->string('file')) : null;
        }

        if ($this->has('vlink')) {
            $data['vlink'] = $this->filled('vlink') ? trim((string) $this->string('vlink')) : null;
        }

        if ($this->has('status')) {
            $data['status'] = trim((string) $this->string('status'));
        }

        if ($this->has('created_by')) {
            $data['created_by'] = $this->filled('created_by') ? (int) $this->input('created_by') : null;
        }

        return $data;
    }
}
