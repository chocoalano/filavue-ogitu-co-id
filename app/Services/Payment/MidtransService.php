<?php

namespace App\Services\Payment;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    /**
     * Buat Snap token via Midtrans API.
     *
     * @throws \RuntimeException jika API Midtrans gagal
     */
    public function createSnapToken(Order $order, Cart $cart, Customer $customer): string
    {
        $serverKey = $this->midtransServerKey();

        if ($serverKey === '') {
            throw new \RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $baseUrl = $this->snapTransactionUrl();

        $itemDetails = $this->buildItemDetails($order, $cart);
        $grossAmount = $this->sumItemDetails($itemDetails);

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_no,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customer->name,
                'email' => $customer->email ?? '',
                'phone' => $customer->phone ?? '',
            ],
            'item_details' => $itemDetails,
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl, $payload);

        if ($response->failed()) {
            $errorMsg = $response->json('error_messages.0') ?? $response->body();
            throw new \RuntimeException('Midtrans API error: '.$errorMsg);
        }

        return $response->json('token');
    }

    /**
     * Buat Snap token untuk order existing (digunakan untuk fitur bayar ulang / bayar sekarang).
     *
     * @throws \RuntimeException jika API Midtrans gagal
     */
    public function createSnapTokenForOrder(Order $order, Customer $customer): string
    {
        $serverKey = $this->midtransServerKey();

        if ($serverKey === '') {
            throw new \RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $baseUrl = $this->snapTransactionUrl();

        $itemDetails = $this->buildItemDetailsFromOrder($order);
        $grossAmount = $this->sumItemDetails($itemDetails);

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_no,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customer->name,
                'email' => $customer->email ?? '',
                'phone' => $customer->phone ?? '',
            ],
            'item_details' => $itemDetails,
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl, $payload);

        if ($response->failed()) {
            $errorMsg = $response->json('error_messages.0') ?? $response->body();
            throw new \RuntimeException('Midtrans API error: '.$errorMsg);
        }

        $token = $response->json('token');

        if (! is_string($token) || trim($token) === '') {
            throw new \RuntimeException('Midtrans API error: token Snap tidak tersedia.');
        }

        return $token;
    }

    /**
     * Buat Snap token untuk topup wallet customer.
     *
     * @throws \RuntimeException jika API Midtrans gagal
     */
    public function createSnapTokenForWalletTopup(string $transactionRef, float $amount, Customer $customer): string
    {
        $serverKey = $this->midtransServerKey();

        if ($serverKey === '') {
            throw new \RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $baseUrl = $this->snapTransactionUrl();

        $orderId = mb_substr(trim($transactionRef), 0, 50);
        $grossAmount = $this->toMidtransAmount($amount);

        if ($orderId === '') {
            throw new \RuntimeException('Referensi transaksi topup Midtrans tidak valid.');
        }

        if ($grossAmount <= 0) {
            throw new \RuntimeException('Nominal topup Midtrans tidak valid.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customer->name,
                'email' => $customer->email ?? '',
                'phone' => $customer->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => 'WALLET-TOPUP',
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Top Up Wallet',
                ],
            ],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl, $payload);

        if ($response->failed()) {
            $errorMsg = $response->json('error_messages.0') ?? $response->body();
            throw new \RuntimeException('Midtrans API error: '.$errorMsg);
        }

        $token = $response->json('token');

        if (! is_string($token) || trim($token) === '') {
            throw new \RuntimeException('Midtrans API error: token Snap topup tidak tersedia.');
        }

        return $token;
    }

    /**
     * Ambil status transaksi dari Midtrans berdasarkan nomor order.
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException jika API Midtrans gagal
     */
    public function getTransactionStatus(string $orderNo): array
    {
        $serverKey = $this->midtransServerKey();

        if ($serverKey === '') {
            throw new \RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $baseUrl = $this->transactionStatusApiBaseUrl();

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->get("{$baseUrl}/{$orderNo}/status");

        if ($response->failed()) {
            $errorMsg = $response->json('status_message')
                ?? $response->json('error_messages.0')
                ?? $response->body();

            throw new \RuntimeException('Midtrans API error: '.$errorMsg);
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new \RuntimeException('Midtrans API error: respons status transaksi tidak valid.');
        }

        return $payload;
    }

    /**
     * @return list<array{id: string, price: int, quantity: int, name: string}>
     */
    private function buildItemDetails(Order $order, Cart $cart): array
    {
        $itemDetails = $cart->items->map(function ($item): array {
            return [
                'id' => (string) $item->product_id,
                'price' => $this->toMidtransAmount($item->unit_price),
                'quantity' => max(1, (int) $item->qty),
                'name' => $this->formatItemName((string) $item->product_name),
            ];
        })->values()->all();

        $shippingAmount = $this->toMidtransAmount($order->shipping_amount);
        $taxAmount = $this->toMidtransAmount($order->tax_amount);
        $discountAmount = $this->toMidtransAmount($order->discount_amount);

        if ($shippingAmount > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => $shippingAmount,
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        if ($taxAmount > 0) {
            $itemDetails[] = [
                'id' => 'TAX',
                'price' => $taxAmount,
                'quantity' => 1,
                'name' => 'Pajak',
            ];
        }

        if ($discountAmount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -$discountAmount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        $expectedGrossAmount = $this->toMidtransAmount($order->grand_total);
        $currentGrossAmount = $this->sumItemDetails($itemDetails);
        $difference = $expectedGrossAmount - $currentGrossAmount;

        if ($difference !== 0) {
            $itemDetails[] = [
                'id' => 'ADJUSTMENT',
                'price' => $difference,
                'quantity' => 1,
                'name' => 'Penyesuaian Pembulatan',
            ];
        }

        return $itemDetails;
    }

    /**
     * @param  list<array{id: string, price: int, quantity: int, name: string}>  $itemDetails
     */
    private function sumItemDetails(array $itemDetails): int
    {
        return array_reduce(
            $itemDetails,
            fn (int $carry, array $item): int => $carry + ($item['price'] * $item['quantity']),
            0
        );
    }

    private function toMidtransAmount(float|int|string|null $amount): int
    {
        return (int) round((float) ($amount ?? 0));
    }

    private function formatItemName(string $name): string
    {
        $normalizedName = trim($name);

        if ($normalizedName === '') {
            return 'Item';
        }

        return mb_substr($normalizedName, 0, 50);
    }

    private function midtransServerKey(): string
    {
        return trim((string) config('services.midtrans.server_key', ''));
    }

    private function isProductionMode(): bool
    {
        $midtransEnv = strtolower(trim((string) config('services.midtrans.env', '')));

        if ($midtransEnv === 'production') {
            return true;
        }

        if ($midtransEnv === 'sandbox') {
            return false;
        }

        return (bool) config('services.midtrans.is_production', false);
    }

    private function snapTransactionUrl(): string
    {
        return $this->isProductionMode()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function transactionStatusApiBaseUrl(): string
    {
        return $this->isProductionMode()
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * @return list<array{id: string, price: int, quantity: int, name: string}>
     */
    private function buildItemDetailsFromOrder(Order $order): array
    {
        $itemDetails = $order->items
            ->map(function (OrderItem $item): array {
                return [
                    'id' => (string) ($item->product_id ?? $item->id),
                    'price' => $this->toMidtransAmount($item->unit_price),
                    'quantity' => max(1, (int) ($item->qty ?? 1)),
                    'name' => $this->formatItemName((string) ($item->name ?? 'Item')),
                ];
            })
            ->values()
            ->all();

        $shippingAmount = $this->toMidtransAmount($order->shipping_amount);
        $taxAmount = $this->toMidtransAmount($order->tax_amount);
        $discountAmount = $this->toMidtransAmount($order->discount_amount);

        if ($shippingAmount > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => $shippingAmount,
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        if ($taxAmount > 0) {
            $itemDetails[] = [
                'id' => 'TAX',
                'price' => $taxAmount,
                'quantity' => 1,
                'name' => 'Pajak',
            ];
        }

        if ($discountAmount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -$discountAmount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        $expectedGrossAmount = $this->toMidtransAmount($order->grand_total);
        $currentGrossAmount = $this->sumItemDetails($itemDetails);
        $difference = $expectedGrossAmount - $currentGrossAmount;

        if ($difference !== 0) {
            $itemDetails[] = [
                'id' => 'ADJUSTMENT',
                'price' => $difference,
                'quantity' => 1,
                'name' => 'Penyesuaian Pembulatan',
            ];
        }

        return $itemDetails;
    }
}
