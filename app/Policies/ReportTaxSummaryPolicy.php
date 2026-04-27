<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ReportTaxSummary;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportTaxSummaryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ReportTaxSummary');
    }

    public function view(AuthUser $authUser, ReportTaxSummary $reportTaxSummary): bool
    {
        return $authUser->can('View:ReportTaxSummary');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ReportTaxSummary');
    }

    public function update(AuthUser $authUser, ReportTaxSummary $reportTaxSummary): bool
    {
        return $authUser->can('Update:ReportTaxSummary');
    }

    public function delete(AuthUser $authUser, ReportTaxSummary $reportTaxSummary): bool
    {
        return $authUser->can('Delete:ReportTaxSummary');
    }

    public function restore(AuthUser $authUser, ReportTaxSummary $reportTaxSummary): bool
    {
        return $authUser->can('Restore:ReportTaxSummary');
    }

    public function forceDelete(AuthUser $authUser, ReportTaxSummary $reportTaxSummary): bool
    {
        return $authUser->can('ForceDelete:ReportTaxSummary');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ReportTaxSummary');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ReportTaxSummary');
    }

    public function replicate(AuthUser $authUser, ReportTaxSummary $reportTaxSummary): bool
    {
        return $authUser->can('Replicate:ReportTaxSummary');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ReportTaxSummary');
    }

}