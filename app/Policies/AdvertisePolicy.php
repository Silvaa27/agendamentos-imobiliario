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
        return $authUser->can('view_any_advertise');
    }

    public function view(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('view_advertise');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_advertise');
    }

    public function update(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('update_advertise');
    }

    public function delete(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('delete_advertise');
    }

    public function restore(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('restore_advertise');
    }

    public function forceDelete(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('force_delete_advertise');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_advertise');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_advertise');
    }

    public function replicate(AuthUser $authUser, Advertise $advertise): bool
    {
        return $authUser->can('replicate_advertise');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_advertise');
    }

}