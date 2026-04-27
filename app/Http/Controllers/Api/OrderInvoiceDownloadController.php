<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\OrderInvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use OpenApi\Annotations as OA;

class OrderInvoiceDownloadController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/control-panel/orders/{order}/invoice",
     *     tags={"Orders"},
     *     summary="Download invoice order (control panel)",
     *     description="Menghasilkan file PDF invoice untuk order pada control panel. Hanya untuk order yang sudah dibayar dan user yang memiliki akses.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="preview", in="query", required=false, @OA\Schema(type="boolean")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Invoice PDF berhasil dibuat",
     *
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *
     *             @OA\Schema(type="string", format="binary", example="%PDF-1.7 ...binary invoice content...")
     *         )
     *     ),
     *
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=404, description="Invoice tidak tersedia", @OA\JsonContent(example={"message":"Invoice hanya tersedia untuk pesanan yang sudah dibayar."}))
     * )
     */
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
