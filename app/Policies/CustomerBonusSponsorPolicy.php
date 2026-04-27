<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerBonusSponsor;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerBonusSponsorPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerBonusSponsor');
    }

    public function view(AuthUser $authUser, CustomerBonusSponsor $customerBonusSponsor): bool
    {
        return $authUser->can('View:CustomerBonusSponsor');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerBonusSponsor');
    }

    public function update(AuthUser $authUser, CustomerBonusSponsor $customerBonusSponsor): bool
    {
        return $authUser->can('Update:CustomerBonusSponsor');
    }

    public function delete(AuthUser $authUser, CustomerBonusSponsor $customerBonusSponsor): bool
    {
        return $authUser->can('Delete:CustomerBonusSponsor');
    }

    public function restore(AuthUser $authUser, CustomerBonusSponsor $customerBonusSponsor): bool
    {
        return $authUser->can('Restore:CustomerBonusSponsor');
    }

    public function forceDelete(AuthUser $authUser, CustomerBonusSponsor $customerBonusSponsor): bool
    {
        return $authUser->can('ForceDelete:CustomerBonusSponsor');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerBonusSponsor');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerBonusSponsor');
    }

    public function replicate(AuthUser $authUser, CustomerBonusSponsor $customerBonusSponsor): bool
    {
        return $authUser->can('Replicate:CustomerBonusSponsor');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerBonusSponsor');
    }

}