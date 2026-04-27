<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CheckOrderPaymentStatusRequest;
use App\Http\Requests\Dashboard\CreateMidtransPayNowRequest;
use App\Http\Requests\Dashboard\DownloadOrderInvoiceRequest;
use App\Http\Requests\Dashboard\ListOrderRequest;
use App\Http\Requests\Dashboard\ShowOrderRequest;
use App\Http\Requests\Dashboard\SubmitOrderItemReviewRequest;
use App\Models\Order;
use App\Services\Dashboard\DashboardService;
use App\Services\Orders\OrderInvoicePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class DashboardOrderController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/dashboard/orders",
     *     tags={"Dashboard Orders"},
     *     summary="Daftar order customer (filter + pagination)",
     *     description="Mengembalikan daftar order milik customer yang sedang login (current user) dengan filter status, pencarian, rentang tanggal, urutan, dan pagination.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1, example=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100, example=10)),
     *     @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string", nullable=true, example="ORD-202603")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", nullable=true, enum={"all","unpaid","pending","paid","processing","shipped","delivered","cancelled","refunded"}, example="all")),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string", nullable=true, enum={"newest","oldest","highest","lowest"}, example="newest")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date", nullable=true, example="2026-03-01")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date", nullable=true, example="2026-03-31")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar order berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data order berhasil diambil.",
     *                 "data":{
     *                     "data":{
     *                         {
     *                             "id":1201,
     *                             "code":"ORD-20260301-ABC123",
     *                             "status":"pending",
     *                             "payment_status":"unpaid",
     *                             "total":550000
     *                         }
     *                     },
     *                     "current_page":1,
     *                     "next_page":2,
     *                     "has_more":true,
     *                     "per_page":10,
     *                     "total":32
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function index(ListOrderRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $payload = $request->payload();

        try {
            $orders = $this->dashboardService->getOrdersPagination(
                $customer,
                $payload['page'],
                $payload['per_page'],
                $payload['filters'],
            );

            return response()->json([
                'message' => 'Data order berhasil diambil.',
                'data' => $orders,
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal memuat data order.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/orders/{order}",
     *     tags={"Dashboard Orders"},
     *     summary="Detail order customer",
     *     description="Mengembalikan detail lengkap order milik customer yang sedang login (current user).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Detail order berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Detail order berhasil diambil.",
     *                 "data":{
     *                     "id":1201,
     *                     "code":"ORD-20260301-ABC123",
     *                     "status":"pending",
     *                     "payment_status":"unpaid",
     *                     "subtotal":500000,
     *                     "shipping_cost":25000,
     *                     "tax_amount":25000,
     *                     "discount_amount":0,
     *                     "total":550000,
     *                     "items_count":2
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal / order tidak ditemukan", @OA\JsonContent(example={"message":"Order tidak ditemukan.","errors":{"order":{"Order tidak ditemukan."}}}))
     * )
     */
    public function show(ShowOrderRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $payload = $request->payload();

        try {
            $order = $this->dashboardService->getOrderDetail($customer, $payload['order_id']);

            return response()->json([
                'message' => 'Detail order berhasil diambil.',
                'data' => $order,
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal memuat detail order.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    public function submitReview(
        SubmitOrderItemReviewRequest $request,
        Order $order
    ): JsonResponse {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $result = $this->dashboardService->submitOrderItemReview(
                $customer,
                (int) $order->id,
                $request->payload(),
            );

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'order' => $result['order'],
                    'review' => $result['review'],
                ],
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal mengirim review produk.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/orders/{order}/payment-status",
     *     tags={"Dashboard Orders"},
     *     summary="Sinkronisasi status pembayaran order",
     *     description="Sinkronisasi status pembayaran untuk order milik customer yang sedang login (current user).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status pembayaran berhasil diperiksa",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Status pembayaran sudah diperbarui.",
     *                 "data":{"order":{"id":1201,"order_no":"ORD-20260301-ABC123","status":"paid"}}
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function checkPaymentStatus(
        CheckOrderPaymentStatusRequest $request,
        Order $order
    ): JsonResponse {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $result = $this->dashboardService->checkOrderPaymentStatus($customer, (int) $order->id);

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'order' => $result['order'],
                ],
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal memeriksa status pembayaran.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/orders/{order}/pay-now",
     *     tags={"Dashboard Orders"},
     *     summary="Generate token Midtrans pay now untuk order",
     *     description="Membuat token pembayaran Midtrans untuk order milik customer yang sedang login (current user).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token pay now berhasil dibuat",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Token pembayaran Midtrans berhasil dibuat.",
     *                 "snapToken":"midtrans-snap-token",
     *                 "orderId":1201,
     *                 "orderNo":"ORD-20260301-ABC123"
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function createMidtransPayNowToken(
        CreateMidtransPayNowRequest $request,
        Order $order
    ): JsonResponse {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            return response()->json(
                $this->dashboardService->createMidtransPayNowToken($customer, (int) $order->id)
            );
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal membuat token pembayaran Midtrans.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/orders/{order}/invoice",
     *     tags={"Dashboard Orders"},
     *     summary="Download invoice order customer",
     *     description="Menghasilkan file PDF invoice untuk order milik customer yang sedang login (current user) dan sudah dibayar.",
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
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=404, description="Invoice tidak tersedia", @OA\JsonContent(example={"message":"Invoice hanya tersedia untuk pesanan yang sudah dibayar."}))
     * )
     */
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
}
