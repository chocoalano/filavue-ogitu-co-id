<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProductReview;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductReviewPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductReview');
    }

    public function view(AuthUser $authUser, ProductReview $productReview): bool
    {
        return $authUser->can('View:ProductReview');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductReview');
    }

    public function update(AuthUser $authUser, ProductReview $productReview): bool
    {
        return $authUser->can('Update:ProductReview');
    }

    public function delete(AuthUser $authUser, ProductReview $productReview): bool
    {
        return $authUser->can('Delete:ProductReview');
    }

    public function restore(AuthUser $authUser, ProductReview $productReview): bool
    {
        return $authUser->can('Restore:ProductReview');
    }

    public function forceDelete(AuthUser $authUser, ProductReview $productReview): bool
    {
        return $authUser->can('ForceDelete:ProductReview');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductReview');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductReview');
    }

    public function replicate(AuthUser $authUser, ProductReview $productReview): bool
    {
        return $authUser->can('Replicate:ProductReview');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductReview');
    }

}