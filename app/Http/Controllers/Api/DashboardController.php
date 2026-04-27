<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesSanctumCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\LeaderboardRequest;
use App\Services\Dashboard\DashboardLeaderboardService;
use App\Services\Dashboard\DashboardService;
use App\Services\NetworkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class DashboardController extends Controller
{
    use ResolvesSanctumCustomer;

    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly DashboardLeaderboardService $dashboardLeaderboardService,
        private readonly NetworkService $networkService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/dashboard",
     *     tags={"Dashboard"},
     *     summary="Data dashboard customer",
     *     description="Mengembalikan seluruh data dashboard customer seperti profil, order, wallet, bonus, dan jaringan.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="orders_page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="orders_q", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="orders_status", in="query", required=false, @OA\Schema(type="string", enum={"all","unpaid","pending","paid","processing","shipped","delivered","cancelled","refunded"})),
     *     @OA\Parameter(name="orders_sort", in="query", required=false, @OA\Schema(type="string", enum={"newest","oldest","highest","lowest"})),
     *     @OA\Parameter(name="wallet_page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="wallet_search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="wallet_type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="wallet_status", in="query", required=false, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data dashboard berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data dashboard berhasil diambil.",
     *                 "data":{
     *                     "customer":{"id":1,"name":"Budi Santoso"},
     *                     "stats":{"orders_total":12,"wallet_balance":150000}
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"message":"Tidak terautentikasi."})
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json([
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $walletFilters = [
            'search' => $request->query('wallet_search'),
            'type' => $request->query('wallet_type'),
            'status' => $request->query('wallet_status'),
        ];
        $orderFilters = [
            'q' => $request->query('orders_q'),
            'status' => $request->query('orders_status'),
            'sort' => $request->query('orders_sort'),
            'date_from' => $request->query('orders_date_from'),
            'date_to' => $request->query('orders_date_to'),
        ];
        $generationFilters = [
            'level' => $request->query('generation_level'),
        ];

        $data = $this->dashboardService->getPageData(
            $customer,
            max(1, (int) $request->integer('orders_page', 1)),
            max(1, (int) $request->integer('wallet_page', 1)),
            $walletFilters,
            $orderFilters,
            null,
            max(1, (int) $request->integer('generation_page', 1)),
            $generationFilters,
        );

        return response()->json([
            'message' => 'Data dashboard berhasil diambil.',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/leaderboards",
     *     tags={"Dashboard"},
     *     summary="Leaderboard customer",
     *     description="Mengembalikan data leaderboard customer dengan filter periode harian, mingguan, dan bulanan.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="tab",
     *         in="query",
     *         required=false,
     *         description="Filter tab: 1=Harian, 2=Mingguan, 3=Bulanan",
     *
     *         @OA\Schema(type="integer", enum={1,2,3})
     *     ),
     *
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         required=false,
     *         description="Alternatif filter periode: daily/weekly/monthly atau harian/mingguan/bulanan",
     *
     *         @OA\Schema(type="string", enum={"daily","weekly","monthly","harian","mingguan","bulanan"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Leaderboard berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "success":true,
     *                 "message":"Leaderboard fetched successfully",
     *                 "data":{
     *                     "tabs":{"Harian","Mingguan","Bulanan"},
     *                     "selected_tab":1,
     *                     "my_rank":{
     *                         "rank":12,
     *                         "name":"Budi Santoso",
     *                         "avatar":"BS",
     *                         "level":"Gold Member",
     *                         "trend":"up",
     *                         "streak":7,
     *                         "points":18500
     *                     },
     *                     "leaderboard":{
     *                         {
     *                             "id":101,
     *                             "name":"Andi Wijaya",
     *                             "avatar":"AW",
     *                             "level":"Diamond",
     *                             "trend":"up",
     *                             "streak":21,
     *                             "points":125000
     *                         }
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"success": false, "message": "Tidak terautentikasi."})
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal",
     *
     *         @OA\JsonContent(example={"message":"The given data was invalid."})
     *     )
     * )
     */
    public function leaderboards(LeaderboardRequest $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $payload = $request->payload();
        $data = $this->dashboardLeaderboardService->getLeaderboardData(
            $customer,
            $payload['period_key'],
            $payload['selected_tab'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard fetched successfully',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/network",
     *     tags={"Dashboard"},
     *     summary="Network tree customer",
     *     description="Mengembalikan struktur jaringan lengkap (network tree) dari customer beserta statistik jaringan.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Network tree customer berhasil diambil",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Network tree retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="statistics",
     *                     type="object",
     *                     @OA\Property(property="total_downline", type="integer", example=1024),
     *                     @OA\Property(property="active_members", type="integer", example=856),
     *                     @OA\Property(property="inactive_members", type="integer", example=168),
     *                     @OA\Property(property="total_levels", type="integer", example=5),
     *                     @OA\Property(property="total_points", type="integer", example=12500)
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="user_001"),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+62812345678"),
     *                     @OA\Property(property="avatar_url", type="string", nullable=true, example=null),
     *                     @OA\Property(property="level", type="integer", example=0),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="points", type="integer", example=1200),
     *                     @OA\Property(property="joined_at", type="string", format="date-time", example="2024-01-15T08:30:00Z"),
     *                     @OA\Property(property="sponsor_id", type="string", nullable=true, example=null),
     *                     @OA\Property(property="sponsor_name", type="string", nullable=true, example=null)
     *                 ),
     *                 @OA\Property(
     *                     property="tree",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="user_001"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="level", type="integer", example=0),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="points", type="integer", example=1200),
     *                     @OA\Property(property="total_downline", type="integer", example=15),
     *                     @OA\Property(property="direct_referrals", type="integer", example=4),
     *                     @OA\Property(
     *                         property="children",
     *                         type="array",
     *
     *                         @OA\Items(type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"success": false, "message": "Tidak terautentikasi."})
     *     )
     * )
     */
    public function getNetworkUser(Request $request): JsonResponse
    {
        $customer = $this->resolveSanctumCustomer($request);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $networkData = $this->networkService->getNetworkTree($customer);

        return response()->json([
            'success' => true,
            'message' => 'Network tree retrieved successfully',
            'data' => $networkData,
        ]);
    }
}
