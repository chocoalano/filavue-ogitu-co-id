<?php

namespace App\Services\Dashboard;

use App\Http\Requests\Dashboard\UpdateAccountProfileRequest;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerBonusCashback;
use App\Models\CustomerBonusReward;
use App\Models\CustomerBonusRoyalty;
use App\Models\CustomerBonusSponsor;
use App\Models\CustomerNetworkMatrix;
use App\Models\CustomerNpwp;
use App\Models\CustomerWalletTransaction;
use App\Models\CustomerWhatsAppConfirmation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Models\Promotion;
use App\Models\Reward;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Payment\MidtransService;
use App\Services\QontactService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class DashboardService
{
    private const WITHDRAWAL_MIN_AMOUNT = 10000;

    private const WITHDRAWAL_ADMIN_FEE = 6500;

    private const WITHDRAWAL_STEP_AMOUNT = 500;

    public function __construct(
        protected DashboardRepositoryInterface $dashboardRepository,
        protected CustomerAddressRepositoryInterface $customerAddressRepository,
        protected MidtransService $midtransService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getPageData(
        Customer $authenticatedCustomer,
        int $ordersPage = 1,
        int $walletPage = 1,
        array $walletFilters = [],
        array $orderFilters = [],
        ?int $networkRootId = null,
        int $generationPage = 1,
        array $generationFilters = [],
    ): array {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            return $this->emptyPageData();
        }

        $now = now();
        $normalizedWalletFilters = $this->normalizeWalletFilters($walletFilters);
        $normalizedOrderFilters = $this->normalizeOrdersFilters($orderFilters);
        $normalizedGenerationFilters = $this->normalizeGenerationFilters($generationFilters);
        $coreMetrics = $this->loadCoreMetrics($customer, $now);
        $addressData = $this->loadAddressData($customer);
        $mitraMembers = $this->loadMitraMembers($customer);
        $networkData = $this->loadNetworkData($customer, $networkRootId);
        $generationNetwork = $this->loadGenerationNetworkData($customer, max(1, $generationPage), $normalizedGenerationFilters);
        $orders = $this->loadOrdersData($customer, max(1, $ordersPage), $normalizedOrderFilters);
        $walletData = $this->loadWalletData($customer, max(1, $walletPage), $normalizedWalletFilters);
        $bonusData = $this->loadBonusData($customer);
        $contentData = $this->loadContentData($now);

        return $this->buildPageDataPayload(
            $customer,
            $addressData,
            $mitraMembers,
            $networkData,
            $generationNetwork,
            $orders,
            $walletData,
            $bonusData,
            $contentData,
            $coreMetrics
        );
    }

    /**
     * @param  array{default_address:?CustomerAddress,addresses:Collection<int, CustomerAddress>}  $addressData
     * @param  array{active:array<int, array<string, mixed>>,passive:array<int, array<string, mixed>>,prospect:array<int, array<string, mixed>>}  $mitraMembers
     * @param  array{
     *   has_left:bool,
     *   has_right:bool,
     *   tree:?array<string, mixed>,
     *   stats:array<string, int>,
     *   tree_root_id:int
     * }  $networkData
     * @param  array{
     *   data:list<array<string, mixed>>,
     *   current_page:int,
     *   per_page:int,
     *   total:int,
     *   last_page:int,
     *   from:int|null,
     *   to:int|null,
     *   filters:array{level:int|null},
     *   available_generations:list<array{value:int,label:string}>
     * }  $generationNetwork
     * @param  array{
     *   data:array<int, array<string, mixed>>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int,
     *   filters:array{
     *     q:string|null,
     *     status:string,
     *     sort:string,
     *     date_from:string|null,
     *     date_to:string|null
     *   },
     *   pending_review_count:int,
     *   has_pending_review:bool
     * }  $orders
     * @param  array{transactions:array<string, mixed>,has_pending_withdrawal:bool}  $walletData
     * @param  array{bonus_stats:array<string, mixed>,bonus_tables:array<string, array<int, array<string, mixed>>>,lifetime_rewards:array<string, mixed>}  $bonusData
     * @param  array{promos:array<int, array<string, mixed>>,zenner_categories:array<int, array<string, mixed>>,zenner_contents:array<int, array<string, mixed>>}  $contentData
     * @param array{
     *   orders_total:int,
     *   orders_pending:int,
     *   active_network_members:int,
     *   network_level:int,
     *   bonus_available:float,
     *   bonus_month:float,
     *   bonus_lifetime:float,
     *   promo_active:int,
     *   last_order_at:?CarbonInterface,
     *   has_npwp:bool,
     *   left_count:int,
     *   right_count:int,
     *   total_downline:int
     * } $coreMetrics
     * @return array<string, mixed>
     */
    private function buildPageDataPayload(
        Customer $customer,
        array $addressData,
        array $mitraMembers,
        array $networkData,
        array $generationNetwork,
        array $orders,
        array $walletData,
        array $bonusData,
        array $contentData,
        array $coreMetrics,
    ): array {
        return [
            'customer' => $this->formatCustomer($customer),
            'waConfirmationUrl' => $this->buildWaConfirmationUrl($customer),
            'waGatewayHematMode' => (bool) config('services.qontak.wa_gateway_hemat_mode', false),
            'currentCustomerId' => $customer->id,
            'defaultAddress' => $this->formatAddress($addressData['default_address']),
            'addresses' => $this->formatAddresses($addressData['addresses']),
            'activeMembers' => Arr::get($mitraMembers, 'active', []),
            'passiveMembers' => Arr::get($mitraMembers, 'passive', []),
            'prospectMembers' => Arr::get($mitraMembers, 'prospect', []),
            'hasLeft' => $networkData['has_left'],
            'hasRight' => $networkData['has_right'],
            'binaryTree' => $networkData['tree'],
            'networkTreeStats' => $networkData['stats'],
            'networkTreeRootId' => $networkData['tree_root_id'],
            'generationNetwork' => $generationNetwork,
            'orders' => $orders,
            'walletTransactions' => $walletData['transactions'],
            'hasPendingWithdrawal' => $walletData['has_pending_withdrawal'],
            'bonusStats' => $bonusData['bonus_stats'],
            'bonusTables' => $bonusData['bonus_tables'],
            'lifetimeRewards' => $bonusData['lifetime_rewards'],
            'promos' => $contentData['promos'],
            'zennerCategories' => $contentData['zenner_categories'],
            'zennerContents' => $contentData['zenner_contents'],
            'midtrans' => [
                'env' => config('services.midtrans.env', 'sandbox'),
                'client_key' => config('services.midtrans.client_key', ''),
            ],
            'stats' => [
                'orders_total' => $coreMetrics['orders_total'],
                'orders_pending' => $coreMetrics['orders_pending'],
                'network_total' => $coreMetrics['total_downline'],
                'network_active' => $coreMetrics['active_network_members'],
                'network_level' => $coreMetrics['network_level'],
                'bonus_total' => (float) data_get(
                    collect($bonusData['bonus_stats'] ?? [])->firstWhere('key', 'total_bonus'),
                    'amount',
                    0
                ),
                'bonus_month' => $coreMetrics['bonus_month'],
                'bonus_lifetime' => $coreMetrics['bonus_lifetime'],
                'bonus_available' => $coreMetrics['bonus_available'],
                'wallet_balance' => (float) ($customer->ewallet_saldo ?? 0),
                'promo_active' => $coreMetrics['promo_active'],
            ],
            'networkProfile' => [
                'username' => $customer->username ?? '—',
                'level' => $customer->level ?? $this->memberStatusLabel((int) ($customer->status ?? 1)),
                'referral_code' => $customer->ref_code ?? '—',
                'balance' => (float) ($customer->ewallet_saldo ?? 0),
            ],
            'networkStats' => [
                'left_count' => $coreMetrics['left_count'],
                'right_count' => $coreMetrics['right_count'],
                'total_downline' => $coreMetrics['total_downline'],
                'omset_nb_left' => 0.0,
                'omset_nb_right' => 0.0,
                'omset_retail_left' => 0.0,
                'omset_retail_right' => 0.0,
                'omset_group' => (float) ($customer->omzet_group ?? 0),
            ],
            'securitySummary' => [
                'account_status_label' => $this->memberStatusLabel((int) ($customer->status ?? 1)),
                'email_verified' => $customer->email_verified_at !== null,
                'has_bank_account' => filled($customer->bank_name) && filled($customer->bank_account),
                'has_npwp' => $coreMetrics['has_npwp'],
                'last_order_at' => $coreMetrics['last_order_at']?->toIso8601String(),
            ],
        ];
    }

    /**
     * @return array{
     *   orders_total:int,
     *   orders_pending:int,
     *   active_network_members:int,
     *   network_level:int,
     *   bonus_available:float,
     *   bonus_month:float,
     *   bonus_lifetime:float,
     *   promo_active:int,
     *   last_order_at:?CarbonInterface,
     *   has_npwp:bool,
     *   left_count:int,
     *   right_count:int,
     *   total_downline:int
     * }
     */
    private function loadCoreMetrics(Customer $customer, CarbonInterface $now): array
    {
        $leftCount = 0;
        $rightCount = 0;
        $totalDownline = $this->dashboardRepository->countActiveNetworkMembers($customer->id);

        return [
            'orders_total' => $this->dashboardRepository->countOrders($customer->id),
            'orders_pending' => $this->dashboardRepository->countPendingOrders($customer->id, $this->pendingOrderStatuses()),
            'active_network_members' => $totalDownline,
            'network_level' => $this->dashboardRepository->getMaxNetworkLevel($customer->id),
            'bonus_available' => (float) $this->dashboardRepository->sumAvailableBonuses($customer->id),
            'bonus_month' => (float) $this->dashboardRepository->sumMonthlyBonuses($customer->id, (int) $now->year, (int) $now->month),
            'bonus_lifetime' => (float) $this->dashboardRepository->sumLifetimeBonuses($customer->id),
            'promo_active' => $this->dashboardRepository->countActivePromotions($now),
            'last_order_at' => $this->dashboardRepository->getLatestOrderTimestamp($customer->id),
            'has_npwp' => $this->dashboardRepository->hasNpwp($customer->id),
            'left_count' => $leftCount,
            'right_count' => $rightCount,
            'total_downline' => $totalDownline,
        ];
    }

    /**
     * @return array{
     *   default_address:?CustomerAddress,
     *   addresses:Collection<int, CustomerAddress>
     * }
     */
    private function loadAddressData(Customer $customer): array
    {
        return [
            'default_address' => $this->dashboardRepository->getDefaultAddress($customer->id),
            'addresses' => $this->customerAddressRepository->getByCustomerId($customer->id),
        ];
    }

    /**
     * @return array{active:array<int, array<string, mixed>>,passive:array<int, array<string, mixed>>,prospect:array<int, array<string, mixed>>}
     */
    private function loadMitraMembers(Customer $customer): array
    {
        return $this->formatMitraMembers($this->dashboardRepository->getSponsoredMembers($customer->id));
    }

    /**
     * @return array{
     *   has_left:bool,
     *   has_right:bool,
     *   tree:?array<string, mixed>,
     *   stats:array<string, int>,
     *   tree_root_id:int
     * }
     */
    private function loadNetworkData(Customer $customer, ?int $networkRootId = null): array
    {
        $treeRoot = $this->resolveNetworkTreeRoot($customer, $networkRootId);
        $treeData = $this->formatBinaryTreeData($treeRoot);

        return [
            'has_left' => $this->dashboardRepository->hasBinaryChildAtPosition($customer->id, 'left'),
            'has_right' => $this->dashboardRepository->hasBinaryChildAtPosition($customer->id, 'right'),
            'tree' => $treeData['tree'],
            'stats' => $treeData['stats'],
            'tree_root_id' => (int) $treeRoot->id,
        ];
    }

    /**
     * @param  array{level:int|null}  $filters
     * @return array{
     *   data:list<array<string, mixed>>,
     *   current_page:int,
     *   per_page:int,
     *   total:int,
     *   last_page:int,
     *   from:int|null,
     *   to:int|null,
     *   filters:array{level:int|null},
     *   available_generations:list<array{value:int,label:string}>
     * }
     */
    private function loadGenerationNetworkData(Customer $customer, int $page, array $filters = []): array
    {
        $pagination = $this->dashboardRepository->getPaginatedGenerationNetworkMembers($customer->id, 15, $page, $filters);
        $levels = $this->dashboardRepository->getGenerationLevelOptions($customer->id);

        return $this->formatGenerationNetworkPagination($pagination, $levels, $filters);
    }

    private function resolveNetworkTreeRoot(Customer $customer, ?int $networkRootId = null): Customer
    {
        if ($networkRootId === null || $networkRootId <= 0 || $networkRootId === (int) $customer->id) {
            return $customer;
        }

        if (! $this->dashboardRepository->isMemberInCustomerNetwork($customer->id, $networkRootId)) {
            return $customer;
        }

        $member = $this->dashboardRepository->findCustomerById($networkRootId);

        if (! $member instanceof Customer) {
            return $customer;
        }

        return $member;
    }

    /**
     * @return array{
     *   data:array<int, array<string, mixed>>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int,
     *   filters:array{
     *     q:string|null,
     *     status:string,
     *     sort:string,
     *     date_from:string|null,
     *     date_to:string|null
     *   },
     *   pending_review_count:int,
     *   has_pending_review:bool
     * }
     */
    private function loadOrdersData(Customer $customer, int $page, array $filters = []): array
    {
        $ordersPagination = $this->dashboardRepository->getPaginatedOrders($customer->id, 10, $page, $filters);
        $pendingReviewCount = $this->dashboardRepository->countPendingReviewItems($customer->id);

        return $this->formatOrders($ordersPagination, $pendingReviewCount, $filters);
    }

    /**
     * @param  array{
     *   search:string|null,
     *   type:string|null,
     *   status:string|null,
     *   direction:string|null,
     *   payment_method:string|null,
     *   date_from:string|null,
     *   date_to:string|null,
     *   amount_min:float|null,
     *   amount_max:float|null,
     *   sort:string
     * }  $normalizedWalletFilters
     * @return array{
     *   transactions:array<string, mixed>,
     *   has_pending_withdrawal:bool
     * }
     */
    private function loadWalletData(Customer $customer, int $page, array $normalizedWalletFilters): array
    {
        $walletPagination = $this->dashboardRepository->getPaginatedWalletTransactions(
            $customer->id,
            15,
            $page,
            $normalizedWalletFilters
        );

        return [
            'transactions' => $this->formatWalletTransactionsPagination($walletPagination, $normalizedWalletFilters),
            'has_pending_withdrawal' => $this->dashboardRepository->hasPendingWithdrawal($customer->id),
        ];
    }

    /**
     * @return array{
     *   bonus_stats:array<string, mixed>,
     *   bonus_tables:array<string, array<int, array<string, mixed>>>,
     *   lifetime_rewards:array<string, mixed>
     * }
     */
    private function loadBonusData(Customer $customer): array
    {
        return [
            'bonus_stats' => $this->formatBonusStats($this->dashboardRepository->getBonusStats($customer->id)),
            'bonus_tables' => [
                'referral_incentive' => $this->formatBonusSponsors(
                    $this->dashboardRepository->getBonusSponsors($customer->id)
                ),
                'team_affiliate_commission' => $this->formatBonusMatchings(
                    $this->dashboardRepository->getBonusMatchings($customer->id)
                ),
                'partner_team_commission' => $this->formatBonusPairings(
                    $this->dashboardRepository->getBonusPairings($customer->id)
                ),
                'cashback_commission' => $this->formatBonusCashbacks(
                    $this->dashboardRepository->getBonusCashbacks($customer->id)
                ),
                'promotions_rewards' => $this->formatPromotionRewards(
                    $this->dashboardRepository->getPromotionRewards($customer->id)
                ),
                'retail_commission' => $this->formatBonusRetails(
                    $this->dashboardRepository->getBonusRetails($customer->id)
                ),
                'lifetime_cash_rewards' => $this->formatBonusLifetimeCashRewards(
                    $this->dashboardRepository->getBonusLifetimeCashRewards($customer->id)
                ),
            ],
            'lifetime_rewards' => $this->formatLifetimeRewardsData(
                $customer,
                $this->dashboardRepository->getActiveLifetimeRewards(),
                $this->dashboardRepository->getClaimedLifetimeRewardNames($customer->id),
                $this->dashboardRepository->getClaimedLifetimeRewards($customer->id)
            ),
        ];
    }

    /**
     * @return array{
     *   promos:array<int, array<string, mixed>>,
     *   zenner_categories:array<int, array<string, mixed>>,
     *   zenner_contents:array<int, array<string, mixed>>
     * }
     */
    private function loadContentData(CarbonInterface $asOf): array
    {
        return [
            'promos' => $this->formatPromotions($this->dashboardRepository->getDashboardPromotions($asOf), $asOf),
            'zenner_categories' => $this->formatZennerCategories($this->dashboardRepository->getZennerCategories()),
            'zenner_contents' => $this->formatZennerContents($this->dashboardRepository->getZennerContents()),
        ];
    }

    /**
     * @return list<string>
     */
    private function pendingOrderStatuses(): array
    {
        return ['pending', 'PENDING', 'unpaid', 'waiting_payment', 'awaiting_payment'];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPageData(): array
    {
        return [
            'customer' => null,
            'currentCustomerId' => null,
            'defaultAddress' => null,
            'addresses' => [],
            'activeMembers' => [],
            'passiveMembers' => [],
            'prospectMembers' => [],
            'hasLeft' => false,
            'hasRight' => false,
            'binaryTree' => null,
            'networkTreeStats' => [
                'left_count' => 0,
                'right_count' => 0,
                'total_downlines' => 0,
            ],
            'generationNetwork' => [
                'data' => [],
                'current_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'last_page' => 1,
                'from' => null,
                'to' => null,
                'filters' => $this->normalizeGenerationFilters([]),
                'available_generations' => [],
            ],
            'orders' => [
                'data' => [],
                'current_page' => 1,
                'next_page' => null,
                'has_more' => false,
                'per_page' => 10,
                'total' => 0,
                'filters' => $this->normalizeOrdersFilters([]),
                'pending_review_count' => 0,
                'has_pending_review' => false,
            ],
            'walletTransactions' => [
                'data' => [],
                'current_page' => 1,
                'next_page' => null,
                'has_more' => false,
                'per_page' => 15,
                'total' => 0,
                'filters' => $this->normalizeWalletFilters([]),
            ],
            'hasPendingWithdrawal' => false,
            'bonusStats' => $this->formatBonusStats([]),
            'bonusTables' => [
                'referral_incentive' => [],
                'team_affiliate_commission' => [],
                'partner_team_commission' => [],
                'cashback_commission' => [],
                'promotions_rewards' => [],
                'retail_commission' => [],
                'lifetime_cash_rewards' => [],
            ],
            'lifetimeRewards' => [
                'summary' => [
                    'accumulated_left' => 0,
                    'accumulated_right' => 0,
                    'eligible_count' => 0,
                    'claimed_count' => 0,
                    'remaining_count' => 0,
                ],
                'rewards' => [],
                'claimed' => [],
            ],
            'promos' => [],
            'zennerCategories' => [],
            'zennerContents' => [],
            'midtrans' => [
                'env' => config('services.midtrans.env', 'sandbox'),
                'client_key' => config('services.midtrans.client_key', ''),
            ],
            'stats' => [
                'orders_total' => 0,
                'orders_pending' => 0,
                'network_total' => 0,
                'network_active' => 0,
                'network_level' => 0,
                'bonus_month' => 0,
                'bonus_lifetime' => 0,
                'bonus_available' => 0,
                'wallet_balance' => 0,
                'promo_active' => 0,
            ],
            'networkProfile' => [
                'username' => '—',
                'level' => '—',
                'referral_code' => '—',
                'balance' => 0,
            ],
            'networkStats' => [
                'left_count' => 0,
                'right_count' => 0,
                'total_downline' => 0,
                'omset_nb_left' => 0,
                'omset_nb_right' => 0,
                'omset_retail_left' => 0,
                'omset_retail_right' => 0,
                'omset_group' => 0,
            ],
            'securitySummary' => [
                'account_status_label' => 'Prospek',
                'email_verified' => false,
                'has_bank_account' => false,
                'has_npwp' => false,
                'last_order_at' => null,
            ],
        ];
    }

    /**
     * @param array{
     *   username:string,
     *   name:string,
     *   nik:string,
     *   gender:string,
     *   email:string,
     *   phone:string,
     *   bank_name:string,
     *   bank_account:string,
     *   npwp:?array{
     *     nama:?string,
     *     npwp:?string,
     *     jk:?int,
     *     npwp_date:?string,
     *     alamat:?string,
     *     menikah:?string,
     *     anak:?string,
     *     kerja:?string,
     *     office:?string
     *   }
     * } $payload
     */
    public function updateAccountProfile(Customer $authenticatedCustomer, array $payload): void
    {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer tidak ditemukan.',
            ]);
        }

        DB::transaction(function () use ($customer, $payload): void {
            $lockedCustomer = $this->dashboardRepository->findCustomerByIdForUpdate($customer->id);

            if (! $lockedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => 'Customer tidak ditemukan.',
                ]);
            }

            $incomingPhone = UpdateAccountProfileRequest::normalizePhone((string) ($payload['phone'] ?? ''));
            $currentPhone = UpdateAccountProfileRequest::normalizePhone((string) ($lockedCustomer->phone ?? ''));

            if ($incomingPhone !== '' && $incomingPhone !== $currentPhone) {
                $phoneUsageCount = $this->dashboardRepository->countCustomersByPhone($incomingPhone, $lockedCustomer->id);

                if ($phoneUsageCount >= 7) {
                    throw ValidationException::withMessages([
                        'phone' => UpdateAccountProfileRequest::sharedIdentityLimitMessage('Nomor HP/WhatsApp'),
                    ]);
                }
            }

            $this->dashboardRepository->updateCustomerAccount($lockedCustomer, [
                'username' => $payload['username'],
                'name' => $payload['name'],
                'nik' => $payload['nik'],
                'gender' => $payload['gender'],
                'email' => $payload['email'],
                'phone' => $incomingPhone,
                'bank_name' => $payload['bank_name'],
                'bank_account' => $payload['bank_account'],
            ]);

            $npwpPayload = $payload['npwp'] ?? null;

            if (is_array($npwpPayload)) {
                $this->dashboardRepository->upsertCustomerNpwp($lockedCustomer->id, $npwpPayload);
            }
        });
    }

    /**
     * @param  array{amount:float,notes?:string|null}  $payload
     * @return array{
     *   snapToken:string,
     *   walletTransactionId:int,
     *   successUrl:string,
     *   pendingUrl:string,
     *   message:string
     * }
     */
    public function createWalletTopupToken(Customer $authenticatedCustomer, array $payload): array
    {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer tidak ditemukan.',
            ]);
        }

        $amount = (float) ($payload['amount'] ?? 0);
        $notes = $this->normalizeWalletTransactionNotes($payload['notes'] ?? null);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal topup tidak valid.',
            ]);
        }

        $transactionRef = $this->generateWalletTransactionRef('TOPUP', $customer->id);
        $transaction = DB::transaction(function () use ($customer, $amount, $notes, $transactionRef): CustomerWalletTransaction {
            $lockedCustomer = $this->dashboardRepository->findCustomerByIdForUpdate($customer->id);

            if (! $lockedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => 'Customer tidak ditemukan.',
                ]);
            }

            $balance = (float) ($lockedCustomer->ewallet_saldo ?? 0);

            return $this->dashboardRepository->createWalletTransaction([
                'customer_id' => $lockedCustomer->id,
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $balance,
                'balance_after' => $balance,
                'status' => 'pending',
                'payment_method' => 'midtrans',
                'transaction_ref' => $transactionRef,
                'midtrans_transaction_id' => null,
                'notes' => $notes,
                'completed_at' => null,
                'is_system' => false,
                'midtrans_signature_key' => null,
            ]);
        });

        try {
            $snapToken = $this->midtransService->createSnapTokenForWalletTopup(
                $transactionRef,
                $amount,
                $customer
            );
        } catch (\RuntimeException $exception) {
            $this->dashboardRepository->updateWalletTransaction($transaction, [
                'status' => 'failed',
                'notes' => $this->appendWalletNote($transaction->notes, 'Gagal membuat token Midtrans: '.$exception->getMessage()),
            ]);

            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }

        return [
            'snapToken' => $snapToken,
            'walletTransactionId' => (int) $transaction->id,
            'successUrl' => route('dashboard', ['section' => 'wallet']),
            'pendingUrl' => route('dashboard', ['section' => 'wallet']),
            'message' => 'Token topup Midtrans berhasil dibuat.',
        ];
    }

    /**
     * @return array{
     *   transaction:array<string, mixed>,
     *   balance:float,
     *   message:string
     * }
     */
    public function syncWalletTopupStatus(Customer $authenticatedCustomer, int $walletTransactionId): array
    {
        $transaction = $this->dashboardRepository->findWalletTransactionForCustomer(
            $authenticatedCustomer->id,
            $walletTransactionId
        );

        if (! $transaction) {
            throw ValidationException::withMessages([
                'transaction' => 'Transaksi wallet tidak ditemukan.',
            ]);
        }

        if ($this->normalizeWalletTransactionType((string) $transaction->type) !== 'topup') {
            throw ValidationException::withMessages([
                'transaction' => 'Transaksi ini bukan transaksi topup.',
            ]);
        }

        $transactionRef = trim((string) ($transaction->transaction_ref ?? ''));

        if ($transactionRef === '') {
            throw ValidationException::withMessages([
                'transaction' => 'Referensi transaksi Midtrans tidak tersedia.',
            ]);
        }

        try {
            $gatewayPayload = $this->midtransService->getTransactionStatus($transactionRef);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }

        $transactionStatus = strtolower(trim((string) ($gatewayPayload['transaction_status'] ?? '')));
        $fraudStatus = strtolower(trim((string) ($gatewayPayload['fraud_status'] ?? '')));
        $mappedStatus = $this->mapMidtransWalletStatus($transactionStatus, $fraudStatus);

        DB::transaction(function () use ($authenticatedCustomer, $walletTransactionId, $mappedStatus, $gatewayPayload): void {
            $lockedTransaction = $this->dashboardRepository->findWalletTransactionForCustomer(
                $authenticatedCustomer->id,
                $walletTransactionId,
                true
            );

            if (! $lockedTransaction) {
                throw ValidationException::withMessages([
                    'transaction' => 'Transaksi wallet tidak ditemukan.',
                ]);
            }

            $lockedCustomer = $this->dashboardRepository->findCustomerByIdForUpdate($authenticatedCustomer->id);

            if (! $lockedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => 'Customer tidak ditemukan.',
                ]);
            }

            $currentStatus = $this->normalizeWalletTransactionStatus((string) ($lockedTransaction->status ?? ''));

            if ($currentStatus === 'completed') {
                return;
            }

            $amount = (float) ($lockedTransaction->amount ?? 0);

            if ($mappedStatus === 'completed') {
                $balanceBefore = (float) ($lockedCustomer->ewallet_saldo ?? 0);
                $balanceAfter = $balanceBefore + $amount;

                $this->dashboardRepository->adjustCustomerWalletBalance($lockedCustomer, $amount);

                $this->dashboardRepository->updateWalletTransaction($lockedTransaction, [
                    'status' => 'completed',
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'midtrans_transaction_id' => trim((string) ($gatewayPayload['transaction_id'] ?? '')) ?: $lockedTransaction->midtrans_transaction_id,
                    'midtrans_signature_key' => trim((string) ($gatewayPayload['signature_key'] ?? '')) ?: $lockedTransaction->midtrans_signature_key,
                    'completed_at' => now(),
                    'notes' => $this->appendWalletNote(
                        $lockedTransaction->notes,
                        'Topup Midtrans terkonfirmasi.'
                    ),
                ]);

                return;
            }

            $terminalStatus = $mappedStatus === 'cancelled' ? 'cancelled' : ($mappedStatus === 'failed' ? 'failed' : 'pending');

            $this->dashboardRepository->updateWalletTransaction($lockedTransaction, [
                'status' => $terminalStatus,
                'midtrans_transaction_id' => trim((string) ($gatewayPayload['transaction_id'] ?? '')) ?: $lockedTransaction->midtrans_transaction_id,
                'midtrans_signature_key' => trim((string) ($gatewayPayload['signature_key'] ?? '')) ?: $lockedTransaction->midtrans_signature_key,
                'completed_at' => in_array($terminalStatus, ['failed', 'cancelled'], true) ? now() : $lockedTransaction->completed_at,
            ]);
        });

        $freshTransaction = $this->dashboardRepository->findWalletTransactionForCustomer(
            $authenticatedCustomer->id,
            $walletTransactionId
        );
        $freshCustomer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $freshTransaction || ! $freshCustomer) {
            throw ValidationException::withMessages([
                'transaction' => 'Gagal memuat status transaksi wallet terbaru.',
            ]);
        }

        return [
            'transaction' => $this->formatWalletTransaction($freshTransaction),
            'balance' => (float) ($freshCustomer->ewallet_saldo ?? 0),
            'message' => 'Status topup wallet berhasil disinkronkan.',
        ];
    }

    /**
     * @param  array{amount:float,password:string,notes?:string|null}  $payload
     * @return array{
     *   transaction:array<string, mixed>,
     *   balance:float,
     *   message:string
     * }
     */
    public function submitWalletWithdrawal(Customer $authenticatedCustomer, array $payload): array
    {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer tidak ditemukan.',
            ]);
        }

        $rawAmount = (float) ($payload['amount'] ?? 0);
        $amount = (float) round($rawAmount);
        $password = (string) ($payload['password'] ?? '');
        $notes = $this->normalizeWalletTransactionNotes($payload['notes'] ?? null);
        $minimumRequestAmount = (float) $this->withdrawalMinimumRequestAmount();
        $estimatedReceivedAmount = max(0, $amount - self::WITHDRAWAL_ADMIN_FEE);

        if (! Hash::check($password, (string) $customer->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password tidak sesuai.',
            ]);
        }

        if (! filled($customer->bank_name) || ! filled($customer->bank_account)) {
            throw ValidationException::withMessages([
                'withdrawal' => 'Data rekening bank belum lengkap. Lengkapi profil bank terlebih dahulu.',
            ]);
        }

        if ($rawAmount <= 0 || abs($rawAmount - $amount) > 0.0001) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal withdrawal tidak valid.',
            ]);
        }

        if (((int) $amount) % self::WITHDRAWAL_STEP_AMOUNT !== 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal withdrawal harus kelipatan Rp 500.',
            ]);
        }

        if ($amount < $minimumRequestAmount) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'Nominal withdrawal minimal %s.',
                    $this->formatRupiahAmount($minimumRequestAmount),
                ),
            ]);
        }

        if ($estimatedReceivedAmount <= 0) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'Nominal withdrawal harus lebih besar dari biaya admin %s.',
                    $this->formatRupiahAmount(self::WITHDRAWAL_ADMIN_FEE),
                ),
            ]);
        }

        if ($this->dashboardRepository->hasPendingWithdrawal($customer->id)) {
            throw ValidationException::withMessages([
                'withdrawal' => 'Masih ada withdrawal yang sedang diproses.',
            ]);
        }

        $transactionRef = $this->generateWalletTransactionRef('WD', $customer->id);

        $createdTransaction = DB::transaction(function () use ($customer, $amount, $notes, $transactionRef): CustomerWalletTransaction {
            $lockedCustomer = $this->dashboardRepository->findCustomerByIdForUpdate($customer->id);

            if (! $lockedCustomer) {
                throw ValidationException::withMessages([
                    'customer' => 'Customer tidak ditemukan.',
                ]);
            }

            if ($this->dashboardRepository->hasPendingWithdrawal($lockedCustomer->id)) {
                throw ValidationException::withMessages([
                    'withdrawal' => 'Masih ada withdrawal yang sedang diproses.',
                ]);
            }

            $balanceBefore = (float) ($lockedCustomer->ewallet_saldo ?? 0);

            if ($balanceBefore < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo wallet tidak mencukupi untuk withdrawal.',
                ]);
            }

            $balanceAfter = $balanceBefore - $amount;

            $this->dashboardRepository->adjustCustomerWalletBalance($lockedCustomer, -$amount);

            return $this->dashboardRepository->createWalletTransaction([
                'customer_id' => $lockedCustomer->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'pending',
                'payment_method' => 'bank_transfer',
                'transaction_ref' => $transactionRef,
                'midtrans_transaction_id' => null,
                'notes' => $this->buildWithdrawalNotes($lockedCustomer, $notes, $amount),
                'completed_at' => null,
                'is_system' => false,
                'midtrans_signature_key' => null,
            ]);
        });

        $freshCustomer = $this->dashboardRepository->findCustomerById($customer->id);

        if (! $freshCustomer) {
            throw ValidationException::withMessages([
                'customer' => 'Gagal memuat saldo wallet terbaru.',
            ]);
        }

        return [
            'transaction' => $this->formatWalletTransaction($createdTransaction),
            'balance' => (float) ($freshCustomer->ewallet_saldo ?? 0),
            'message' => 'Permintaan withdrawal berhasil dikirim.',
        ];
    }

    /**
     * @param  array{
     *   search?:string|null,
     *   q?:string|null,
     *   type?:string|null,
     *   status?:string|null,
     *   direction?:string|null,
     *   payment_method?:string|null,
     *   date_from?:string|null,
     *   date_to?:string|null,
     *   amount_min?:float|int|string|null,
     *   amount_max?:float|int|string|null,
     *   sort?:string|null
     * } $filters
     * @return array{
     *   summary:array{
     *     balance_available:float,
     *     topup_30d:float,
     *     withdrawal_30d:float,
     *     netflow_30d:float,
     *     pending_count:int
     *   },
     *   window:array{
     *     days:int,
     *     from:string,
     *     to:string,
     *     timezone:string
     *   },
     *   data:list<array{
     *     id:int|string,
     *     type:string,
     *     type_label:string,
     *     direction:string,
     *     status:string,
     *     status_label:string,
     *     amount:float,
     *     balance_before:float,
     *     balance_after:float,
     *     payment_method:mixed,
     *     transaction_ref:mixed,
     *     created_at:?string,
     *     completed_at:?string,
     *     description:string
     *   }>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int
     * }
     */
    public function getWalletTransactionsPagination(
        Customer $authenticatedCustomer,
        int $page = 1,
        int $perPage = 15,
        array $filters = [],
    ): array {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer tidak ditemukan.',
            ]);
        }

        $normalizedFilters = $this->normalizeWalletFilters($filters);
        $walletPagination = $this->dashboardRepository->getPaginatedWalletTransactions(
            $customer->id,
            max(1, min(100, $perPage)),
            max(1, $page),
            $normalizedFilters,
        );

        $window = $this->buildWalletSummaryWindow(30);
        $summary = $this->dashboardRepository->getWalletTransactionSummary(
            $customer->id,
            $window['from_at'],
            $window['to_at'],
        );

        $topupLast30Days = (float) ($summary['topup_total'] ?? 0);
        $withdrawalLast30Days = (float) ($summary['withdrawal_total'] ?? 0);

        return array_merge([
            'summary' => [
                'balance_available' => (float) ($customer->ewallet_saldo ?? 0),
                'topup_30d' => $topupLast30Days,
                'withdrawal_30d' => $withdrawalLast30Days,
                'netflow_30d' => $topupLast30Days - $withdrawalLast30Days,
                'pending_count' => (int) ($summary['pending_count'] ?? 0),
            ],
            'window' => [
                'days' => $window['days'],
                'from' => $window['from_at']->toIso8601String(),
                'to' => $window['to_at']->toIso8601String(),
                'timezone' => $window['timezone'],
            ],
        ], $this->formatWalletTransactionsListPagination($walletPagination));
    }

    /**
     * @param array{
     *   q?:string|null,
     *   status?:string|null,
     *   sort?:string|null,
     *   date_from?:string|null,
     *   date_to?:string|null
     * } $filters
     * @return array{
     *   data:list<array<string,mixed>>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int,
     *   filters:array{
     *     q:string|null,
     *     status:string,
     *     sort:string,
     *     date_from:string|null,
     *     date_to:string|null
     *   },
     *   pending_review_count:int,
     *   has_pending_review:bool
     * }
     */
    public function getOrdersPagination(
        Customer $authenticatedCustomer,
        int $page = 1,
        int $perPage = 10,
        array $filters = [],
    ): array {
        $customer = $this->dashboardRepository->findCustomerById($authenticatedCustomer->id);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer tidak ditemukan.',
            ]);
        }

        $normalizedFilters = $this->normalizeOrdersFilters($filters);
        $ordersPagination = $this->dashboardRepository->getPaginatedOrders(
            $customer->id,
            max(1, min(100, $perPage)),
            max(1, $page),
            $normalizedFilters,
        );
        $pendingReviewCount = $this->dashboardRepository->countPendingReviewItems($customer->id);

        return $this->formatOrders($ordersPagination, $pendingReviewCount, $normalizedFilters);
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrderDetail(Customer $authenticatedCustomer, int $orderId): array
    {
        $order = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        return $this->formatOrder($order);
    }

    /**
     * @param  array{
     *   order_item_id:int,
     *   rating:int,
     *   title:?string,
     *   comment:string
     * }  $payload
     * @return array{
     *   order:array<string,mixed>,
     *   review:array<string,mixed>,
     *   message:string
     * }
     */
    public function submitOrderItemReview(Customer $authenticatedCustomer, int $orderId, array $payload): array
    {
        $order = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        $latestShipment = $order->shipments
            ->sortByDesc(fn ($shipment): int => $shipment->created_at?->getTimestamp() ?? 0)
            ->first();
        $isDeliveredOrder = $this->normalizeOrderStatus((string) ($order->status ?? 'pending')) === 'delivered'
            || strtolower(trim((string) ($latestShipment?->status ?? ''))) === 'delivered';

        if (! $isDeliveredOrder) {
            throw ValidationException::withMessages([
                'order' => 'Review hanya dapat dikirim untuk pesanan yang sudah diterima.',
            ]);
        }

        $orderItemId = (int) ($payload['order_item_id'] ?? 0);
        $rating = (int) ($payload['rating'] ?? 0);
        $title = isset($payload['title']) ? trim((string) $payload['title']) : null;
        $comment = trim((string) ($payload['comment'] ?? ''));

        /** @var OrderItem|null $orderItem */
        $orderItem = $order->items
            ->first(fn (OrderItem $item): bool => (int) $item->id === $orderItemId);

        if (! $orderItem) {
            throw ValidationException::withMessages([
                'order_item_id' => 'Item order tidak ditemukan pada pesanan ini.',
            ]);
        }

        if ((int) ($orderItem->product_id ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'order_item_id' => 'Produk pada item order tidak valid untuk direview.',
            ]);
        }

        $existingReview = ProductReview::query()
            ->where('customer_id', (int) $authenticatedCustomer->id)
            ->where('order_item_id', $orderItemId)
            ->first();

        if ($existingReview !== null) {
            throw ValidationException::withMessages([
                'order_item_id' => 'Produk pada item order ini sudah pernah Anda review.',
            ]);
        }

        $createdReview = DB::transaction(function () use ($authenticatedCustomer, $orderItem, $rating, $title, $comment): ProductReview {
            return ProductReview::query()->create([
                'customer_id' => (int) $authenticatedCustomer->id,
                'product_id' => (int) $orderItem->product_id,
                'order_item_id' => (int) $orderItem->id,
                'rating' => $rating,
                'title' => $title !== '' ? $title : null,
                'comment' => $comment,
                'is_verified_purchase' => true,
                'is_approved' => false,
            ]);
        });

        $refreshedOrder = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        return [
            'order' => $this->formatOrder($refreshedOrder ?? $order),
            'review' => [
                'id' => (int) $createdReview->id,
                'order_item_id' => (int) ($createdReview->order_item_id ?? 0),
                'rating' => (int) ($createdReview->rating ?? 0),
                'title' => $createdReview->title,
                'comment' => $createdReview->comment,
                'is_approved' => (bool) ($createdReview->is_approved ?? false),
                'created_at' => $createdReview->created_at?->toIso8601String(),
            ],
            'message' => 'Ulasan berhasil dikirim. Menunggu persetujuan admin.',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{name:string,position:string}
     */
    public function placeMember(Customer $authenticatedCustomer, array $payload): array
    {
        throw ValidationException::withMessages([
            'error' => 'Placement binary tidak tersedia pada schema database existing.',
        ]);
    }

    /**
     * Cek status pembayaran order via Midtrans dan sinkronkan ke database.
     *
     * @return array{order:array<string, mixed>,message:string}
     */
    public function checkOrderPaymentStatus(Customer $authenticatedCustomer, int $orderId): array
    {
        $order = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        $latestPayment = $order->payments
            ->sortByDesc(fn ($payment): int => $payment->created_at?->getTimestamp() ?? 0)
            ->first();

        if (! $latestPayment) {
            throw ValidationException::withMessages([
                'payment' => 'Data pembayaran tidak tersedia pada order ini.',
            ]);
        }

        $methodCode = strtolower((string) ($latestPayment->method?->code ?? ''));

        if (! in_array($methodCode, ['p-001', 'midtrans'], true)) {
            return [
                'order' => $this->formatOrder($order),
                'message' => 'Order ini tidak menggunakan metode Midtrans.',
            ];
        }

        try {
            $gatewayPayload = $this->midtransService->getTransactionStatus((string) $order->order_no);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }

        $transactionStatus = strtolower(trim((string) ($gatewayPayload['transaction_status'] ?? '')));
        $fraudStatus = strtolower(trim((string) ($gatewayPayload['fraud_status'] ?? '')));

        if ($transactionStatus === '') {
            $statusCode = trim((string) ($gatewayPayload['status_code'] ?? ''));
            $statusMessage = trim((string) ($gatewayPayload['status_message'] ?? ''));
            $statusMessageLower = strtolower($statusMessage);
            $isTransactionNotFound = $statusCode === '404'
                || str_contains($statusMessageLower, 'not found')
                || str_contains($statusMessageLower, "doesn't exist")
                || str_contains($statusMessageLower, 'does not exist')
                || str_contains($statusMessageLower, 'not exist');

            if ($isTransactionNotFound) {
                $existingStatus = (string) ($latestPayment->status ?? 'pending');
                $this->dashboardRepository->updatePaymentFromGateway($latestPayment, $existingStatus, $gatewayPayload);

                $refreshedOrder = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

                return [
                    'order' => $this->formatOrder($refreshedOrder ?? $order),
                    'message' => 'Transaksi Midtrans belum tersedia untuk order ini. Selesaikan pembayaran lalu cek kembali.',
                ];
            }

            $resolvedMessage = $statusMessage !== ''
                ? "Midtrans: {$statusMessage}"
                : 'Status transaksi Midtrans tidak tersedia.';

            throw ValidationException::withMessages([
                'payment' => $resolvedMessage,
            ]);
        }

        $mappedStatus = $this->mapMidtransPaymentStatus($transactionStatus, $fraudStatus);
        $previousPaymentStatus = $this->normalizePaymentStatus((string) ($latestPayment->status ?? ''));

        $this->dashboardRepository->updatePaymentFromGateway($latestPayment, $mappedStatus, $gatewayPayload);
        $this->dashboardRepository->createPaymentTransaction(
            $latestPayment,
            $mappedStatus,
            (float) ($latestPayment->amount ?? 0),
            $gatewayPayload,
        );

        if ($mappedStatus === 'paid') {
            $this->dashboardRepository->markOrderAsPaid($order);
            $this->runBonusEngineAfterPaymentSettlement(
                $order,
                $previousPaymentStatus,
                (int) ($authenticatedCustomer->status ?? 0),
            );
        }

        $refreshedOrder = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        if (! $refreshedOrder) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan setelah sinkronisasi status pembayaran.',
            ]);
        }

        return [
            'order' => $this->formatOrder($refreshedOrder),
            'message' => 'Status pembayaran berhasil diperbarui dari Midtrans.',
        ];
    }

    private function runBonusEngineAfterPaymentSettlement(
        Order $order,
        ?string $previousPaymentStatus,
        int $customerStatus,
    ): void {
        if ($customerStatus !== 3) {
            return;
        }

        if ($previousPaymentStatus === 'paid' || (bool) ($order->bonus_generated ?? false)) {
            return;
        }

        if (! $this->dashboardRepository->markOrderBonusGenerated($order)) {
            return;
        }

        $this->dashboardRepository->callBonusEngine((int) $order->id);
    }

    /**
     * Buat token Midtrans untuk order existing yang belum dibayar.
     *
     * @return array<string, string|null>
     */
    public function createMidtransPayNowToken(Customer $authenticatedCustomer, int $orderId): array
    {
        $order = $this->dashboardRepository->findOrderForCustomer($authenticatedCustomer->id, $orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        $latestPayment = $order->payments
            ->sortByDesc(fn ($payment): int => $payment->created_at?->getTimestamp() ?? 0)
            ->first();

        if (! $latestPayment) {
            throw ValidationException::withMessages([
                'payment' => 'Data pembayaran tidak tersedia pada order ini.',
            ]);
        }

        $methodCode = strtolower((string) ($latestPayment->method?->code ?? ''));

        if (! in_array($methodCode, ['p-001', 'midtrans'], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Order ini tidak menggunakan metode pembayaran Midtrans.',
            ]);
        }

        $normalizedPaymentStatus = $this->normalizePaymentStatus((string) ($latestPayment->status ?? ''));

        if (in_array($normalizedPaymentStatus, ['paid', 'refunded'], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Order ini sudah tidak dapat dibayar ulang.',
            ]);
        }

        $metadata = is_array($latestPayment->metadata_json) ? $latestPayment->metadata_json : [];
        $snapToken = trim((string) ($metadata['snap_token'] ?? ''));

        if ($snapToken !== '') {
            return [
                'snapToken' => $snapToken,
                'redirectUrl' => null,
                'successUrl' => route('dashboard'),
                'pendingUrl' => route('dashboard'),
                'message' => 'Token pembayaran Midtrans siap digunakan.',
            ];
        }

        try {
            $newSnapToken = $this->midtransService->createSnapTokenForOrder($order, $authenticatedCustomer);
        } catch (\RuntimeException $exception) {
            $errorMessage = strtolower($exception->getMessage());

            if (str_contains($errorMessage, 'already been taken')) {
                try {
                    $gatewayPayload = $this->midtransService->getTransactionStatus((string) $order->order_no);
                } catch (\RuntimeException $statusException) {
                    throw ValidationException::withMessages([
                        'payment' => $statusException->getMessage(),
                    ]);
                }
                $transactionStatus = strtolower(trim((string) ($gatewayPayload['transaction_status'] ?? '')));
                $fraudStatus = strtolower(trim((string) ($gatewayPayload['fraud_status'] ?? '')));

                if ($transactionStatus !== '') {
                    $mappedStatus = $this->mapMidtransPaymentStatus($transactionStatus, $fraudStatus);

                    $this->dashboardRepository->updatePaymentFromGateway($latestPayment, $mappedStatus, $gatewayPayload);
                    $this->dashboardRepository->createPaymentTransaction(
                        $latestPayment,
                        $mappedStatus,
                        (float) ($latestPayment->amount ?? 0),
                        $gatewayPayload,
                    );

                    if ($mappedStatus === 'paid') {
                        $this->dashboardRepository->markOrderAsPaid($order);

                        throw ValidationException::withMessages([
                            'payment' => 'Order ini sudah dibayar.',
                        ]);
                    }
                }

                $redirectUrl = $this->extractMidtransRedirectUrl($gatewayPayload);

                if ($redirectUrl !== null) {
                    return [
                        'snapToken' => null,
                        'redirectUrl' => $redirectUrl,
                        'successUrl' => route('dashboard'),
                        'pendingUrl' => route('dashboard'),
                        'message' => 'Transaksi Midtrans sudah tersedia. Lanjutkan pembayaran dari halaman Midtrans.',
                    ];
                }

                throw ValidationException::withMessages([
                    'payment' => 'Transaksi Midtrans sudah dibuat sebelumnya. Silakan cek status pembayaran terlebih dahulu.',
                ]);
            }

            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }

        $updatedMetadata = array_merge($metadata, [
            'snap_token' => $newSnapToken,
            'snap_created_at' => now()->toIso8601String(),
        ]);

        $this->dashboardRepository->updatePaymentMetadata($latestPayment, $updatedMetadata);

        return [
            'snapToken' => $newSnapToken,
            'redirectUrl' => null,
            'successUrl' => route('dashboard'),
            'pendingUrl' => route('dashboard'),
            'message' => 'Token pembayaran Midtrans berhasil dibuat.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCustomer(Customer $customer): array
    {
        $customer->loadMissing('npwp');

        $isHematMode = (bool) config('services.qontak.wa_gateway_hemat_mode', false);
        $normalizedPhone = app(QontactService::class)->normalizePhoneNumber((string) ($customer->phone ?? ''));
        $isWaConfirmed = $isHematMode || ($normalizedPhone !== '' && CustomerWhatsAppConfirmation::isConfirmed($normalizedPhone));

        return [
            'id' => $customer->id,
            'username' => $customer->username,
            'ewallet_id' => $customer->ewallet_id,
            'nik' => $customer->nik,
            'name' => $customer->name,
            'gender' => $customer->gender,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'alamat' => $customer->alamat,
            'bank_name' => $customer->bank_name,
            'bank_account' => $customer->bank_account,
            'npwp' => $this->formatCustomerNpwp($customer->npwp),
            'avatar_url' => null,
            'tier' => $customer->level ?? $this->memberStatusLabel((int) ($customer->status ?? 1)),
            'member_since' => $customer->created_at?->toIso8601String(),
            'wallet_balance' => (float) ($customer->ewallet_saldo ?? 0),
            'status' => (int) ($customer->status ?? 0),
            // 'has_placement' => $customer->networkMatrixes()->exists(),
            'has_placement' => $customer->where('status', 3)->exists(),
            'is_wa_confirmed' => $isWaConfirmed,
        ];
    }

    private function buildWaConfirmationUrl(Customer $customer): ?string
    {
        if (config('services.qontak.wa_gateway_hemat_mode', true)) {
            return null;
        }

        $gatewayNumber = preg_replace('/[^0-9]/', '', (string) config('services.qontak.wa_gateway_number', ''));

        if ($gatewayNumber === '') {
            return null;
        }

        $normalizedPhone = app(QontactService::class)->normalizePhoneNumber((string) ($customer->phone ?? ''));

        if ($normalizedPhone !== '' && CustomerWhatsAppConfirmation::isConfirmed($normalizedPhone)) {
            return null;
        }

        $signedUrl = URL::signedRoute('wa.confirm', ['customer' => $customer->getKey()], now()->addHours(48));

        $username = (string) ($customer->username ?? '');
        $message = "Halo, saya *{$username}* ingin mengkonfirmasi nomor WhatsApp ini untuk mengaktifkan fitur topup dan penarikan saldo."
            ."\n\nLangkah konfirmasi:"
            ."\n1. Kirim pesan ini ke kami terlebih dahulu."
            ."\n2. Setelah pesan terkirim, klik link berikut untuk menyelesaikan konfirmasi:"
            ."\n{$signedUrl}"
            ."\n\nLink berlaku 48 jam.";

        return 'https://wa.me/'.$gatewayNumber.'?text='.rawurlencode($message);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatCustomerNpwp(?CustomerNpwp $npwp): ?array
    {
        if (! $npwp) {
            return null;
        }

        return [
            'nama' => $npwp->nama,
            'npwp' => $npwp->npwp,
            'jk' => $npwp->jk !== null ? (int) $npwp->jk : null,
            'npwp_date' => $npwp->npwp_date?->toDateString(),
            'alamat' => $npwp->alamat,
            'menikah' => $npwp->menikah,
            'anak' => $npwp->anak,
            'kerja' => $npwp->kerja,
            'office' => $npwp->office,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatAddress(?CustomerAddress $address): ?array
    {
        if (! $address) {
            return null;
        }

        return [
            'id' => $address->id,
            'label' => $address->label ?? 'Alamat',
            'recipient_name' => $address->recipient_name,
            'phone' => $address->recipient_phone,
            'address_line' => $address->address_line1,
            'city' => $address->city_label,
            'province' => $address->province_label,
            'postal_code' => $address->postal_code ?? '-',
            'is_default' => (bool) $address->is_default,
        ];
    }

    /**
     * @param  Collection<int, CustomerAddress>  $addresses
     * @return list<array<string, mixed>>
     */
    private function formatAddresses(Collection $addresses): array
    {
        return $addresses->map(fn (CustomerAddress $address): array => [
            'id' => $address->id,
            'label' => $address->label,
            'is_default' => (bool) $address->is_default,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->recipient_phone,
            'address_line1' => $address->address_line1,
            'address_line2' => $address->address_line2,
            'province_label' => $address->province_label,
            'province_id' => (int) $address->province_id,
            'city_label' => $address->city_label,
            'city_id' => (int) $address->city_id,
            'district' => $address->district,
            'district_lion' => $address->district_lion,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
            'description' => $address->description,
        ])->toArray();
    }

    /**
     * @param array{
     *   referral_incentive?:array{amount?:float|int,count?:int},
     *   team_affiliate_commission?:array{amount?:float|int,count?:int},
     *   partner_team_commission?:array{amount?:float|int,count?:int},
     *   cashback_commission?:array{amount?:float|int,count?:int},
     *   promotions_rewards?:array{amount?:float|int,count?:int},
     *   retail_commission?:array{amount?:float|int,count?:int},
     *   lifetime_cash_rewards?:array{amount?:float|int,count?:int},
     *   total_bonus?:array{amount?:float|int,count?:int}
     * } $stats
     * @return list<array{
     *   key:string,
     *   title:string,
     *   icon:string,
     *   amount:float,
     *   count:int
     * }>
     */
    private function formatBonusStats(array $stats): array
    {
        $definitions = [
            'referral_incentive' => [
                'title' => 'Referral Incentive',
                'icon' => 'i-lucide-users',
            ],
            'team_affiliate_commission' => [
                'title' => 'Team Affiliate Commission',
                'icon' => 'i-lucide-handshake',
            ],
            'partner_team_commission' => [
                'title' => 'Partner Team Commission',
                'icon' => 'i-lucide-network',
            ],
            'cashback_commission' => [
                'title' => 'Cashback Commission',
                'icon' => 'i-lucide-percent',
            ],
            'promotions_rewards' => [
                'title' => 'Promotions Rewards',
                'icon' => 'i-lucide-gift',
            ],
            'retail_commission' => [
                'title' => 'Retail Commission',
                'icon' => 'i-lucide-store',
            ],
            'lifetime_cash_rewards' => [
                'title' => 'Lifetime Cash Rewards',
                'icon' => 'i-lucide-trophy',
            ],
            'total_bonus' => [
                'title' => 'Total Bonus',
                'icon' => 'i-lucide-wallet-cards',
            ],
        ];

        return collect($definitions)->map(
            function (array $definition, string $key) use ($stats): array {
                $entry = $stats[$key] ?? [];

                return [
                    'key' => $key,
                    'title' => (string) ($definition['title'] ?? $key),
                    'icon' => (string) ($definition['icon'] ?? 'i-lucide-coins'),
                    'amount' => (float) ($entry['amount'] ?? 0),
                    'count' => (int) ($entry['count'] ?? 0),
                ];
            }
        )->values()->all();
    }

    /**
     * @param  Collection<int, CustomerBonusSponsor>  $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusSponsors(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusSponsor $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'referral_incentive',
                'type_label' => 'Referral Incentive',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => $this->formatBonusMember($bonus->fromMember),
                'meta' => [
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, object>  $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusMatchings(Collection $bonuses): array
    {
        return $bonuses->map(function (object $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'team_affiliate_commission',
                'type_label' => 'Team Affiliate Commission',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => $this->formatBonusMember($bonus->fromMember),
                'meta' => [
                    'level' => $bonus->level !== null ? (int) $bonus->level : null,
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, object>  $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusPairings(Collection $bonuses): array
    {
        return $bonuses->map(function (object $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'partner_team_commission',
                'type_label' => 'Partner Team Commission',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => $this->formatBonusMember($bonus->sourceMember),
                'meta' => [
                    'pairing_count' => (int) ($bonus->pairing_count ?? 0),
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, CustomerBonusCashback>  $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusCashbacks(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusCashback $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'cashback_commission',
                'type_label' => 'Cashback Commission',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => null,
                'meta' => [
                    'order_id' => $bonus->order_id !== null ? (int) $bonus->order_id : null,
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, CustomerBonusReward>  $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatPromotionRewards(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusReward $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'promotions_rewards',
                'type_label' => 'Promotions Rewards',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => null,
                'meta' => [
                    'reward_type' => $bonus->reward_type,
                    'reward_name' => $bonus->reward,
                    'bv' => (float) ($bonus->bv ?? 0),
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, CustomerBonusRoyalty>  $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusRetails(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusRoyalty $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'retail_commission',
                'type_label' => 'Retail Commission',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => $this->formatBonusMember($bonus->fromMember),
                'meta' => [
                    'index_value' => (float) ($bonus->index_value ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, CustomerBonusReward>  $bonuses
     * @return list<array<string, mixed>>
     */
    private function formatBonusLifetimeCashRewards(Collection $bonuses): array
    {
        return $bonuses->map(function (CustomerBonusReward $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'type' => 'lifetime_cash_rewards',
                'type_label' => 'Lifetime Cash Rewards',
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
                'from_member' => null,
                'meta' => [
                    'reward_name' => $bonus->reward,
                    'bv' => (float) ($bonus->bv ?? 0),
                ],
            ];
        })->values()->all();
    }

    /**
     * @return array{name:string,email:string|null}|null
     */
    private function formatBonusMember(?Customer $customer): ?array
    {
        if (! $customer) {
            return null;
        }

        return [
            'name' => $customer->name,
            'email' => $customer->email,
        ];
    }

    private function normalizeBonusStatus(int|string|null $status): string
    {
        if (is_string($status)) {
            $normalized = strtolower(trim($status));

            if (in_array($normalized, ['1', 'released', 'success', 'completed', 'settlement'], true)) {
                return 'released';
            }

            return 'pending';
        }

        return ((int) $status) === 1 ? 'released' : 'pending';
    }

    private function bonusStatusLabel(string $status): string
    {
        return $status === 'released' ? 'Released' : 'Pending';
    }

    /**
     * @param  Collection<int, Reward>  $activeRewards
     * @param  Collection<int, string>  $claimedRewardNames
     * @param  Collection<int, CustomerBonusReward>  $claimedLifetimeRewards
     * @return array{
     *   summary:array{
     *     accumulated_left:float,
     *     accumulated_right:float,
     *     eligible_count:int,
     *     claimed_count:int,
     *     remaining_count:int
     *   },
     *   rewards:list<array<string, mixed>>,
     *   claimed:list<array<string, mixed>>
     * }
     */
    private function formatLifetimeRewardsData(
        Customer $customer,
        Collection $activeRewards,
        Collection $claimedRewardNames,
        Collection $claimedLifetimeRewards,
    ): array {
        $accumulatedLeft = (float) ($customer->omzet_group ?? 0);
        $accumulatedRight = (float) ($customer->omzet_group ?? 0);
        $claimedLookup = $claimedRewardNames
            ->map(fn (string $reward): string => strtolower(trim($reward)))
            ->filter(fn (string $reward): bool => $reward !== '')
            ->values()
            ->all();

        $rewards = $activeRewards->map(function (Reward $reward) use (
            $accumulatedLeft,
            $accumulatedRight,
            $claimedLookup
        ): array {
            $requiredBv = (float) ($reward->bv ?? 0);
            $isClaimed = in_array(strtolower(trim((string) $reward->name)), $claimedLookup, true);
            $canClaim = ! $isClaimed
                && $accumulatedLeft >= $requiredBv
                && $accumulatedRight >= $requiredBv;
            $progressLeft = $requiredBv > 0
                ? min(100.0, ($accumulatedLeft / $requiredBv) * 100.0)
                : 100.0;
            $progressRight = $requiredBv > 0
                ? min(100.0, ($accumulatedRight / $requiredBv) * 100.0)
                : 100.0;

            return [
                'id' => (int) $reward->id,
                'name' => $reward->name,
                'reward' => $reward->reward,
                'bv' => $requiredBv,
                'value' => (float) ($reward->value ?? 0),
                'can_claim' => $canClaim,
                'is_claimed' => $isClaimed,
                'accumulated_left' => $accumulatedLeft,
                'accumulated_right' => $accumulatedRight,
                'progress_left' => round($progressLeft, 2),
                'progress_right' => round($progressRight, 2),
                'progress_percent' => round(min($progressLeft, $progressRight), 2),
            ];
        })->values();

        $claimed = $claimedLifetimeRewards->map(function (CustomerBonusReward $bonus): array {
            $status = $this->normalizeBonusStatus($bonus->status);

            return [
                'id' => (int) $bonus->id,
                'reward' => $bonus->reward,
                'bv' => (float) ($bonus->bv ?? 0),
                'amount' => (float) ($bonus->amount ?? 0),
                'status' => $status,
                'status_label' => $this->bonusStatusLabel($status),
                'description' => $bonus->description,
                'created_at' => $bonus->created_at?->toIso8601String(),
            ];
        })->values();

        $eligibleCount = $rewards->where('can_claim', true)->count();
        $claimedCount = $rewards->where('is_claimed', true)->count();
        $totalRewardCount = $rewards->count();

        return [
            'summary' => [
                'accumulated_left' => $accumulatedLeft,
                'accumulated_right' => $accumulatedRight,
                'eligible_count' => $eligibleCount,
                'claimed_count' => $claimedCount,
                'remaining_count' => max(0, $totalRewardCount - $claimedCount),
            ],
            'rewards' => $rewards->all(),
            'claimed' => $claimed->all(),
        ];
    }

    /**
     * @param  Collection<int, CustomerWalletTransaction>  $transactions
     * @return list<array<string, mixed>>
     */
    private function formatWalletTransactions(Collection $transactions): array
    {
        return $transactions
            ->map(fn (CustomerWalletTransaction $transaction): array => $this->formatWalletTransaction($transaction))
            ->values()
            ->all();
    }

    /**
     * @param  array{
     *   search?:string|null,
     *   type?:string|null,
     *   status?:string|null,
     *   direction?:string|null,
     *   payment_method?:string|null,
     *   date_from?:string|null,
     *   date_to?:string|null,
     *   amount_min?:float|int|string|null,
     *   amount_max?:float|int|string|null,
     *   sort?:string|null
     * }  $filters
     * @return array{
     *   data:list<array<string, mixed>>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int,
     *   filters:array{
     *     search:string|null,
     *     type:string|null,
     *     status:string|null,
     *     direction:string|null,
     *     payment_method:string|null,
     *     date_from:string|null,
     *     date_to:string|null,
     *     amount_min:float|null,
     *     amount_max:float|null,
     *     sort:string
     *   }
     * }
     */
    private function formatWalletTransactionsPagination(LengthAwarePaginator $paginator, array $filters): array
    {
        $transactions = collect($paginator->items())
            ->filter(fn (mixed $item): bool => $item instanceof CustomerWalletTransaction)
            ->map(fn (CustomerWalletTransaction $transaction): array => $this->formatWalletTransaction($transaction))
            ->values()
            ->all();

        return [
            'data' => $transactions,
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'has_more' => $paginator->hasMorePages(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'filters' => $this->normalizeWalletFilters($filters),
        ];
    }

    /**
     * @return array{
     *   data:list<array{
     *     id:int|string,
     *     type:string,
     *     type_label:string,
     *     direction:string,
     *     status:string,
     *     status_label:string,
     *     amount:float,
     *     balance_before:float,
     *     balance_after:float,
     *     payment_method:mixed,
     *     transaction_ref:mixed,
     *     created_at:?string,
     *     completed_at:?string,
     *     description:string
     *   }>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int
     * }
     */
    private function formatWalletTransactionsListPagination(LengthAwarePaginator $paginator): array
    {
        $transactions = collect($paginator->items())
            ->filter(fn (mixed $item): bool => $item instanceof CustomerWalletTransaction)
            ->map(fn (CustomerWalletTransaction $transaction): array => $this->formatWalletTransactionListItem($transaction))
            ->values()
            ->all();

        return [
            'data' => $transactions,
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'has_more' => $paginator->hasMorePages(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    /**
     * @return array{
     *   id:int|string,
     *   type:string,
     *   type_label:string,
     *   direction:string,
     *   status:string,
     *   status_label:string,
     *   amount:float,
     *   balance_before:float,
     *   balance_after:float,
     *   payment_method:mixed,
     *   transaction_ref:mixed,
     *   created_at:?string,
     *   completed_at:?string,
     *   description:string
     * }
     */
    private function formatWalletTransactionListItem(CustomerWalletTransaction $transaction): array
    {
        $type = $this->normalizeWalletTransactionType((string) ($transaction->type ?? ''));
        $status = $this->normalizeWalletTransactionStatus((string) ($transaction->status ?? ''));
        $timezone = (string) config('app.timezone', 'Asia/Jakarta');

        return [
            'id' => $transaction->id,
            'type' => $type,
            'type_label' => $this->walletTransactionTypeLabel($type),
            'direction' => $this->walletTransactionDirection($type),
            'status' => $status,
            'status_label' => $this->walletTransactionStatusLabel($status),
            'amount' => (float) ($transaction->amount ?? 0),
            'balance_before' => (float) ($transaction->balance_before ?? 0),
            'balance_after' => (float) ($transaction->balance_after ?? 0),
            'payment_method' => $transaction->payment_method,
            'transaction_ref' => $transaction->transaction_ref,
            'created_at' => $transaction->created_at?->copy()?->timezone($timezone)->toIso8601String(),
            'completed_at' => $transaction->completed_at?->copy()?->timezone($timezone)->toIso8601String(),
            'description' => $this->walletTransactionDescription($transaction, $type),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatWalletTransaction(CustomerWalletTransaction $transaction): array
    {
        $type = $this->normalizeWalletTransactionType((string) ($transaction->type ?? ''));
        $status = $this->normalizeWalletTransactionStatus((string) ($transaction->status ?? ''));

        return [
            'id' => $transaction->id,
            'type' => $type,
            'type_label' => $this->walletTransactionTypeLabel($type),
            'direction' => $this->walletTransactionDirection($type),
            'status' => $status,
            'status_label' => $this->walletTransactionStatusLabel($status),
            'amount' => (float) ($transaction->amount ?? 0),
            'balance_before' => (float) ($transaction->balance_before ?? 0),
            'balance_after' => (float) ($transaction->balance_after ?? 0),
            'payment_method' => $transaction->payment_method,
            'transaction_ref' => $transaction->transaction_ref,
            'midtrans_transaction_id' => $transaction->midtrans_transaction_id,
            'notes' => $this->normalizeWalletTransactionNotes($transaction->notes),
            'is_system' => (bool) ($transaction->is_system ?? false),
            'created_at' => $transaction->created_at?->toIso8601String(),
            'completed_at' => $transaction->completed_at?->toIso8601String(),
            'description' => $this->walletTransactionDescription($transaction, $type),
            'to' => null,
        ];
    }

    private function walletTransactionDescription(CustomerWalletTransaction $transaction, string $normalizedType): string
    {
        $parts = [$this->walletTransactionTypeLabel($normalizedType)];

        if (filled($transaction->transaction_ref)) {
            $parts[] = 'Ref: '.$transaction->transaction_ref;
        }

        if (filled($transaction->payment_method)) {
            $parts[] = strtoupper((string) $transaction->payment_method);
        }

        return implode(' • ', $parts);
    }

    private function normalizeWalletTransactionType(string $type): string
    {
        $normalized = strtolower(trim($type));

        return match ($normalized) {
            'topup', 'withdrawal', 'bonus', 'purchase', 'refund', 'tax' => $normalized,
            default => 'other',
        };
    }

    private function walletTransactionDirection(string $normalizedType): string
    {
        return in_array($normalizedType, ['topup', 'bonus', 'refund'], true) ? 'credit' : 'debit';
    }

    private function walletTransactionTypeLabel(string $normalizedType): string
    {
        return match ($normalizedType) {
            'topup' => 'Top Up Saldo',
            'withdrawal' => 'Penarikan Saldo',
            'bonus' => 'Bonus Member',
            'purchase' => 'Pembayaran Belanja',
            'refund' => 'Refund',
            'tax' => 'Potongan Pajak',
            default => 'Transaksi Wallet',
        };
    }

    private function normalizeWalletTransactionStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'completed', 'success', 'succeeded', 'settlement' => 'completed',
            'failed', 'failure', 'deny', 'expired', 'expire' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }

    private function walletTransactionStatusLabel(string $normalizedStatus): string
    {
        return match ($normalizedStatus) {
            'completed' => 'Selesai',
            'failed' => 'Gagal',
            'cancelled' => 'Dibatalkan',
            default => 'Menunggu',
        };
    }

    private function normalizeWalletTransactionNotes(?string $notes): ?string
    {
        if (! is_string($notes)) {
            return null;
        }

        $trimmed = trim($notes);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @param  array{level?:int|string|null}  $filters
     * @return array{level:int|null}
     */
    private function normalizeGenerationFilters(array $filters): array
    {
        $level = isset($filters['level']) && is_numeric($filters['level'])
            ? max(1, (int) $filters['level'])
            : null;

        return [
            'level' => $level,
        ];
    }

    /**
     * @param  array{
     *   search?:string|null,
     *   q?:string|null,
     *   type?:string|null,
     *   status?:string|null,
     *   direction?:string|null,
     *   payment_method?:string|null,
     *   date_from?:string|null,
     *   date_to?:string|null,
     *   amount_min?:float|int|string|null,
     *   amount_max?:float|int|string|null,
     *   sort?:string|null
     * }  $filters
     * @return array{
     *   search:string|null,
     *   type:string|null,
     *   status:string|null,
     *   direction:string|null,
     *   payment_method:string|null,
     *   date_from:string|null,
     *   date_to:string|null,
     *   amount_min:float|null,
     *   amount_max:float|null,
     *   sort:string
     * }
     */
    private function normalizeWalletFilters(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ($filters['q'] ?? '')));
        $type = strtolower(trim((string) ($filters['type'] ?? '')));
        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        $direction = strtolower(trim((string) ($filters['direction'] ?? '')));
        $paymentMethod = strtolower(trim((string) ($filters['payment_method'] ?? '')));
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        $sort = strtolower(trim((string) ($filters['sort'] ?? 'newest')));

        $amountMin = is_numeric($filters['amount_min'] ?? null)
            ? max(0, (float) $filters['amount_min'])
            : null;
        $amountMax = is_numeric($filters['amount_max'] ?? null)
            ? max(0, (float) $filters['amount_max'])
            : null;

        if ($type === '' || $type === 'all' || ! in_array($type, $this->walletSupportedTypes(), true)) {
            $type = null;
        }

        if ($status === '' || $status === 'all' || ! in_array($status, $this->walletSupportedStatuses(), true)) {
            $status = null;
        }

        if ($direction === '' || $direction === 'all' || ! in_array($direction, $this->walletSupportedDirections(), true)) {
            $direction = null;
        }

        if ($amountMin !== null && $amountMax !== null && $amountMax < $amountMin) {
            $amountMax = $amountMin;
        }

        if (! in_array($sort, ['newest', 'oldest', 'highest', 'lowest'], true)) {
            $sort = 'newest';
        }

        return [
            'search' => $search !== '' ? $search : null,
            'type' => $type,
            'status' => $status,
            'direction' => $direction,
            'payment_method' => $paymentMethod !== '' ? $paymentMethod : null,
            'date_from' => $dateFrom !== '' ? $dateFrom : null,
            'date_to' => $dateTo !== '' ? $dateTo : null,
            'amount_min' => $amountMin,
            'amount_max' => $amountMax,
            'sort' => $sort,
        ];
    }

    /**
     * @param array{
     *   q?:string|null,
     *   status?:string|null,
     *   sort?:string|null,
     *   date_from?:string|null,
     *   date_to?:string|null
     * } $filters
     * @return array{
     *   q:string|null,
     *   status:string,
     *   sort:string,
     *   date_from:string|null,
     *   date_to:string|null
     * }
     */
    private function normalizeOrdersFilters(array $filters): array
    {
        $status = strtolower(trim((string) ($filters['status'] ?? 'all')));
        $sort = strtolower(trim((string) ($filters['sort'] ?? 'newest')));

        if (in_array($status, ['belum bayar', 'waiting_payment', 'awaiting_payment'], true)) {
            $status = 'unpaid';
        }

        if (! in_array($status, ['all', 'unpaid', 'pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'], true)) {
            $status = 'all';
        }

        if (! in_array($sort, ['newest', 'oldest', 'highest', 'lowest'], true)) {
            $sort = 'newest';
        }

        $query = trim((string) ($filters['q'] ?? ''));
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));

        return [
            'q' => $query !== '' ? $query : null,
            'status' => $status,
            'sort' => $sort,
            'date_from' => $dateFrom !== '' ? $dateFrom : null,
            'date_to' => $dateTo !== '' ? $dateTo : null,
        ];
    }

    /**
     * @return list<string>
     */
    private function walletSupportedTypes(): array
    {
        return ['topup', 'withdrawal', 'bonus', 'purchase', 'refund', 'tax'];
    }

    /**
     * @return list<string>
     */
    private function walletSupportedStatuses(): array
    {
        return ['pending', 'completed', 'failed', 'cancelled'];
    }

    /**
     * @return list<string>
     */
    private function walletSupportedDirections(): array
    {
        return ['credit', 'debit'];
    }

    /**
     * @return array{
     *   days:int,
     *   from_at:CarbonInterface,
     *   to_at:CarbonInterface,
     *   timezone:string
     * }
     */
    private function buildWalletSummaryWindow(int $days): array
    {
        $safeDays = max(1, $days);
        $timezone = (string) config('app.timezone', 'Asia/Jakarta');
        $now = now()->timezone($timezone);

        return [
            'days' => $safeDays,
            'from_at' => $now->copy()->subDays($safeDays - 1)->startOfDay(),
            'to_at' => $now->copy()->endOfDay(),
            'timezone' => $timezone,
        ];
    }

    private function mapMidtransWalletStatus(string $transactionStatus, string $fraudStatus = ''): string
    {
        $normalizedStatus = strtolower(trim($transactionStatus));
        $normalizedFraud = strtolower(trim($fraudStatus));

        return match ($normalizedStatus) {
            'settlement' => 'completed',
            'capture' => $normalizedFraud === 'challenge' ? 'pending' : 'completed',
            'deny', 'failure' => 'failed',
            'expire', 'expired', 'cancel', 'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }

    private function generateWalletTransactionRef(string $prefix, int $customerId): string
    {
        $timePart = now()->format('YmdHis');
        $randomPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $cleanPrefix = strtoupper(trim($prefix));

        return "{$cleanPrefix}-{$customerId}-{$timePart}-{$randomPart}";
    }

    private function appendWalletNote(?string $existingNotes, string $additionalNote): ?string
    {
        $base = $this->normalizeWalletTransactionNotes($existingNotes);
        $addition = trim($additionalNote);

        if ($addition === '') {
            return $base;
        }

        if ($base === null) {
            return $addition;
        }

        return $base.PHP_EOL.$addition;
    }

    private function buildWithdrawalNotes(Customer $customer, ?string $notes, float $amount): string
    {
        $bankName = trim((string) ($customer->bank_name ?? '-'));
        $bankAccount = trim((string) ($customer->bank_account ?? '-'));
        $adminFee = $this->formatRupiahAmount(self::WITHDRAWAL_ADMIN_FEE);
        $estimatedReceived = $this->formatRupiahAmount(max(0, $amount - self::WITHDRAWAL_ADMIN_FEE));
        $base = "Bank: {$bankName} ({$bankAccount})".PHP_EOL
            ."Biaya admin: {$adminFee}".PHP_EOL
            ."Estimasi diterima: {$estimatedReceived}";
        $extra = $this->normalizeWalletTransactionNotes($notes);

        return $extra !== null ? $base.PHP_EOL.$extra : $base;
    }

    private function withdrawalMinimumRequestAmount(): int
    {
        return (int) (ceil(self::WITHDRAWAL_MIN_AMOUNT / self::WITHDRAWAL_STEP_AMOUNT) * self::WITHDRAWAL_STEP_AMOUNT);
    }

    private function formatRupiahAmount(float|int $amount): string
    {
        return 'Rp '.number_format((int) round($amount), 0, ',', '.');
    }

    private function memberStatusLabel(int $status): string
    {
        return match ($status) {
            3 => 'Aktif',
            2 => 'Pasif',
            default => 'Prospek',
        };
    }

    /**
     * @param  Collection<int, ContentCategory>  $categories
     * @return list<array{
     *   id:int,
     *   parent_id:int|null,
     *   name:string,
     *   slug:string,
     *   contents_count:int
     * }>
     */
    private function formatZennerCategories(Collection $categories): array
    {
        return $categories
            ->map(fn (ContentCategory $category): array => [
                'id' => (int) $category->id,
                'parent_id' => $category->parent_id !== null ? (int) $category->parent_id : null,
                'name' => (string) $category->name,
                'slug' => (string) $category->slug,
                'contents_count' => (int) ($category->contents_count ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Content>  $contents
     * @return list<array{
     *   id:int,
     *   category_id:int|null,
     *   category_name:string|null,
     *   category_slug:string|null,
     *   title:string,
     *   slug:string,
     *   excerpt:string,
     *   content:string|null,
     *   file:string|null,
     *   vlink:string|null,
     *   status:string|null,
     *   status_label:string,
     *   created_at:string|null,
     *   updated_at:string|null
     * }>
     */
    private function formatZennerContents(Collection $contents): array
    {
        return $contents
            ->map(function (Content $content): array {
                $status = $content->status !== null ? strtolower(trim((string) $content->status)) : null;

                return [
                    'id' => (int) $content->id,
                    'category_id' => $content->category_id !== null ? (int) $content->category_id : null,
                    'category_name' => $content->category?->name,
                    'category_slug' => $content->category?->slug,
                    'title' => (string) ($content->title ?? ''),
                    'slug' => (string) ($content->slug ?? ''),
                    'excerpt' => $this->buildZennerExcerpt($content->content),
                    'content' => $content->content,
                    'file' => $content->file,
                    'vlink' => $content->vlink,
                    'status' => $status,
                    'status_label' => $this->formatZennerStatusLabel($status),
                    'created_at' => $content->created_at?->toIso8601String(),
                    'updated_at' => $content->updated_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    private function buildZennerExcerpt(?string $content): string
    {
        if (! is_string($content) || trim($content) === '') {
            return 'Konten belum memiliki deskripsi.';
        }

        $plain = strip_tags($content);
        $plain = preg_replace('/\s+/', ' ', $plain) ?? '';
        $plain = trim($plain);

        if ($plain === '') {
            return 'Konten belum memiliki deskripsi.';
        }

        return mb_strlen($plain) > 180
            ? mb_substr($plain, 0, 177).'...'
            : $plain;
    }

    private function formatZennerStatusLabel(?string $status): string
    {
        return match ($status) {
            'published', 'publish', 'active' => 'Published',
            'draft' => 'Draft',
            'archived' => 'Archived',
            default => 'Unknown',
        };
    }

    /**
     * @param  Collection<int, Promotion>  $promotions
     * @return list<array<string, mixed>>
     */
    private function formatPromotions(Collection $promotions, CarbonInterface $asOf): array
    {
        return $promotions
            ->map(function (Promotion $promotion) use ($asOf): array {
                $conditions = is_array($promotion->conditions_json) ? $promotion->conditions_json : [];

                $minSpend = $this->extractNumericCondition($conditions, ['min_spend', 'minimum_spend', 'min_purchase', 'minimum_order']);
                $maxDiscount = $this->extractNumericCondition($conditions, ['max_discount', 'maximum_discount', 'discount_cap']);
                $quotaLeft = $this->extractIntegerCondition($conditions, ['quota_left', 'remaining_quota', 'quota']);
                $terms = $this->extractPromotionTerms($conditions);

                if ($quotaLeft === null && $promotion->max_redemption !== null) {
                    $quotaLeft = (int) $promotion->max_redemption;
                }

                $discountLabel = $this->buildPromotionDiscountLabel($promotion, $conditions);
                $isRunningNow = $promotion->start_at !== null
                    && $promotion->end_at !== null
                    && $promotion->start_at <= $asOf
                    && $promotion->end_at >= $asOf;

                return [
                    'id' => $promotion->id,
                    'title' => (string) ($promotion->name ?? ''),
                    'description' => $promotion->description,
                    'code' => $promotion->code ? strtoupper((string) $promotion->code) : null,
                    'type' => $this->mapPromotionType((string) $promotion->type),
                    'discount_label' => $discountLabel,
                    'min_spend' => $minSpend,
                    'max_discount' => $maxDiscount,
                    'quota_left' => $quotaLeft,
                    'expires_at' => $promotion->end_at?->toIso8601String(),
                    'terms' => $terms,
                    'claimed' => false,
                    'to' => filled($promotion->code) ? route('shop.index', ['promo' => (string) $promotion->code]) : route('shop.index'),
                    'highlight' => $isRunningNow && (int) ($promotion->priority ?? 0) > 0,
                ];
            })
            ->values()
            ->all();
    }

    private function mapPromotionType(string $type): string
    {
        $normalized = strtolower(trim($type));

        return match ($normalized) {
            'flash_sale', 'flash' => 'flash',
            'bundle' => 'bundle',
            'shipping', 'free_shipping' => 'shipping',
            'member' => 'member',
            'voucher' => 'voucher',
            default => 'discount',
        };
    }

    /**
     * @param  array<string, mixed>  $conditions
     * @param  list<string>  $keys
     */
    private function extractNumericCondition(array $conditions, array $keys): ?float
    {
        foreach ($keys as $key) {
            $value = data_get($conditions, $key);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $conditions
     * @param  list<string>  $keys
     */
    private function extractIntegerCondition(array $conditions, array $keys): ?int
    {
        foreach ($keys as $key) {
            $value = data_get($conditions, $key);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $conditions
     * @return list<string>|null
     */
    private function extractPromotionTerms(array $conditions): ?array
    {
        $rawTerms = data_get($conditions, 'terms');

        if (is_array($rawTerms)) {
            $terms = collect($rawTerms)
                ->map(fn (mixed $item): string => trim((string) $item))
                ->filter(fn (string $item): bool => $item !== '')
                ->values()
                ->all();

            return $terms !== [] ? $terms : null;
        }

        if (is_string($rawTerms) && trim($rawTerms) !== '') {
            $terms = preg_split('/\r\n|\r|\n|;/', $rawTerms) ?: [];
            $normalized = collect($terms)
                ->map(fn (string $item): string => trim($item))
                ->filter(fn (string $item): bool => $item !== '')
                ->values()
                ->all();

            return $normalized !== [] ? $normalized : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $conditions
     */
    private function buildPromotionDiscountLabel(Promotion $promotion, array $conditions): ?string
    {
        $discountPercent = $this->extractNumericCondition($conditions, ['discount_percent', 'percent', 'discount_percentage']);
        $discountValue = $this->extractNumericCondition($conditions, ['discount_value', 'discount_amount', 'nominal_discount', 'value']);
        $bundlePrice = $this->extractNumericCondition($conditions, ['bundle_price']);

        if ($discountPercent !== null && $discountPercent > 0) {
            $formatted = rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.');

            return "Diskon {$formatted}%";
        }

        if ($discountValue !== null && $discountValue > 0) {
            return 'Potongan Rp '.number_format((int) round($discountValue), 0, ',', '.');
        }

        if ($bundlePrice !== null && $bundlePrice > 0) {
            return 'Harga Bundle Rp '.number_format((int) round($bundlePrice), 0, ',', '.');
        }

        return match ($this->mapPromotionType((string) $promotion->type)) {
            'bundle' => 'Promo Bundle',
            'flash' => 'Flash Deal',
            'shipping' => 'Gratis Ongkir',
            'voucher' => 'Voucher Promo',
            'member' => 'Khusus Member',
            default => null,
        };
    }

    /**
     * @return array{
     *   tree:array<string,mixed>|null,
     *   stats:array{
     *     left_count:int,
     *     right_count:int,
     *     total_downlines:int
     *   }
     * }
     */
    private function formatBinaryTreeData(Customer $customer, int $maxDepth = 5): array
    {
        $leftCount = 0;
        $rightCount = 0;
        $totalDownlines = $leftCount + $rightCount;
        $members = $this->dashboardRepository->getBinaryTreeMembers($customer->id, $maxDepth);

        if ($members->isEmpty()) {
            return [
                'tree' => null,
                'stats' => [
                    'left_count' => $leftCount,
                    'right_count' => $rightCount,
                    'total_downlines' => $totalDownlines,
                ],
            ];
        }

        /** @var Collection<int, Customer> $membersById */
        $membersById = $members
            ->filter(fn (mixed $member): bool => $member instanceof Customer)
            ->keyBy(fn (Customer $member): int => (int) $member->id);

        if (! $membersById->has((int) $customer->id)) {
            $membersById->put((int) $customer->id, $customer);
        }

        $visited = [];
        $tree = $this->buildBinaryTreeNode(
            (int) $customer->id,
            1,
            max(1, $maxDepth),
            $membersById,
            $visited,
            true,
        );

        return [
            'tree' => $tree,
            'stats' => [
                'left_count' => $leftCount,
                'right_count' => $rightCount,
                'total_downlines' => $totalDownlines,
            ],
        ];
    }

    /**
     * @param  Collection<int, Customer>  $membersById
     * @param  array<int, bool>  $visited
     * @return array<string,mixed>|null
     */
    private function buildBinaryTreeNode(
        int $memberId,
        int $level,
        int $maxDepth,
        Collection $membersById,
        array &$visited,
        bool $isRoot = false,
    ): ?array {
        if ($level > $maxDepth || isset($visited[$memberId])) {
            return null;
        }

        $member = $membersById->get($memberId);

        if (! $member instanceof Customer) {
            return null;
        }

        $visited[$memberId] = true;

        return [
            'id' => (int) $member->id,
            'member_id' => (int) $member->id,
            'name' => (string) ($member->name ?? ''),
            'username' => (string) ($member->username ?? ''),
            'email' => $member->email,
            'phone' => $member->phone,
            'package_name' => $member->package?->name,
            'total_left' => 0,
            'total_right' => 0,
            'position' => null,
            'level' => $level,
            'status' => (int) ($member->status ?? 0) === 3,
            'joined_at' => $member->created_at?->toIso8601String(),
            'has_children' => false,
            'left' => null,
            'right' => null,
        ];
    }

    /**
     * @param  Collection<int, Customer>  $members
     * @return array{active:list<array<string,mixed>>,passive:list<array<string,mixed>>,prospect:list<array<string,mixed>>}
     */
    private function formatMitraMembers(Collection $members): array
    {
        $activeMembers = [];
        $passiveMembers = [];
        $prospectMembers = [];

        foreach ($members as $member) {
            $memberStatus = (int) ($member->status ?? 1);
            $hasPurchase = ((int) ($member->orders_count ?? 0)) > 0;

            $data = [
                'id' => $member->id,
                'username' => (string) ($member->username ?? ''),
                'name' => (string) ($member->name ?? ''),
                'email' => (string) ($member->email ?? ''),
                'phone' => $member->phone,
                'package_name' => $member->package?->name,
                'total_left' => 0,
                'total_right' => 0,
                'position' => null,
                'level' => $member->level,
                'has_placement' => $member->networkMatrixes()->exists(),
                'has_purchase' => $hasPurchase,
                'omzet' => (float) ($member->omzet ?? 0),
                'joined_at' => $member->created_at?->toIso8601String(),
                'status' => $memberStatus,
                'status_label' => $this->memberStatusLabel($memberStatus),
            ];

            if ($memberStatus === 3) {
                $activeMembers[] = $data;

                continue;
            }

            if ($memberStatus === 2) {
                $passiveMembers[] = $data;

                continue;
            }

            if ($memberStatus === 1) {
                $prospectMembers[] = $data;
            }
        }

        return [
            'active' => $activeMembers,
            'passive' => $passiveMembers,
            'prospect' => $prospectMembers,
        ];
    }

    /**
     * @param  list<int>  $availableLevels
     * @param  array{level:int|null}  $filters
     * @return array{
     *   data:list<array<string, mixed>>,
     *   current_page:int,
     *   per_page:int,
     *   total:int,
     *   last_page:int,
     *   from:int|null,
     *   to:int|null,
     *   filters:array{level:int|null},
     *   available_generations:list<array{value:int,label:string}>
     * }
     */
    private function formatGenerationNetworkPagination(
        LengthAwarePaginator $paginator,
        array $availableLevels,
        array $filters = [],
    ): array {
        $rows = collect($paginator->items())
            ->filter(fn (mixed $item): bool => $item instanceof CustomerNetworkMatrix)
            ->map(fn (CustomerNetworkMatrix $matrix): array => $this->formatGenerationNetworkMember($matrix))
            ->values()
            ->all();

        return [
            'data' => $rows,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'filters' => $this->normalizeGenerationFilters($filters),
            'available_generations' => collect($availableLevels)
                ->map(static fn (int $level): array => [
                    'value' => $level,
                    'label' => 'Generasi '.$level,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{
     *   id:int,
     *   member_id:int|null,
     *   generation:int,
     *   username:string,
     *   name:string,
     *   email:string,
     *   phone:?string,
     *   package_name:?string,
     *   member_level:string|null,
     *   omzet_group:float,
     *   status:int,
     *   status_label:string,
     *   joined_at:?string
     * }
     */
    private function formatGenerationNetworkMember(CustomerNetworkMatrix $matrix): array
    {
        $member = $matrix->member;
        $memberStatus = (int) ($member?->status ?? 1);
        $memberId = $member?->id !== null ? (int) $member->id : null;

        return [
            'id' => $memberId ?? (int) $matrix->id,
            'member_id' => $memberId,
            'generation' => max(1, (int) ($matrix->level ?? 1)),
            'username' => (string) ($member?->username ?? ''),
            'name' => (string) ($member?->name ?? ''),
            'email' => (string) ($member?->email ?? ''),
            'phone' => $member?->phone,
            'package_name' => $member?->package?->name,
            'member_level' => $member?->level,
            'omzet_group' => (float) ($member?->omzet_group ?? 0),
            'status' => $memberStatus,
            'status_label' => $this->memberStatusLabel($memberStatus),
            'joined_at' => $member?->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{
     *   data:list<array<string,mixed>>,
     *   current_page:int,
     *   next_page:int|null,
     *   has_more:bool,
     *   per_page:int,
     *   total:int,
     *   filters:array{
     *     q:string|null,
     *     status:string,
     *     sort:string,
     *     date_from:string|null,
     *     date_to:string|null
     *   },
     *   pending_review_count:int,
     *   has_pending_review:bool
     * }
     */
    private function formatOrders(LengthAwarePaginator $paginator, int $pendingReviewCount = 0, array $filters = []): array
    {
        $orders = collect($paginator->items())
            ->filter(fn (mixed $item): bool => $item instanceof Order)
            ->map(fn (Order $order): array => $this->formatOrder($order))
            ->values()
            ->all();

        return [
            'data' => $orders,
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'has_more' => $paginator->hasMorePages(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'filters' => $this->normalizeOrdersFilters($filters),
            'pending_review_count' => max(0, $pendingReviewCount),
            'has_pending_review' => $pendingReviewCount > 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatOrder(Order $order): array
    {
        $latestPayment = $order->payments
            ->sortByDesc(fn ($payment): int => $payment->created_at?->getTimestamp() ?? 0)
            ->first();

        $latestShipment = $order->shipments
            ->sortByDesc(fn ($shipment): int => $shipment->created_at?->getTimestamp() ?? 0)
            ->first();

        $normalizedStatus = $this->normalizeOrderStatus((string) ($order->status ?? 'pending'));
        $isDeliveredOrder = $normalizedStatus === 'delivered'
            || strtolower(trim((string) ($latestShipment?->status ?? ''))) === 'delivered';
        $orderCustomerId = (int) ($order->customer_id ?? 0);

        $items = $order->items
            ->map(function (OrderItem $item) use ($isDeliveredOrder, $orderCustomerId): array {
                $productImage = $this->resolveOrderItemProductImagePath($item);
                $review = $item->review;
                $hasOwnedReview = $review !== null && (int) ($review->customer_id ?? 0) === $orderCustomerId;
                $canReview = $isDeliveredOrder
                    && (int) ($item->product_id ?? 0) > 0
                    && ! $hasOwnedReview;

                return [
                    'id' => $item->id,
                    'name' => (string) ($item->name ?? ''),
                    'sku' => $item->sku,
                    'variant' => data_get($item->meta_json, 'variant'),
                    'qty' => (int) ($item->qty ?? 0),
                    'price' => (float) ($item->unit_price ?? 0),
                    'row_total' => (float) ($item->row_total ?? 0),
                    'image' => $this->resolveProductImageUrl($productImage),
                    'can_review' => $canReview,
                    'is_reviewed' => $hasOwnedReview,
                    'review_id' => $hasOwnedReview ? (int) $review->id : null,
                    'review_rating' => $hasOwnedReview ? (int) ($review->rating ?? 0) : null,
                    'review_is_approved' => $hasOwnedReview ? (bool) ($review->is_approved ?? false) : null,
                    'review_created_at' => $hasOwnedReview ? $review->created_at?->toIso8601String() : null,
                ];
            })
            ->values()
            ->all();
        $pendingReviewCount = collect($items)
            ->filter(fn (array $item): bool => (bool) ($item['can_review'] ?? false))
            ->count();

        return [
            'id' => $order->id,
            'code' => (string) ($order->order_no ?? $order->id),
            'created_at' => $order->placed_at?->toIso8601String() ?? $order->created_at?->toIso8601String() ?? '-',
            'status' => $normalizedStatus,
            'payment_status' => $this->normalizePaymentStatus((string) ($latestPayment?->status ?? '')),
            'payment_method' => $latestPayment?->method?->name ?? $latestPayment?->method?->code,
            'payment_method_code' => $latestPayment?->method?->code,
            'shipping_method' => filled($latestShipment?->courier_id) ? strtoupper((string) $latestShipment?->courier_id) : null,
            'subtotal' => (float) ($order->subtotal_amount ?? 0),
            'discount_amount' => (float) ($order->discount_amount ?? 0),
            'shipping_cost' => (float) ($order->shipping_amount ?? 0),
            'tax_amount' => (float) ($order->tax_amount ?? 0),
            'total' => (float) ($order->grand_total ?? 0),
            'items_count' => (int) ($order->items_count ?? $order->items->count()),
            'items' => $items,
            'items_preview' => collect($items)->take(3)->values()->all(),
            'pending_review_count' => $pendingReviewCount,
            'has_pending_review' => $pendingReviewCount > 0,
            'tracking_number' => $latestShipment?->tracking_no,
            'notes' => $order->notes,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'shipping_address' => $order->shippingAddress ? [
                'recipient_name' => $order->shippingAddress->recipient_name,
                'recipient_phone' => $order->shippingAddress->recipient_phone,
                'address_line1' => $order->shippingAddress->address_line1,
                'address_line2' => $order->shippingAddress->address_line2,
                'district' => $order->shippingAddress->district,
                'city' => $order->shippingAddress->city_label,
                'province' => $order->shippingAddress->province_label,
                'postal_code' => $order->shippingAddress->postal_code,
                'country' => $order->shippingAddress->country,
            ] : null,
            'customer' => [
                'name' => (string) ($order->customer?->name ?? '-'),
                'email' => $order->customer?->email,
            ],
            'to' => null,
        ];
    }

    private function resolveOrderItemProductImagePath(OrderItem $item): ?string
    {
        $primaryMediaPaths = $item->product?->primaryMedia
            ?->sortBy('sort_order')
            ->pluck('url')
            ->all() ?? [];

        $mediaPaths = $item->product?->media
            ?->sortBy('sort_order')
            ->pluck('url')
            ->all() ?? [];

        $candidatePaths = collect([...$primaryMediaPaths, ...$mediaPaths])
            ->map(static fn (mixed $path): string => trim((string) $path))
            ->filter(static fn (string $path): bool => $path !== '')
            ->unique()
            ->values();

        if ($candidatePaths->isEmpty()) {
            return null;
        }

        $availablePath = $candidatePaths->first(fn (string $path): bool => $this->isProductMediaPathReadable($path));

        if (is_string($availablePath) && $availablePath !== '') {
            return $availablePath;
        }

        $fallbackPath = $candidatePaths->first();

        return is_string($fallbackPath) && $fallbackPath !== '' ? $fallbackPath : null;
    }

    private function isProductMediaPathReadable(string $path): bool
    {
        $trimmedPath = trim($path);

        if ($trimmedPath === '') {
            return false;
        }

        if (str_starts_with($trimmedPath, 'http://') || str_starts_with($trimmedPath, 'https://')) {
            $storageRelativePath = $this->extractPublicStorageRelativePath($trimmedPath);

            if ($storageRelativePath === null) {
                return true;
            }

            return Storage::disk('public')->exists($storageRelativePath);
        }

        if (str_starts_with($trimmedPath, '/')) {
            if (str_starts_with($trimmedPath, '/storage/')) {
                $storageRelativePath = $this->extractPublicStorageRelativePath($trimmedPath);

                return $storageRelativePath !== null && Storage::disk('public')->exists($storageRelativePath);
            }

            return is_file(public_path(ltrim($trimmedPath, '/')));
        }

        $storageRelativePath = $this->extractPublicStorageRelativePath($trimmedPath);

        return $storageRelativePath !== null && Storage::disk('public')->exists($storageRelativePath);
    }

    private function extractPublicStorageRelativePath(string $path): ?string
    {
        $normalizedPath = trim($path);

        if ($normalizedPath === '') {
            return null;
        }

        if (str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://')) {
            $parsedPath = parse_url($normalizedPath, PHP_URL_PATH);

            if (! is_string($parsedPath) || trim($parsedPath) === '') {
                return null;
            }

            $normalizedPath = trim($parsedPath);
        }

        if (str_starts_with($normalizedPath, '/storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('/storage/'));
        } elseif (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('storage/'));
        } elseif (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, strlen('public/'));
        } elseif (str_starts_with($normalizedPath, '/')) {
            return null;
        }

        $normalizedPath = ltrim($normalizedPath, '/');

        return $normalizedPath !== '' ? $normalizedPath : null;
    }

    private function resolveProductImageUrl(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $path = trim((string) $url);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return Storage::url($path);
    }

    private function mapMidtransPaymentStatus(string $transactionStatus, string $fraudStatus = ''): string
    {
        $normalizedStatus = strtolower(trim($transactionStatus));
        $normalizedFraud = strtolower(trim($fraudStatus));

        return match ($normalizedStatus) {
            'settlement' => 'paid',
            'capture' => $normalizedFraud === 'challenge' ? 'pending' : 'paid',
            'refund', 'partial_refund' => 'refunded',
            'deny', 'expire', 'cancel', 'cancelled', 'canceled', 'failure' => 'failed',
            default => 'pending',
        };
    }

    private function extractMidtransRedirectUrl(array $gatewayPayload): ?string
    {
        $redirectUrl = trim((string) ($gatewayPayload['redirect_url'] ?? ''));

        if ($redirectUrl !== '') {
            return $redirectUrl;
        }

        $actions = $gatewayPayload['actions'] ?? null;

        if (! is_array($actions)) {
            return null;
        }

        foreach ($actions as $action) {
            $url = trim((string) data_get($action, 'url'));

            if ($url !== '') {
                return $url;
            }
        }

        return null;
    }

    private function normalizeOrderStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'paid' => 'paid',
            'processing', 'processed' => 'processing',
            'shipped', 'ready_to_ship' => 'shipped',
            'delivered' => 'delivered',
            'cancelled', 'canceled' => 'cancelled',
            'refunded' => 'refunded',
            default => 'pending',
        };
    }

    private function normalizePaymentStatus(string $status): ?string
    {
        $normalized = strtolower(trim($status));

        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            'paid', 'settlement', 'capture', 'completed', 'success', 'succeeded' => 'paid',
            'refunded', 'refund' => 'refunded',
            'failed', 'deny', 'expire', 'cancel', 'cancelled', 'canceled' => 'failed',
            default => 'unpaid',
        };
    }
}
