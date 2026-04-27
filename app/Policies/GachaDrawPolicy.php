<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GachaDraw;
use Illuminate\Auth\Access\HandlesAuthorization;

class GachaDrawPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GachaDraw');
    }

    public function view(AuthUser $authUser, GachaDraw $gachaDraw): bool
    {
        return $authUser->can('View:GachaDraw');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GachaDraw');
    }

    public function update(AuthUser $authUser, GachaDraw $gachaDraw): bool
    {
        return $authUser->can('Update:GachaDraw');
    }

    public function delete(AuthUser $authUser, GachaDraw $gachaDraw): bool
    {
        return $authUser->can('Delete:GachaDraw');
    }

    public function restore(AuthUser $authUser, GachaDraw $gachaDraw): bool
    {
        return $authUser->can('Restore:GachaDraw');
    }

    public function forceDelete(AuthUser $authUser, GachaDraw $gachaDraw): bool
    {
        return $authUser->can('ForceDelete:GachaDraw');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GachaDraw');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GachaDraw');
    }

    public function replicate(AuthUser $authUser, GachaDraw $gachaDraw): bool
    {
        return $authUser->can('Replicate:GachaDraw');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GachaDraw');
    }

}