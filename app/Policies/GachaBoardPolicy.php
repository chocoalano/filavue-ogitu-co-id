<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GachaBoard;
use Illuminate\Auth\Access\HandlesAuthorization;

class GachaBoardPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GachaBoard');
    }

    public function view(AuthUser $authUser, GachaBoard $gachaBoard): bool
    {
        return $authUser->can('View:GachaBoard');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GachaBoard');
    }

    public function update(AuthUser $authUser, GachaBoard $gachaBoard): bool
    {
        return $authUser->can('Update:GachaBoard');
    }

    public function delete(AuthUser $authUser, GachaBoard $gachaBoard): bool
    {
        return $authUser->can('Delete:GachaBoard');
    }

    public function restore(AuthUser $authUser, GachaBoard $gachaBoard): bool
    {
        return $authUser->can('Restore:GachaBoard');
    }

    public function forceDelete(AuthUser $authUser, GachaBoard $gachaBoard): bool
    {
        return $authUser->can('ForceDelete:GachaBoard');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GachaBoard');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GachaBoard');
    }

    public function replicate(AuthUser $authUser, GachaBoard $gachaBoard): bool
    {
        return $authUser->can('Replicate:GachaBoard');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GachaBoard');
    }

}