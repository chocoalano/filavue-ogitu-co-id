<?php

use App\Http\Controllers\Web\DashboardOrderController;
use App\Http\Requests\Dashboard\DownloadOrderInvoiceRequest;
use App\Models\Order;
use App\Services\Dashboard\DashboardService;
use App\Services\Orders\OrderInvoicePdfService;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('registers customer dashboard invoice download route', function (): void {
    expect(route('dashboard.orders.invoice', ['order' => 99], false))
        ->toBe('/dashboard/orders/99/invoice');
});

it('returns downloadable pdf response for paid order invoice from dashboard', function (): void {
    $order = new Order([
        'id' => 12,
        'order_no' => 'ORD-2026-0012',
        'customer_id' => 301,
        'paid_at' => Carbon::parse('2026-02-27 11:00:00'),
    ]);

    $order->exists = true;
    $order->setRelation('customer', null);
    $order->setRelation('shippingAddress', null);
    $order->setRelation('billingAddress', null);
    $order->setRelation('items', collect());
    $order->setRelation('payments', collect());

    $service = Mockery::mock(OrderInvoicePdfService::class);
    $service->shouldReceive('generate')
        ->once()
        ->with($order)
        ->andReturn([
            'filename' => 'invoice-ORD-2026-0012.pdf',
            'content' => '%PDF-1.4 dashboard',
        ]);

    $request = DownloadOrderInvoiceRequest::create('/dashboard/orders/12/invoice', 'GET');
    $controller = new DashboardOrderController(Mockery::mock(DashboardService::class));
    $response = $controller->downloadInvoice($request, $order, $service);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('content-type'))->toBe('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('attachment;')
        ->and($response->headers->get('content-disposition'))->toContain('invoice-ORD-2026-0012.pdf');
});

it('returns inline pdf response when dashboard invoice preview is requested', function (): void {
    $order = new Order([
        'id' => 14,
        'order_no' => 'ORD-2026-0014',
        'customer_id' => 301,
        'paid_at' => Carbon::parse('2026-02-27 12:00:00'),
    ]);

    $order->exists = true;
    $order->setRelation('customer', null);
    $order->setRelation('shippingAddress', null);
    $order->setRelation('billingAddress', null);
    $order->setRelation('items', collect());
    $order->setRelation('payments', collect());

    $service = Mockery::mock(OrderInvoicePdfService::class);
    $service->shouldReceive('generate')
        ->once()
        ->with($order)
        ->andReturn([
            'filename' => 'invoice-ORD-2026-0014.pdf',
            'content' => '%PDF-1.4 preview',
        ]);

    $request = DownloadOrderInvoiceRequest::create('/dashboard/orders/14/invoice?preview=1', 'GET', ['preview' => 1]);
    $controller = new DashboardOrderController(Mockery::mock(DashboardService::class));
    $response = $controller->downloadInvoice($request, $order, $service);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('content-type'))->toBe('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('inline;')
        ->and($response->headers->get('content-disposition'))->toContain('invoice-ORD-2026-0014.pdf');
});

it('throws not found when trying to download unpaid dashboard invoice', function (): void {
    $order = new Order([
        'id' => 13,
        'order_no' => 'ORD-2026-0013',
        'customer_id' => 301,
        'paid_at' => null,
    ]);

    $request = DownloadOrderInvoiceRequest::create('/dashboard/orders/13/invoice', 'GET');
    $controller = new DashboardOrderController(Mockery::mock(DashboardService::class));
    $service = Mockery::mock(OrderInvoicePdfService::class);

    expect(fn () => $controller->downloadInvoice($request, $order, $service))
        ->toThrow(HttpException::class);
});
