<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Advertise;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvertisePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Advertise');
    }

    public function view(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('View:Advertise');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Advertise');
    }

    public function update(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('Update:Advertise');
    }

    public function delete(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('Delete:Advertise');
    }

    public function restore(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('Restore:Advertise');
    }

    public function forceDelete(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('ForceDelete:Advertise');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Advertise');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Advertise');
    }

    public function replicate(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('Replicate:Advertise');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Advertise');
    }

}