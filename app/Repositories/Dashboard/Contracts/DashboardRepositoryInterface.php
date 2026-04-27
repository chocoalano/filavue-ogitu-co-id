<?php

namespace App\Repositories\Dashboard\Contracts;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerBonusCashback;
use App\Models\CustomerBonusReward;
use App\Models\CustomerBonusRoyalty;
use App\Models\CustomerBonusSponsor;
use App\Models\CustomerWalletTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Reward;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use stdClass;

interface DashboardRepositoryInterface
{
    public function findCustomerById(int $customerId): ?Customer;

    public function countCustomersByPhone(string $phone, ?int $exceptCustomerId = null): int;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateCustomerAccount(Customer $customer, array $attributes): void;

    /**
     * @param array{
     *   nama:?string,
     *   npwp:?string,
     *   jk:?int,
     *   npwp_date:?string,
     *   alamat:?string,
     *   menikah:?string,
     *   anak:?string,
     *   kerja:?string,
     *   office:?string
     * } $attributes
     */
    public function upsertCustomerNpwp(int $customerId, array $attributes): void;

    public function getDefaultAddress(int $customerId): ?CustomerAddress;

    public function countOrders(int $customerId): int;

    /**
     * @param  list<string>  $pendingStatuses
     */
    public function countPendingOrders(int $customerId, array $pendingStatuses): int;

    /**
     * @param array{
     *   q?:string|null,
     *   status?:string|null,
     *   sort?:string|null,
     *   date_from?:string|null,
     *   date_to?:string|null
     * } $filters
     */
    public function getPaginatedOrders(
        int $customerId,
        int $perPage = 10,
        int $page = 1,
        array $filters = [],
    ): LengthAwarePaginator;

    public function findOrderForCustomer(int $customerId, int $orderId): ?Order;

    public function countPendingReviewItems(int $customerId): int;

    /**
     * @param  array<string, mixed>  $gatewayPayload
     */
    public function updatePaymentFromGateway(Payment $payment, string $status, array $gatewayPayload): void;

    /**
     * @param  array<string, mixed>  $rawPayload
     */
    public function createPaymentTransaction(Payment $payment, string $status, float $amount, array $rawPayload): void;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function updatePaymentMetadata(Payment $payment, array $metadata): void;

    public function markOrderAsPaid(Order $order): void;

    public function markOrderBonusGenerated(Order $order): bool;

    public function callBonusEngine(int $orderId): void;

    public function getLatestOrderTimestamp(int $customerId): ?CarbonInterface;

    public function countActiveNetworkMembers(int $customerId): int;

    public function getMaxNetworkLevel(int $customerId): int;

    public function sumAvailableBonuses(int $customerId): float;

    public function sumMonthlyBonuses(int $customerId, int $year, int $month): float;

    public function sumLifetimeBonuses(int $customerId): float;

    /**
     * @return array{
     *   referral_incentive:array{amount:float,count:int},
     *   team_affiliate_commission:array{amount:float,count:int},
     *   partner_team_commission:array{amount:float,count:int},
     *   cashback_commission:array{amount:float,count:int},
     *   promotions_rewards:array{amount:float,count:int},
     *   retail_commission:array{amount:float,count:int},
     *   lifetime_cash_rewards:array{amount:float,count:int},
     *   total_bonus:array{amount:float,count:int}
     * }
     */
    public function getBonusStats(int $customerId): array;

    /**
     * @return Collection<int, CustomerBonusSponsor>
     */
    public function getBonusSponsors(int $customerId, int $limit = 50): Collection;

    /**
     * @return Collection<int, object>
     */
    public function getBonusMatchings(int $customerId, int $limit = 50): Collection;

    /**
     * @return Collection<int, object>
     */
    public function getBonusPairings(int $customerId, int $limit = 50): Collection;

    /**
     * @return Collection<int, CustomerBonusCashback>
     */
    public function getBonusCashbacks(int $customerId, int $limit = 50): Collection;

    /**
     * @return Collection<int, CustomerBonusReward>
     */
    public function getPromotionRewards(int $customerId, int $limit = 50): Collection;

    /**
     * @return Collection<int, CustomerBonusRoyalty>
     */
    public function getBonusRetails(int $customerId, int $limit = 50): Collection;

    /**
     * @return Collection<int, CustomerBonusReward>
     */
    public function getBonusLifetimeCashRewards(int $customerId, int $limit = 50): Collection;

    /**
     * @return Collection<int, Reward>
     */
    public function getActiveLifetimeRewards(int $limit = 100): Collection;

    /**
     * @return Collection<int, string>
     */
    public function getClaimedLifetimeRewardNames(int $customerId): Collection;

    /**
     * @return Collection<int, CustomerBonusReward>
     */
    public function getClaimedLifetimeRewards(int $customerId, int $limit = 50): Collection;

    public function countActivePromotions(CarbonInterface $asOf): int;

    /**
     * @return Collection<int, Promotion>
     */
    public function getDashboardPromotions(CarbonInterface $asOf, int $limit = 50): Collection;

    /**
     * @return Collection<int, ContentCategory>
     */
    public function getZennerCategories(int $limit = 100): Collection;

    /**
     * @return Collection<int, Content>
     */
    public function getZennerContents(int $limit = 200): Collection;

    public function hasNpwp(int $customerId): bool;

    /**
     * @return Collection<int, CustomerWalletTransaction>
     */
    public function getWalletTransactions(int $customerId, int $limit = 25): Collection;

    /**
     * @param array{
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
     * } $filters
     */
    public function getPaginatedWalletTransactions(
        int $customerId,
        int $perPage = 15,
        int $page = 1,
        array $filters = [],
    ): LengthAwarePaginator;

    /**
     * @return array{
     *   topup_total:float,
     *   withdrawal_total:float,
     *   pending_count:int
     * }
     */
    public function getWalletTransactionSummary(
        int $customerId,
        CarbonInterface $from,
        CarbonInterface $to,
    ): array;

    public function hasPendingWithdrawal(int $customerId): bool;

    public function findWalletTransactionForCustomer(
        int $customerId,
        int $walletTransactionId,
        bool $lockForUpdate = false,
    ): ?CustomerWalletTransaction;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createWalletTransaction(array $attributes): CustomerWalletTransaction;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateWalletTransaction(CustomerWalletTransaction $transaction, array $attributes): void;

    public function adjustCustomerWalletBalance(Customer $customer, float $delta): void;

    /**
     * @return Collection<int, Customer>
     */
    public function getSponsoredMembers(int $customerId, int $limit = 200): Collection;

    /**
     * @param  array{level?:int|string|null}  $filters
     */
    public function getPaginatedGenerationNetworkMembers(
        int $customerId,
        int $perPage = 15,
        int $page = 1,
        array $filters = [],
    ): LengthAwarePaginator;

    /**
     * @return list<int>
     */
    public function getGenerationLevelOptions(int $customerId): array;

    /**
     * @return Collection<int, Customer>
     */
    public function getBinaryTreeMembers(int $rootCustomerId, int $maxDepth = 5): Collection;

    public function hasBinaryChildAtPosition(int $customerId, string $position): bool;

    public function isMemberInCustomerNetwork(int $uplineCustomerId, int $memberId): bool;

    public function findCustomerByIdForUpdate(int $customerId): ?Customer;

    public function updateMemberPlacement(Customer $member, int $uplineId, string $position): void;

    public function updateUplineFoot(Customer $upline, string $position, int $memberId): void;

    public function callRegistrationProcedure(int $memberId): ?stdClass;
}
