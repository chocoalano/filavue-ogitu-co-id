<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CommodityCode;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommodityCodePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CommodityCode');
    }

    public function view(AuthUser $authUser, CommodityCode $commodityCode): bool
    {
        return $authUser->can('View:CommodityCode');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CommodityCode');
    }

    public function update(AuthUser $authUser, CommodityCode $commodityCode): bool
    {
        return $authUser->can('Update:CommodityCode');
    }

    public function delete(AuthUser $authUser, CommodityCode $commodityCode): bool
    {
        return $authUser->can('Delete:CommodityCode');
    }

    public function restore(AuthUser $authUser, CommodityCode $commodityCode): bool
    {
        return $authUser->can('Restore:CommodityCode');
    }

    public function forceDelete(AuthUser $authUser, CommodityCode $commodityCode): bool
    {
        return $authUser->can('ForceDelete:CommodityCode');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CommodityCode');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CommodityCode');
    }

    public function replicate(AuthUser $authUser, CommodityCode $commodityCode): bool
    {
        return $authUser->can('Replicate:CommodityCode');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CommodityCode');
    }

}