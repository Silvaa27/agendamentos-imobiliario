<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BusinessHour;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusinessHourPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BusinessHour');
    }

    public function view(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('View:BusinessHour');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BusinessHour');
    }

    public function update(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('Update:BusinessHour');
    }

    public function delete(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('Delete:BusinessHour');
    }

    public function restore(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('Restore:BusinessHour');
    }

    public function forceDelete(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('ForceDelete:BusinessHour');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BusinessHour');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BusinessHour');
    }

    public function replicate(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('Replicate:BusinessHour');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BusinessHour');
    }

}