<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use Illuminate\Foundation\Http\FormRequest;

class SyncWalletTopupStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->resolveAuthenticatedCustomer();
        $walletTransaction = $this->route('walletTransaction');

        return $customer !== null
            && $walletTransaction instanceof CustomerWalletTransaction
            && (int) $walletTransaction->customer_id === (int) $customer->id
            && strtolower((string) $walletTransaction->type) === 'topup';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
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
