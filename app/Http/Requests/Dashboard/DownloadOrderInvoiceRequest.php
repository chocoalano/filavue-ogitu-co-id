<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class DownloadOrderInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->resolveAuthenticatedCustomer();
        $order = $this->route('order');

        return $customer !== null
            && $order instanceof Order
            && (int) $order->customer_id === (int) $customer->id;
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
