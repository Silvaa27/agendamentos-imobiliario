<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Opportunity;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpportunityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_opportunity');
    }

    public function view(AuthUser $authUser, Opportunity $opportunity): bool
    {
        return $authUser->can('view_opportunity');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_opportunity');
    }

    public function update(AuthUser $authUser, Opportunity $opportunity): bool
    {
        return $authUser->can('update_opportunity');
    }

    public function delete(AuthUser $authUser, Opportunity $opportunity): bool
    {
        return $authUser->can('delete_opportunity');
    }

    public function restore(AuthUser $authUser, Opportunity $opportunity): bool
    {
        return $authUser->can('restore_opportunity');
    }

    public function forceDelete(AuthUser $authUser, Opportunity $opportunity): bool
    {
        return $authUser->can('force_delete_opportunity');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_opportunity');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_opportunity');
    }

    public function replicate(AuthUser $authUser, Opportunity $opportunity): bool
    {
        return $authUser->can('replicate_opportunity');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_opportunity');
    }

}