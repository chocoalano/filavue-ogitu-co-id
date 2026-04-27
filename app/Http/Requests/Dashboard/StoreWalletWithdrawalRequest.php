<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalletWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'integer', 'min:10000', 'multiple_of:500'],
            'password' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal withdrawal wajib diisi.',
            'amount.numeric' => 'Nominal withdrawal harus berupa angka.',
            'amount.integer' => 'Nominal withdrawal harus berupa bilangan bulat.',
            'amount.min' => 'Nominal withdrawal minimal Rp 10.000.',
            'amount.multiple_of' => 'Nominal withdrawal harus kelipatan Rp 500.',
            'password.required' => 'Password wajib diisi untuk konfirmasi withdrawal.',
            'notes.max' => 'Catatan withdrawal maksimal 500 karakter.',
        ];
    }

    /**
     * @return array{amount:float,password:string,notes:string|null}
     */
    public function payload(): array
    {
        $notes = trim((string) $this->input('notes', ''));

        return [
            'amount' => (float) $this->input('amount'),
            'password' => (string) $this->input('password'),
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
