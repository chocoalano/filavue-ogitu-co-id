<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'telp' => ['required', 'string', 'max:20'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'telp.required' => 'Nomor WhatsApp wajib diisi.',
            'telp.max' => 'Format nomor WhatsApp tidak valid.',
        ];
    }
}
