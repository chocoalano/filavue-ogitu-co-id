<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WhatsAppBroadcast;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsAppBroadcastPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WhatsAppBroadcast');
    }

    public function view(AuthUser $authUser, WhatsAppBroadcast $whatsAppBroadcast): bool
    {
        return $authUser->can('View:WhatsAppBroadcast');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WhatsAppBroadcast');
    }

    public function update(AuthUser $authUser, WhatsAppBroadcast $whatsAppBroadcast): bool
    {
        return $authUser->can('Update:WhatsAppBroadcast');
    }

    public function delete(AuthUser $authUser, WhatsAppBroadcast $whatsAppBroadcast): bool
    {
        return $authUser->can('Delete:WhatsAppBroadcast');
    }

    public function restore(AuthUser $authUser, WhatsAppBroadcast $whatsAppBroadcast): bool
    {
        return $authUser->can('Restore:WhatsAppBroadcast');
    }

    public function forceDelete(AuthUser $authUser, WhatsAppBroadcast $whatsAppBroadcast): bool
    {
        return $authUser->can('ForceDelete:WhatsAppBroadcast');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WhatsAppBroadcast');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WhatsAppBroadcast');
    }

    public function replicate(AuthUser $authUser, WhatsAppBroadcast $whatsAppBroadcast): bool
    {
        return $authUser->can('Replicate:WhatsAppBroadcast');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WhatsAppBroadcast');
    }

}