<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CreateWalletTopupTokenRequest;
use App\Http\Requests\Dashboard\ListWalletTransactionRequest;
use App\Http\Requests\Dashboard\StoreWalletWithdrawalRequest;
use App\Http\Requests\Dashboard\SyncWalletTopupStatusRequest;
use App\Models\CustomerWalletTransaction;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class DashboardWalletController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/dashboard/wallet/transactions",
     *     tags={"Dashboard Wallet"},
     *     summary="Daftar transaksi wallet customer (filter + pagination)",
     *     description="Mengembalikan daftar transaksi wallet milik customer yang sedang login (current user) dengan filter pencarian, tipe, status, arah mutasi, metode pembayaran, rentang tanggal, rentang nominal, urutan, dan pagination.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1, example=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100, example=15)),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", nullable=true, example="TOPUP-2201")),
     *     @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string", nullable=true, example="bank_transfer")),
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string", nullable=true, enum={"all","topup","withdrawal","bonus","purchase","refund","tax"}, example="all")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", nullable=true, enum={"all","pending","completed","failed","cancelled"}, example="all")),
     *     @OA\Parameter(name="direction", in="query", required=false, @OA\Schema(type="string", nullable=true, enum={"all","credit","debit"}, example="all")),
     *     @OA\Parameter(name="payment_method", in="query", required=false, @OA\Schema(type="string", nullable=true, example="midtrans")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date", nullable=true, example="2026-03-01")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date", nullable=true, example="2026-03-31")),
     *     @OA\Parameter(name="amount_min", in="query", required=false, @OA\Schema(type="number", format="float", minimum=0, nullable=true, example=10000)),
     *     @OA\Parameter(name="amount_max", in="query", required=false, @OA\Schema(type="number", format="float", minimum=0, nullable=true, example=500000)),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string", nullable=true, enum={"newest","oldest","highest","lowest"}, example="newest")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Daftar transaksi wallet berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data transaksi wallet berhasil diambil.",
     *                 "data":{
     *                     "summary":{
     *                         "balance_available":2993250,
     *                         "topup_30d":640000,
     *                         "withdrawal_30d":150000,
     *                         "netflow_30d":490000,
     *                         "pending_count":12
     *                     },
     *                     "window":{
     *                         "days":30,
     *                         "from":"2026-02-02T00:00:00+07:00",
     *                         "to":"2026-03-03T23:59:59+07:00",
     *                         "timezone":"Asia/Jakarta"
     *                     },
     *                     "data":{
     *                         {
     *                             "id":1207,
     *                             "type":"topup",
     *                             "type_label":"Top Up Saldo",
     *                             "direction":"credit",
     *                             "status":"pending",
     *                             "status_label":"Menunggu",
     *                             "amount":50000,
     *                             "balance_before":2993250,
     *                             "balance_after":2993250,
     *                             "payment_method":"midtrans",
     *                             "transaction_ref":"TOPUP-24-20260303180112-F24707",
     *                             "created_at":"2026-03-03T18:01:12+07:00",
     *                             "completed_at":null,
     *                             "description":"Top Up Saldo • Ref: TOPUP-24-20260303180112-F24707 • MIDTRANS"
     *                         }
     *                     },
     *                     "current_page":1,
     *                     "next_page":2,
     *                     "has_more":true,
     *                     "per_page":15,
     *                     "total":24
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function index(ListWalletTransactionRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        $payload = $request->payload();

        try {
            $transactions = $this->dashboardService->getWalletTransactionsPagination(
                $customer,
                $payload['page'],
                $payload['per_page'],
                $payload['filters'],
            );

            return response()->json([
                'message' => 'Data transaksi wallet berhasil diambil.',
                'data' => $transactions,
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal memuat data transaksi wallet.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/wallet/topup/token",
     *     tags={"Dashboard Wallet"},
     *     summary="Buat token topup wallet",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"amount"},
     *
     *             @OA\Property(property="amount", type="number", format="float", minimum=10000, example=100000),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Topup saldo via mobile app")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token topup berhasil dibuat",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Token topup Midtrans berhasil dibuat.",
     *                 "snapToken":"midtrans-wallet-snap-token",
     *                 "transaction":{"id":991,"type":"topup","status":"pending"}
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function createTopupToken(CreateWalletTopupTokenRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            return response()->json(
                $this->dashboardService->createWalletTopupToken($customer, $request->payload())
            );
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal membuat token topup Midtrans.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/wallet/topup/{walletTransaction}/payment-status",
     *     tags={"Dashboard Wallet"},
     *     summary="Sinkronisasi status topup wallet",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="walletTransaction", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status topup berhasil disinkronkan",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Status topup berhasil disinkronkan.",
     *                 "data":{
     *                     "transaction":{"id":991,"status":"completed"},
     *                     "balance":750000
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=403, description="Tidak diizinkan", @OA\JsonContent(example={"message":"Tindakan tidak diizinkan."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function syncTopupStatus(
        SyncWalletTopupStatusRequest $request,
        CustomerWalletTransaction $walletTransaction
    ): JsonResponse {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $result = $this->dashboardService->syncWalletTopupStatus($customer, (int) $walletTransaction->id);

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'transaction' => $result['transaction'],
                    'balance' => $result['balance'],
                ],
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal sinkronisasi status topup Midtrans.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/wallet/withdrawal",
     *     tags={"Dashboard Wallet"},
     *     summary="Ajukan withdrawal wallet",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"amount","password"},
     *
     *             @OA\Property(property="amount", type="number", format="float", minimum=10000, example=50000),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Penarikan mingguan")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permintaan withdrawal berhasil dibuat",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Permintaan withdrawal berhasil dibuat.",
     *                 "data":{
     *                     "transaction":{"id":1202,"status":"pending"},
     *                     "balance":500000
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function storeWithdrawal(StoreWalletWithdrawalRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        try {
            $result = $this->dashboardService->submitWalletWithdrawal($customer, $request->payload());

            return response()->json([
                'message' => $result['message'],
                'data' => [
                    'transaction' => $result['transaction'],
                    'balance' => $result['balance'],
                ],
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal mengirim permintaan withdrawal.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}
