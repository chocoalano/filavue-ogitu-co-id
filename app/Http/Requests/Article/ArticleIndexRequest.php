<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleIndexRequest extends FormRequest
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
            'tag' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'oldest', 'az', 'za'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'search.max' => 'Pencarian maksimal 120 karakter.',
            'tag.max' => 'Tag maksimal 80 karakter.',
            'sort.in' => 'Pilihan pengurutan tidak valid.',
            'page.integer' => 'Halaman tidak valid.',
            'page.min' => 'Halaman minimal 1.',
        ];
    }

    /**
     * @return array{
     *   search:string|null,
     *   tag:string|null,
     *   sort:'newest'|'oldest'|'az'|'za',
     *   page:int
     * }
     */
    public function payload(): array
    {
        /** @var 'newest'|'oldest'|'az'|'za' $sort */
        $sort = (string) ($this->input('sort') ?: 'newest');

        return [
            'search' => $this->filled('search') ? trim((string) $this->string('search')) : null,
            'tag' => $this->filled('tag') ? trim((string) $this->string('tag')) : null,
            'sort' => in_array($sort, ['newest', 'oldest', 'az', 'za'], true) ? $sort : 'newest',
            'page' => max(1, (int) $this->integer('page', 1)),
        ];
    }
}
