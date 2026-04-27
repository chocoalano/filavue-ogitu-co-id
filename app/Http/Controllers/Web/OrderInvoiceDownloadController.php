<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\OrderInvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class OrderInvoiceDownloadController extends Controller
{
    public function __invoke(Request $request, Order $order, OrderInvoicePdfService $invoicePdfService): Response
    {
        Gate::authorize('view', $order);

        abort_if(blank($order->paid_at), 404, 'Invoice hanya tersedia untuk pesanan yang sudah dibayar.');

        $order->loadMissing([
            'customer:id,name,email',
            'shippingAddress',
            'billingAddress',
            'items:id,order_id,name,sku,qty,unit_price,row_total',
            'payments:id,order_id,status,provider_txn_id,transaction_id',
        ]);

        $invoice = $invoicePdfService->generate($order);
        $dispositionType = $request->boolean('preview') ? 'inline' : 'attachment';

        return response($invoice['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $dispositionType.'; filename="'.$invoice['filename'].'"',
            'Content-Length' => (string) strlen($invoice['content']),
        ]);
    }
}
