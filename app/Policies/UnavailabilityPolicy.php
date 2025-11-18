<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Unavailability;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnavailabilityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_unavailability');
    }

    public function view(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('view_unavailability');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_unavailability');
    }

    public function update(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('update_unavailability');
    }

    public function delete(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('delete_unavailability');
    }

    public function restore(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('restore_unavailability');
    }

    public function forceDelete(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('force_delete_unavailability');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_unavailability');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_unavailability');
    }

    public function replicate(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('replicate_unavailability');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_unavailability');
    }

}