<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GachaCampaign;
use Illuminate\Auth\Access\HandlesAuthorization;

class GachaCampaignPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GachaCampaign');
    }

    public function view(AuthUser $authUser, GachaCampaign $gachaCampaign): bool
    {
        return $authUser->can('View:GachaCampaign');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GachaCampaign');
    }

    public function update(AuthUser $authUser, GachaCampaign $gachaCampaign): bool
    {
        return $authUser->can('Update:GachaCampaign');
    }

    public function delete(AuthUser $authUser, GachaCampaign $gachaCampaign): bool
    {
        return $authUser->can('Delete:GachaCampaign');
    }

    public function restore(AuthUser $authUser, GachaCampaign $gachaCampaign): bool
    {
        return $authUser->can('Restore:GachaCampaign');
    }

    public function forceDelete(AuthUser $authUser, GachaCampaign $gachaCampaign): bool
    {
        return $authUser->can('ForceDelete:GachaCampaign');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GachaCampaign');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GachaCampaign');
    }

    public function replicate(AuthUser $authUser, GachaCampaign $gachaCampaign): bool
    {
        return $authUser->can('Replicate:GachaCampaign');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GachaCampaign');
    }

}