<?php

namespace App\Repositories\Auth\Contracts;

use App\Models\Customer;
use Carbon\CarbonInterface;

interface CustomerProfileRepositoryInterface
{
    public function findByIdWithPackage(int $customerId): ?Customer;

    /**
     * @return array{
     *   orders_total:int,
     *   orders_processing:int,
     *   orders_completed:int,
     *   network_count:int,
     *   sponsor_count:int,
     *   mitra_prospek:int,
     *   mitra_aktif:int,
     *   mitra_pasif:int,
     *   bonus_total:float,
     *   bonus_sponsor:float,
     *   bonus_matching:float,
     *   bonus_pairing:float,
     *   bonus_cashback:float,
     *   bonus_rewards:float,
     *   bonus_retail:float,
     *   bonus_lifetime_cash:float,
     *   promo_active_count:int,
     *   wallet_reward_points:float,
     *   wallet_has_activity:bool
     * }
     */
    public function getProfileMetrics(int $customerId, CarbonInterface $asOf): array;
}
