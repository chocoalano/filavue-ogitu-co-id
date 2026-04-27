<?php

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\Orders\OrderInvoicePdfService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

it('generates ecommerce invoice pdf content from order data', function (): void {
    Http::fake([
        'https://cdn.jsdelivr.net/*' => Http::response('.btn{display:inline-block;}', 200),
    ]);

    $order = new Order([
        'order_no' => 'ORD-2026-0001',
        'currency' => 'IDR',
        'status' => 'PAID',
        'subtotal_amount' => 250000,
        'discount_amount' => 10000,
        'shipping_amount' => 15000,
        'tax_amount' => 25000,
        'grand_total' => 280000,
        'created_at' => Carbon::parse('2026-02-27 10:00:00'),
        'paid_at' => Carbon::parse('2026-02-27 10:30:00'),
    ]);

    $customer = new Customer([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.test',
    ]);

    $shippingAddress = new CustomerAddress([
        'recipient_name' => 'Budi Santoso',
        'recipient_phone' => '08123456789',
        'address_line1' => 'Jl. Melati No. 10',
        'city_label' => 'Jakarta Barat',
        'province_label' => 'DKI Jakarta',
        'postal_code' => '11510',
        'country' => 'Indonesia',
    ]);

    $item = new OrderItem([
        'name' => 'Produk Herbal Premium',
        'sku' => 'SKU-001',
        'qty' => 2,
        'unit_price' => 125000,
        'row_total' => 250000,
    ]);

    $payment = new Payment([
        'status' => 'settlement',
        'provider_txn_id' => 'MID-TRX-001',
    ]);

    $order->setRelation('customer', $customer);
    $order->setRelation('shippingAddress', $shippingAddress);
    $order->setRelation('billingAddress', $shippingAddress);
    $order->setRelation('items', collect([$item]));
    $order->setRelation('payments', collect([$payment]));

    $service = new OrderInvoicePdfService;
    $result = $service->generate($order);

    expect($result['filename'])
        ->toBe('invoice-ORD-2026-0001.pdf')
        ->and($result['content'])
        ->toStartWith('%PDF-')
        ->and($result['content'])
        ->toContain('/Type /Page');

    expect(strlen($result['content']))->toBeGreaterThan(1000);

    Http::assertNothingSent();
});

it('uses bootstrap fallback stylesheet when bootstrap cdn is unavailable', function (): void {
    Http::fake([
        'https://cdn.jsdelivr.net/*' => Http::response('', 503),
    ]);

    $service = new OrderInvoicePdfService;
    $method = new ReflectionMethod(OrderInvoicePdfService::class, 'resolveStylesheetContent');
    $method->setAccessible(true);

    $css = $method->invoke($service, 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css');

    expect($css)
        ->toContain('.btn')
        ->and($css)
        ->toContain('.btn-default')
        ->and($css)
        ->toContain('.btn-group-justified');

    Http::assertNothingSent();
});

it('uses local bootstrap fallback stylesheet even when bootstrap cdn responds', function (): void {
    Http::fake([
        'https://cdn.jsdelivr.net/*' => Http::response('.btn{color:#111;}', 200),
    ]);

    $service = new OrderInvoicePdfService;
    $method = new ReflectionMethod(OrderInvoicePdfService::class, 'resolveStylesheetContent');
    $method->setAccessible(true);

    $css = $method->invoke($service, 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css');

    expect($css)
        ->not->toContain('.btn{color:#111;}')
        ->and($css)
        ->toContain('.btn-default')
        ->and($css)
        ->toContain('.btn-group-justified');

    Http::assertNothingSent();
});
