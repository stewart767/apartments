<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApplicationPolicy
{
    /**
     * Determine whether the user can view the application.
     */
    public function view(User $user, Application $application): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $application->tenant_id) {
            return true;
        }

        if ($user->id === $application->property->owner_id) {
            return true;
        }

        if ($user->isManager() || $user->isReceptionist() || $user->isAgent()) {
            return DB::table('property_user')
                ->where('property_id', $application->property_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can process (approve/reject) the application.
     */
    public function process(User $user, Application $application): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $application->property->owner_id) {
            return true;
        }

        if ($user->isManager()) {
            return DB::table('property_user')
                ->where('property_id', $application->property_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the application.
     */
    public function cancel(User $user, Application $application): bool
    {
        return $user->id === $application->tenant_id && $application->status === 'pending';
    }
}
