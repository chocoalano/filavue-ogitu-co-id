<?php

namespace App\Services\Auth;

use App\Http\Requests\Dashboard\UpdateAccountProfileRequest;
use App\Models\Customer;
use App\Repositories\Auth\Contracts\CustomerProfileRepositoryInterface;
use Illuminate\Support\Facades\Validator;

class CustomerProfileService
{
    public function __construct(
        private readonly CustomerProfileRepositoryInterface $repository,
    ) {}

    /** @return array<string, mixed> */
    public function getApiProfile(Customer $authenticatedCustomer): array
    {
        $customer = $this->repository->findByIdWithPackage((int) $authenticatedCustomer->id) ?? $authenticatedCustomer;
        $metrics = $this->repository->getProfileMetrics((int) $customer->id, now());

        $walletBalance = (float) ($customer->ewallet_saldo ?? 0);

        return [
            'id' => (int) $customer->id,
            'name' => $customer->name,
            'username' => $customer->username,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'status' => (int) ($customer->status ?? 0),
            'member_package' => $customer->package?->name,
            'referral_code' => $customer->ref_code,
            'account_compleated' => $this->isAccountCompleted($customer),
            'summary' => [
                'total_bonus' => $metrics['bonus_total'],
                'network_count' => $metrics['network_count'],
                'sponsor_count' => $metrics['sponsor_count'],
            ],
            'orders' => [
                'total' => $metrics['orders_total'],
                'processing' => $metrics['orders_processing'],
                'completed' => $metrics['orders_completed'],
            ],
            'mitra' => [
                'prospek' => $metrics['mitra_prospek'],
                'aktif' => $metrics['mitra_aktif'],
                'pasif' => $metrics['mitra_pasif'],
            ],
            'network_binary' => [
                'bonus' => $metrics['bonus_total'],
                'sponsor' => $metrics['bonus_sponsor'],
                'matching' => $metrics['bonus_matching'],
                'pairing' => $metrics['bonus_pairing'],
                'cashback' => $metrics['bonus_cashback'],
                'rewards' => $metrics['bonus_rewards'],
                'retail' => $metrics['bonus_retail'],
                'lifetime_cash' => $metrics['bonus_lifetime_cash'],
            ],
            'promo' => [
                'active_count' => $metrics['promo_active_count'],
            ],
            'wallet' => [
                'balance' => $walletBalance,
                'reward_points' => (int) round((float) $metrics['wallet_reward_points']),
                'active' => filled($customer->ewallet_id)
                    || (bool) $metrics['wallet_has_activity']
                    || $walletBalance > 0,
            ],
        ];
    }

    private function isAccountCompleted(Customer $customer): bool
    {
        $customer->loadMissing('npwp');

        $normalizedInput = UpdateAccountProfileRequest::normalizeForValidation([
            'username' => $customer->username,
            'name' => $customer->name,
            'nik' => $customer->nik,
            'gender' => $customer->gender,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'bank_name' => $customer->bank_name,
            'bank_account' => $customer->bank_account,
            'npwp_nama' => $customer->npwp?->nama,
            'npwp_number' => $customer->npwp?->npwp,
            'npwp_jk' => $customer->npwp?->jk,
            'npwp_date' => $customer->npwp?->npwp_date?->toDateString(),
            'npwp_alamat' => $customer->npwp?->alamat,
            'npwp_menikah' => $customer->npwp?->menikah,
            'npwp_anak' => $customer->npwp?->anak,
            'npwp_kerja' => $customer->npwp?->kerja,
            'npwp_office' => $customer->npwp?->office,
        ]);

        /** @var list<string> $requiredProfileFields */
        $requiredProfileFields = [
            'username',
            'name',
            'nik',
            'gender',
            'email',
            'phone',
            'bank_name',
            'bank_account',
            'npwp_nama',
            'npwp_number',
            'npwp_jk',
            'npwp_date',
            'npwp_alamat',
            'npwp_menikah',
            'npwp_anak',
            'npwp_kerja',
            'npwp_office',
        ];

        foreach ($requiredProfileFields as $field) {
            if (! $this->isFieldFilled($normalizedInput[$field] ?? null)) {
                return false;
            }
        }

        return Validator::make(
            $normalizedInput,
            UpdateAccountProfileRequest::profileRules((int) $customer->id, $customer->phone, false)
        )->passes();
    }

    private function isFieldFilled(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }

        return $value !== null;
    }
}
