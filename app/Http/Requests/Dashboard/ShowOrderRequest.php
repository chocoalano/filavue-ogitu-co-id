<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class ShowOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'order' => ['required', 'integer', 'min:1'],
            'customer_id' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'order.required' => 'Parameter order wajib diisi.',
            'order.integer' => 'Parameter order harus berupa angka.',
            'order.min' => 'Parameter order minimal 1.',
            'customer_id.prohibited' => 'Data customer ditentukan dari autentikasi.',
        ];
    }

    /** @return array{order_id:int} */
    public function payload(): array
    {
        return [
            'order_id' => (int) $this->integer('order'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order' => $this->route('order'),
        ]);
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
