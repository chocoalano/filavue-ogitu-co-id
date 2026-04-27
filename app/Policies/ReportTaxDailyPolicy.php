<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ReportTaxDaily;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportTaxDailyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ReportTaxDaily');
    }

    public function view(AuthUser $authUser, ReportTaxDaily $reportTaxDaily): bool
    {
        return $authUser->can('View:ReportTaxDaily');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ReportTaxDaily');
    }

    public function update(AuthUser $authUser, ReportTaxDaily $reportTaxDaily): bool
    {
        return $authUser->can('Update:ReportTaxDaily');
    }

    public function delete(AuthUser $authUser, ReportTaxDaily $reportTaxDaily): bool
    {
        return $authUser->can('Delete:ReportTaxDaily');
    }

    public function restore(AuthUser $authUser, ReportTaxDaily $reportTaxDaily): bool
    {
        return $authUser->can('Restore:ReportTaxDaily');
    }

    public function forceDelete(AuthUser $authUser, ReportTaxDaily $reportTaxDaily): bool
    {
        return $authUser->can('ForceDelete:ReportTaxDaily');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ReportTaxDaily');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ReportTaxDaily');
    }

    public function replicate(AuthUser $authUser, ReportTaxDaily $reportTaxDaily): bool
    {
        return $authUser->can('Replicate:ReportTaxDaily');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ReportTaxDaily');
    }

}