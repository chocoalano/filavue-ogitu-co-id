<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CheckOrderPaymentStatusRequest;
use App\Http\Requests\Dashboard\CreateMidtransPayNowRequest;
use App\Http\Requests\Dashboard\DownloadOrderInvoiceRequest;
use App\Http\Requests\Dashboard\SubmitOrderItemReviewRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Dashboard\DashboardService;
use App\Services\Orders\OrderInvoicePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class DashboardOrderController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function checkPaymentStatus(
        CheckOrderPaymentStatusRequest $request,
        Order $order
    ): JsonResponse|RedirectResponse {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        try {
            $result = $this->dashboardService->checkOrderPaymentStatus($customer, (int) $order->id);

            if ($this->isInertiaRequest($request)) {
                return back()->with('orders', [
                    'action' => 'payment_status_checked',
                    'message' => $result['message'],
                    'payload' => [
                        'order' => $result['order'],
                    ],
                ]);
            }

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'order' => $result['order'],
                ],
            ]);
        } catch (ValidationException $exception) {
            return $this->validationFailure($request, $exception, 'Gagal memeriksa status pembayaran.');
        }
    }

    public function createMidtransPayNowToken(
        CreateMidtransPayNowRequest $request,
        Order $order
    ): JsonResponse|RedirectResponse {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        try {
            $result = $this->dashboardService->createMidtransPayNowToken($customer, (int) $order->id);

            if ($this->isInertiaRequest($request)) {
                return back()->with('orders', [
                    'action' => 'pay_now_token_created',
                    'message' => $result['message'] ?? 'Token pembayaran Midtrans berhasil dibuat.',
                    'payload' => $result,
                ]);
            }

            return response()->json($result);
        } catch (ValidationException $exception) {
            return $this->validationFailure($request, $exception, 'Gagal membuat token pembayaran Midtrans.');
        }
    }

    public function submitOrderItemReview(
        SubmitOrderItemReviewRequest $request,
        Order $order
    ): JsonResponse|RedirectResponse {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        try {
            $result = $this->dashboardService->submitOrderItemReview(
                $customer,
                (int) $order->id,
                $request->payload(),
            );

            if ($this->isInertiaRequest($request)) {
                return back()->with('orders', [
                    'action' => 'review_submitted',
                    'message' => $result['message'],
                    'payload' => [
                        'order' => $result['order'],
                        'review' => $result['review'],
                    ],
                ]);
            }

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'order' => $result['order'],
                    'review' => $result['review'],
                ],
            ]);
        } catch (ValidationException $exception) {
            return $this->validationFailure($request, $exception, 'Gagal mengirim review produk.');
        }
    }

    public function downloadInvoice(
        DownloadOrderInvoiceRequest $request,
        Order $order,
        OrderInvoicePdfService $invoicePdfService
    ): Response {
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

    private function isInertiaRequest(Request $request): bool
    {
        return $request->headers->has('X-Inertia');
    }

    private function validationFailure(
        Request $request,
        ValidationException $exception,
        string $fallbackMessage
    ): JsonResponse|RedirectResponse {
        $firstError = collect($exception->errors())->flatten()->first();
        $message = is_string($firstError) ? $firstError : $fallbackMessage;

        if ($this->isInertiaRequest($request)) {
            return back()
                ->withErrors($exception->errors())
                ->with('orders', [
                    'action' => 'error',
                    'message' => $message,
                ]);
        }

        return response()->json([
            'message' => $message,
            'errors' => $exception->errors(),
        ], 422);
    }
}
