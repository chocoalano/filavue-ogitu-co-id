<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CreateWalletTopupTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:10000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal topup wajib diisi.',
            'amount.numeric' => 'Nominal topup harus berupa angka.',
            'amount.min' => 'Nominal topup minimal Rp 10.000.',
            'notes.max' => 'Catatan topup maksimal 500 karakter.',
        ];
    }

    /**
     * @return array{amount:float,notes:string|null}
     */
    public function payload(): array
    {
        $notes = trim((string) $this->input('notes', ''));

        return [
            'amount' => (float) $this->input('amount'),
            'notes' => $notes !== '' ? $notes : null,
        ];
    }

    private function resolveAuthenticatedCustomer(): ?Customer
    {
        $customer = $this->user('customer');

        if ($customer instanceof Customer) {
            return $customer;
        }

        $tokenable = $this->user('sanctum');

        return $tokenable instanceof Customer ? $tokenable : null;
    }
}
