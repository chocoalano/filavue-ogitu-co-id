<?php

namespace App\Repositories\Auth;

use App\Models\Customer;
use App\Models\CustomerBonus;
use App\Models\CustomerBonusCashback;
use App\Models\CustomerBonusReward;
use App\Models\CustomerBonusRoyalty;
use App\Models\CustomerBonusSponsor;
use App\Models\CustomerNetworkMatrix;
use App\Models\CustomerWalletTransaction;
use App\Models\Order;
use App\Models\Promotion;
use App\Repositories\Auth\Contracts\CustomerProfileRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class CustomerProfileRepository implements CustomerProfileRepositoryInterface
{
    public function findByIdWithPackage(int $customerId): ?Customer
    {
        return Customer::query()
            ->with([
                'package:id,name',
                'npwp:id,member_id,nama,npwp,jk,npwp_date,alamat,menikah,anak,kerja,office',
            ])
            ->find($customerId);
    }

    public function getProfileMetrics(int $customerId, CarbonInterface $asOf): array
    {
        $sponsoredMembers = Customer::query()->where('sponsor_id', $customerId);

        $bonusTotal = (float) CustomerBonus::query()
            ->where('member_id', $customerId)
            ->sum('tax_netto');

        return [
            'orders_total' => Order::query()
                ->where('customer_id', $customerId)
                ->count(),
            'orders_processing' => $this->countOrdersByStatuses($customerId, ['processing']),
            'orders_completed' => $this->countOrdersByStatuses($customerId, ['completed', 'delivered']),
            'network_count' => CustomerNetworkMatrix::query()
                ->where('sponsor_id', $customerId)
                ->count(),
            'sponsor_count' => (clone $sponsoredMembers)->count(),
            'mitra_prospek' => (clone $sponsoredMembers)
                ->where('status', 1)
                ->count(),
            'mitra_aktif' => (clone $sponsoredMembers)
                ->where('status', 3)
                ->count(),
            'mitra_pasif' => (clone $sponsoredMembers)
                ->where('status', 2)
                ->count(),
            'bonus_total' => $bonusTotal,
            'bonus_sponsor' => (float) CustomerBonusSponsor::query()
                ->where('member_id', $customerId)
                ->sum('amount'),
            'bonus_matching' => 0.0,
            'bonus_pairing' => 0.0,
            'bonus_cashback' => (float) CustomerBonusCashback::query()
                ->where('member_id', $customerId)
                ->sum('amount'),
            'bonus_rewards' => (float) CustomerBonusReward::query()
                ->where('member_id', $customerId)
                ->sum('amount'),
            'bonus_retail' => (float) CustomerBonusRoyalty::query()
                ->where('member_id', $customerId)
                ->sum('amount'),
            'bonus_lifetime_cash' => (float) CustomerBonusReward::query()
                ->where('member_id', $customerId)
                ->whereRaw('LOWER(reward_type) = ?', ['lifetime'])
                ->sum('amount'),
            'promo_active_count' => Promotion::query()
                ->where('is_active', true)
                ->where(function (Builder $query) use ($asOf): void {
                    $query->whereNull('start_at')
                        ->orWhere('start_at', '<=', $asOf);
                })
                ->where(function (Builder $query) use ($asOf): void {
                    $query->whereNull('end_at')
                        ->orWhere('end_at', '>=', $asOf);
                })
                ->count(),
            'wallet_reward_points' => (float) CustomerBonusReward::query()
                ->where('member_id', $customerId)
                ->sum('index_value'),
            'wallet_has_activity' => CustomerWalletTransaction::query()
                ->where('customer_id', $customerId)
                ->exists(),
        ];
    }

    /** @param list<string> $statuses */
    private function countOrdersByStatuses(int $customerId, array $statuses): int
    {
        return Order::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', $this->expandStatusVariants($statuses))
            ->count();
    }

    /**
     * @param  list<string>  $statuses
     * @return list<string>
     */
    private function expandStatusVariants(array $statuses): array
    {
        return collect($statuses)
            ->map(fn (string $status): string => trim($status))
            ->filter(fn (string $status): bool => $status !== '')
            ->flatMap(fn (string $status): array => [
                $status,
                strtolower($status),
                strtoupper($status),
                ucfirst(strtolower($status)),
            ])
            ->unique()
            ->values()
            ->all();
    }
}
