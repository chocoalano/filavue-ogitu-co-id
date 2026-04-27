<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerWalletTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerWalletTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerWalletTransaction');
    }

    public function view(AuthUser $authUser, CustomerWalletTransaction $customerWalletTransaction): bool
    {
        return $authUser->can('View:CustomerWalletTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerWalletTransaction');
    }

    public function update(AuthUser $authUser, CustomerWalletTransaction $customerWalletTransaction): bool
    {
        return $authUser->can('Update:CustomerWalletTransaction');
    }

    public function delete(AuthUser $authUser, CustomerWalletTransaction $customerWalletTransaction): bool
    {
        return $authUser->can('Delete:CustomerWalletTransaction');
    }

    public function restore(AuthUser $authUser, CustomerWalletTransaction $customerWalletTransaction): bool
    {
        return $authUser->can('Restore:CustomerWalletTransaction');
    }

    public function forceDelete(AuthUser $authUser, CustomerWalletTransaction $customerWalletTransaction): bool
    {
        return $authUser->can('ForceDelete:CustomerWalletTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerWalletTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerWalletTransaction');
    }

    public function replicate(AuthUser $authUser, CustomerWalletTransaction $customerWalletTransaction): bool
    {
        return $authUser->can('Replicate:CustomerWalletTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerWalletTransaction');
    }

}