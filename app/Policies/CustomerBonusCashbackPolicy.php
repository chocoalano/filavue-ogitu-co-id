<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerBonusCashback;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerBonusCashbackPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerBonusCashback');
    }

    public function view(AuthUser $authUser, CustomerBonusCashback $customerBonusCashback): bool
    {
        return $authUser->can('View:CustomerBonusCashback');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerBonusCashback');
    }

    public function update(AuthUser $authUser, CustomerBonusCashback $customerBonusCashback): bool
    {
        return $authUser->can('Update:CustomerBonusCashback');
    }

    public function delete(AuthUser $authUser, CustomerBonusCashback $customerBonusCashback): bool
    {
        return $authUser->can('Delete:CustomerBonusCashback');
    }

    public function restore(AuthUser $authUser, CustomerBonusCashback $customerBonusCashback): bool
    {
        return $authUser->can('Restore:CustomerBonusCashback');
    }

    public function forceDelete(AuthUser $authUser, CustomerBonusCashback $customerBonusCashback): bool
    {
        return $authUser->can('ForceDelete:CustomerBonusCashback');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerBonusCashback');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerBonusCashback');
    }

    public function replicate(AuthUser $authUser, CustomerBonusCashback $customerBonusCashback): bool
    {
        return $authUser->can('Replicate:CustomerBonusCashback');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerBonusCashback');
    }

}