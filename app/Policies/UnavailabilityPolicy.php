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
        return $authUser->can('ViewAny:Unavailability');
    }

    public function view(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('View:Unavailability');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Unavailability');
    }

    public function update(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('Update:Unavailability');
    }

    public function delete(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('Delete:Unavailability');
    }

    public function restore(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('Restore:Unavailability');
    }

    public function forceDelete(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('ForceDelete:Unavailability');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Unavailability');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Unavailability');
    }

    public function replicate(AuthUser $authUser, Unavailability $unavailability): bool
    {
        return $authUser->can('Replicate:Unavailability');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Unavailability');
    }

}