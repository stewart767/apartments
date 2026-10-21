<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MaintenancePolicy
{
    /**
     * Determine whether the user can view the maintenance request.
     */
    public function view(User $user, MaintenanceRequest $ticket): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $ticket->tenant_id) {
            return true;
        }

        if ($user->id === $ticket->property->owner_id) {
            return true;
        }

        if ($user->isManager() || $user->isReceptionist() || $user->isAgent() || $user->isCaretaker()) {
            return DB::table('property_user')
                ->where('property_id', $ticket->property_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can assign or resolve the maintenance request.
     */
    public function manage(User $user, MaintenanceRequest $ticket): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $ticket->property->owner_id) {
            return true;
        }

        if ($user->isManager()) {
            return DB::table('property_user')
                ->where('property_id', $ticket->property_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }
}
