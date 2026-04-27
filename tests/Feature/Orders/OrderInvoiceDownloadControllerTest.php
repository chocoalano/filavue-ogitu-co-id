<?php

use App\Http\Controllers\Web\OrderInvoiceDownloadController;
use App\Models\Order;
use App\Services\Orders\OrderInvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('returns downloadable pdf response for paid order invoice', function (): void {
    $order = new Order([
        'id' => 12,
        'order_no' => 'ORD-2026-0012',
        'paid_at' => Carbon::parse('2026-02-27 11:00:00'),
    ]);

    $order->exists = true;
    $order->setRelation('customer', null);
    $order->setRelation('shippingAddress', null);
    $order->setRelation('billingAddress', null);
    $order->setRelation('items', collect());
    $order->setRelation('payments', collect());

    Gate::shouldReceive('authorize')
        ->once()
        ->with('view', $order)
        ->andReturn(true);

    $service = Mockery::mock(OrderInvoicePdfService::class);
    $service->shouldReceive('generate')
        ->once()
        ->with($order)
        ->andReturn([
            'filename' => 'invoice-ORD-2026-0012.pdf',
            'content' => '%PDF-1.4 test',
        ]);

    $request = Request::create('/control-panel/orders/12/invoice', 'GET');
    $controller = new OrderInvoiceDownloadController;
    $response = $controller($request, $order, $service);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('content-type'))->toBe('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('attachment;')
        ->and($response->headers->get('content-disposition'))->toContain('invoice-ORD-2026-0012.pdf');
});

it('returns inline pdf response when preview is requested', function (): void {
    $order = new Order([
        'id' => 14,
        'order_no' => 'ORD-2026-0014',
        'paid_at' => Carbon::parse('2026-02-27 12:00:00'),
    ]);

    $order->exists = true;
    $order->setRelation('customer', null);
    $order->setRelation('shippingAddress', null);
    $order->setRelation('billingAddress', null);
    $order->setRelation('items', collect());
    $order->setRelation('payments', collect());

    Gate::shouldReceive('authorize')
        ->once()
        ->with('view', $order)
        ->andReturn(true);

    $service = Mockery::mock(OrderInvoicePdfService::class);
    $service->shouldReceive('generate')
        ->once()
        ->with($order)
        ->andReturn([
            'filename' => 'invoice-ORD-2026-0014.pdf',
            'content' => '%PDF-1.4 preview',
        ]);

    $request = Request::create('/control-panel/orders/14/invoice?preview=1', 'GET', ['preview' => 1]);
    $controller = new OrderInvoiceDownloadController;
    $response = $controller($request, $order, $service);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('content-type'))->toBe('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('inline;')
        ->and($response->headers->get('content-disposition'))->toContain('invoice-ORD-2026-0014.pdf');
});

it('throws not found when trying to download unpaid order invoice', function (): void {
    $order = new Order([
        'id' => 13,
        'order_no' => 'ORD-2026-0013',
        'paid_at' => null,
    ]);

    Gate::shouldReceive('authorize')
        ->once()
        ->with('view', $order)
        ->andReturn(true);

    $request = Request::create('/control-panel/orders/13/invoice', 'GET');
    $controller = new OrderInvoiceDownloadController;
    $service = Mockery::mock(OrderInvoicePdfService::class);

    expect(fn () => $controller($request, $order, $service))
        ->toThrow(HttpException::class);
});
