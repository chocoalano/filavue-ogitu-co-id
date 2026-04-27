<?php

namespace App\Http\Requests\Newsletter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SubscribeNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email', ''))),
        ]);
    }

    public function email(): string
    {
        return (string) $this->validated('email');
    }
}
