<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AdvertiseAnswer;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvertiseAnswerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdvertiseAnswer');
    }

    public function view(AuthUser $authUser, AdvertiseAnswer $advertiseAnswer): bool
    {
        return $authUser->can('View:AdvertiseAnswer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdvertiseAnswer');
    }

    public function update(AuthUser $authUser, AdvertiseAnswer $advertiseAnswer): bool
    {
        return $authUser->can('Update:AdvertiseAnswer');
    }

    public function delete(AuthUser $authUser, AdvertiseAnswer $advertiseAnswer): bool
    {
        return $authUser->can('Delete:AdvertiseAnswer');
    }

    public function restore(AuthUser $authUser, AdvertiseAnswer $advertiseAnswer): bool
    {
        return $authUser->can('Restore:AdvertiseAnswer');
    }

    public function forceDelete(AuthUser $authUser, AdvertiseAnswer $advertiseAnswer): bool
    {
        return $authUser->can('ForceDelete:AdvertiseAnswer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdvertiseAnswer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdvertiseAnswer');
    }

    public function replicate(AuthUser $authUser, AdvertiseAnswer $advertiseAnswer): bool
    {
        return $authUser->can('Replicate:AdvertiseAnswer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdvertiseAnswer');
    }

}