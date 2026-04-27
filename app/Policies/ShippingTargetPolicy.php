<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ShippingTarget;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShippingTargetPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ShippingTarget');
    }

    public function view(AuthUser $authUser, ShippingTarget $shippingTarget): bool
    {
        return $authUser->can('View:ShippingTarget');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ShippingTarget');
    }

    public function update(AuthUser $authUser, ShippingTarget $shippingTarget): bool
    {
        return $authUser->can('Update:ShippingTarget');
    }

    public function delete(AuthUser $authUser, ShippingTarget $shippingTarget): bool
    {
        return $authUser->can('Delete:ShippingTarget');
    }

    public function restore(AuthUser $authUser, ShippingTarget $shippingTarget): bool
    {
        return $authUser->can('Restore:ShippingTarget');
    }

    public function forceDelete(AuthUser $authUser, ShippingTarget $shippingTarget): bool
    {
        return $authUser->can('ForceDelete:ShippingTarget');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ShippingTarget');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ShippingTarget');
    }

    public function replicate(AuthUser $authUser, ShippingTarget $shippingTarget): bool
    {
        return $authUser->can('Replicate:ShippingTarget');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ShippingTarget');
    }

}