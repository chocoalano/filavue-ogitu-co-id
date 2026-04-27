<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ReturnOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReturnOrderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ReturnOrder');
    }

    public function view(AuthUser $authUser, ReturnOrder $returnOrder): bool
    {
        return $authUser->can('View:ReturnOrder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ReturnOrder');
    }

    public function update(AuthUser $authUser, ReturnOrder $returnOrder): bool
    {
        return $authUser->can('Update:ReturnOrder');
    }

    public function delete(AuthUser $authUser, ReturnOrder $returnOrder): bool
    {
        return $authUser->can('Delete:ReturnOrder');
    }

    public function restore(AuthUser $authUser, ReturnOrder $returnOrder): bool
    {
        return $authUser->can('Restore:ReturnOrder');
    }

    public function forceDelete(AuthUser $authUser, ReturnOrder $returnOrder): bool
    {
        return $authUser->can('ForceDelete:ReturnOrder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ReturnOrder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ReturnOrder');
    }

    public function replicate(AuthUser $authUser, ReturnOrder $returnOrder): bool
    {
        return $authUser->can('Replicate:ReturnOrder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ReturnOrder');
    }

}