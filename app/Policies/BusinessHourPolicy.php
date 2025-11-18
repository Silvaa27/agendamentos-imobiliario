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
        return $authUser->can('view_any_business_hour');
    }

    public function view(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('view_business_hour');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_business_hour');
    }

    public function update(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('update_business_hour');
    }

    public function delete(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('delete_business_hour');
    }

    public function restore(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('restore_business_hour');
    }

    public function forceDelete(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('force_delete_business_hour');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_business_hour');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_business_hour');
    }

    public function replicate(AuthUser $authUser, BusinessHour $businessHour): bool
    {
        return $authUser->can('replicate_business_hour');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_business_hour');
    }

}