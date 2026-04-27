<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerNetworkMatrix;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerNetworkMatrixPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerNetworkMatrix');
    }

    public function view(AuthUser $authUser, CustomerNetworkMatrix $customerNetworkMatrix): bool
    {
        return $authUser->can('View:CustomerNetworkMatrix');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerNetworkMatrix');
    }

    public function update(AuthUser $authUser, CustomerNetworkMatrix $customerNetworkMatrix): bool
    {
        return $authUser->can('Update:CustomerNetworkMatrix');
    }

    public function delete(AuthUser $authUser, CustomerNetworkMatrix $customerNetworkMatrix): bool
    {
        return $authUser->can('Delete:CustomerNetworkMatrix');
    }

    public function restore(AuthUser $authUser, CustomerNetworkMatrix $customerNetworkMatrix): bool
    {
        return $authUser->can('Restore:CustomerNetworkMatrix');
    }

    public function forceDelete(AuthUser $authUser, CustomerNetworkMatrix $customerNetworkMatrix): bool
    {
        return $authUser->can('ForceDelete:CustomerNetworkMatrix');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerNetworkMatrix');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerNetworkMatrix');
    }

    public function replicate(AuthUser $authUser, CustomerNetworkMatrix $customerNetworkMatrix): bool
    {
        return $authUser->can('Replicate:CustomerNetworkMatrix');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerNetworkMatrix');
    }

}