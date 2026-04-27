<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitOrderItemReviewRequest extends FormRequest
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
        $order = $this->route('order');
        $orderId = $order instanceof Order ? (int) $order->id : 0;

        return [
            'customer_id' => ['prohibited'],
            'order_id' => ['prohibited'],
            'product_id' => ['prohibited'],
            'is_approved' => ['prohibited'],
            'is_verified_purchase' => ['prohibited'],
            'order_item_id' => [
                'required',
                'integer',
                Rule::exists('order_items', 'id')->where('order_id', $orderId),
            ],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'customer_id.prohibited' => 'Data customer ditentukan dari autentikasi.',
            'order_id.prohibited' => 'Data order ditentukan dari endpoint.',
            'product_id.prohibited' => 'Data produk ditentukan dari order item.',
            'is_approved.prohibited' => 'Status approval diatur admin.',
            'is_verified_purchase.prohibited' => 'Status verifikasi pembelian ditentukan sistem.',
            'order_item_id.required' => 'Item order wajib dipilih.',
            'order_item_id.integer' => 'Item order tidak valid.',
            'order_item_id.exists' => 'Item order tidak ditemukan pada pesanan ini.',
            'rating.required' => 'Rating wajib diisi.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 1.',
            'rating.max' => 'Rating maksimal 5.',
            'title.max' => 'Judul review maksimal 255 karakter.',
            'comment.required' => 'Komentar review wajib diisi.',
            'comment.min' => 'Komentar review minimal 3 karakter.',
            'comment.max' => 'Komentar review maksimal 2000 karakter.',
        ];
    }

    /**
     * @return array{
     *   order_item_id:int,
     *   rating:int,
     *   title:?string,
     *   comment:string
     * }
     */
    public function payload(): array
    {
        $title = trim((string) $this->input('title', ''));

        return [
            'order_item_id' => (int) $this->integer('order_item_id'),
            'rating' => (int) $this->integer('rating'),
            'title' => $title !== '' ? $title : null,
            'comment' => trim((string) $this->input('comment', '')),
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
