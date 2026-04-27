<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerBonusReward;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerBonusRewardPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerBonusReward');
    }

    public function view(AuthUser $authUser, CustomerBonusReward $customerBonusReward): bool
    {
        return $authUser->can('View:CustomerBonusReward');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerBonusReward');
    }

    public function update(AuthUser $authUser, CustomerBonusReward $customerBonusReward): bool
    {
        return $authUser->can('Update:CustomerBonusReward');
    }

    public function delete(AuthUser $authUser, CustomerBonusReward $customerBonusReward): bool
    {
        return $authUser->can('Delete:CustomerBonusReward');
    }

    public function restore(AuthUser $authUser, CustomerBonusReward $customerBonusReward): bool
    {
        return $authUser->can('Restore:CustomerBonusReward');
    }

    public function forceDelete(AuthUser $authUser, CustomerBonusReward $customerBonusReward): bool
    {
        return $authUser->can('ForceDelete:CustomerBonusReward');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerBonusReward');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerBonusReward');
    }

    public function replicate(AuthUser $authUser, CustomerBonusReward $customerBonusReward): bool
    {
        return $authUser->can('Replicate:CustomerBonusReward');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerBonusReward');
    }

}