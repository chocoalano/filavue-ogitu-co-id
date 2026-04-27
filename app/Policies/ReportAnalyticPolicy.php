<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ReportAnalytic;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportAnalyticPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ReportAnalytic');
    }

    public function view(AuthUser $authUser, ReportAnalytic $reportAnalytic): bool
    {
        return $authUser->can('View:ReportAnalytic');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ReportAnalytic');
    }

    public function update(AuthUser $authUser, ReportAnalytic $reportAnalytic): bool
    {
        return $authUser->can('Update:ReportAnalytic');
    }

    public function delete(AuthUser $authUser, ReportAnalytic $reportAnalytic): bool
    {
        return $authUser->can('Delete:ReportAnalytic');
    }

    public function restore(AuthUser $authUser, ReportAnalytic $reportAnalytic): bool
    {
        return $authUser->can('Restore:ReportAnalytic');
    }

    public function forceDelete(AuthUser $authUser, ReportAnalytic $reportAnalytic): bool
    {
        return $authUser->can('ForceDelete:ReportAnalytic');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ReportAnalytic');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ReportAnalytic');
    }

    public function replicate(AuthUser $authUser, ReportAnalytic $reportAnalytic): bool
    {
        return $authUser->can('Replicate:ReportAnalytic');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ReportAnalytic');
    }

}