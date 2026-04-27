<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'customer_id' => ['prohibited'],
            'q' => ['nullable', 'string', 'max:120'],
            'status' => [
                'nullable',
                'string',
                Rule::in(['all', 'unpaid', 'pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']),
            ],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'oldest', 'highest', 'lowest'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'page.integer' => 'Parameter page harus berupa angka.',
            'page.min' => 'Parameter page minimal 1.',
            'per_page.integer' => 'Parameter per_page harus berupa angka.',
            'per_page.min' => 'Parameter per_page minimal 1.',
            'per_page.max' => 'Parameter per_page maksimal 100.',
            'customer_id.prohibited' => 'Data customer ditentukan dari autentikasi.',
            'q.max' => 'Pencarian maksimal 120 karakter.',
            'status.in' => 'Status order tidak valid.',
            'sort.in' => 'Urutan data tidak valid.',
            'date_from.date' => 'Format date_from tidak valid.',
            'date_to.date' => 'Format date_to tidak valid.',
            'date_to.after_or_equal' => 'date_to harus sama atau setelah date_from.',
        ];
    }

    /**
     * @return array{
     *   page:int,
     *   per_page:int,
     *   filters:array{
     *     q:string|null,
     *     status:string,
     *     sort:string,
     *     date_from:string|null,
     *     date_to:string|null
     *   }
     * }
     */
    public function payload(): array
    {
        return [
            'page' => max(1, (int) $this->integer('page', 1)),
            'per_page' => max(1, min(100, (int) $this->integer('per_page', 10))),
            'filters' => [
                'q' => $this->filled('q') ? trim((string) $this->string('q')) : null,
                'status' => strtolower(trim((string) $this->input('status', 'all'))),
                'sort' => strtolower(trim((string) $this->input('sort', 'newest'))),
                'date_from' => $this->filled('date_from') ? trim((string) $this->input('date_from')) : null,
                'date_to' => $this->filled('date_to') ? trim((string) $this->input('date_to')) : null,
            ],
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
