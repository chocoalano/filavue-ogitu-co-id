<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListWalletTransactionRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:120'],
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', Rule::in(['all', 'topup', 'withdrawal', 'bonus', 'purchase', 'refund', 'tax'])],
            'status' => ['nullable', 'string', Rule::in(['all', 'pending', 'completed', 'failed', 'cancelled'])],
            'direction' => ['nullable', 'string', Rule::in(['all', 'credit', 'debit'])],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'amount_min' => ['nullable', 'numeric', 'min:0'],
            'amount_max' => ['nullable', 'numeric', 'min:0', 'gte:amount_min'],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'oldest', 'highest', 'lowest'])],
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
            'search.max' => 'Pencarian maksimal 120 karakter.',
            'q.max' => 'Pencarian maksimal 120 karakter.',
            'type.in' => 'Tipe transaksi wallet tidak valid.',
            'status.in' => 'Status transaksi wallet tidak valid.',
            'direction.in' => 'Arah mutasi wallet tidak valid.',
            'payment_method.max' => 'Metode pembayaran maksimal 100 karakter.',
            'date_from.date' => 'Format date_from tidak valid.',
            'date_to.date' => 'Format date_to tidak valid.',
            'date_to.after_or_equal' => 'date_to harus sama atau setelah date_from.',
            'amount_min.numeric' => 'amount_min harus berupa angka.',
            'amount_min.min' => 'amount_min minimal 0.',
            'amount_max.numeric' => 'amount_max harus berupa angka.',
            'amount_max.min' => 'amount_max minimal 0.',
            'amount_max.gte' => 'amount_max harus lebih besar atau sama dengan amount_min.',
            'sort.in' => 'Urutan data tidak valid.',
        ];
    }

    /**
     * @return array{
     *   page:int,
     *   per_page:int,
     *   filters:array{
     *     search:string|null,
     *     type:string,
     *     status:string,
     *     direction:string,
     *     payment_method:string|null,
     *     date_from:string|null,
     *     date_to:string|null,
     *     amount_min:float|null,
     *     amount_max:float|null,
     *     sort:string
     *   }
     * }
     */
    public function payload(): array
    {
        $search = $this->filled('search')
            ? trim((string) $this->input('search'))
            : (
                $this->filled('q')
                    ? trim((string) $this->input('q'))
                    : null
            );

        return [
            'page' => max(1, (int) $this->integer('page', 1)),
            'per_page' => max(1, min(100, (int) $this->integer('per_page', 15))),
            'filters' => [
                'search' => $search !== null && $search !== '' ? $search : null,
                'type' => strtolower(trim((string) $this->input('type', 'all'))),
                'status' => strtolower(trim((string) $this->input('status', 'all'))),
                'direction' => strtolower(trim((string) $this->input('direction', 'all'))),
                'payment_method' => $this->filled('payment_method') ? trim((string) $this->input('payment_method')) : null,
                'date_from' => $this->filled('date_from') ? trim((string) $this->input('date_from')) : null,
                'date_to' => $this->filled('date_to') ? trim((string) $this->input('date_to')) : null,
                'amount_min' => $this->filled('amount_min') ? (float) $this->input('amount_min') : null,
                'amount_max' => $this->filled('amount_max') ? (float) $this->input('amount_max') : null,
                'sort' => strtolower(trim((string) $this->input('sort', 'newest'))),
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
