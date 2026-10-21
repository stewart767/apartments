<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PropertyPolicy
{
    /**
     * Determine whether the user can view the property details.
     */
    public function view(?User $user, Property $property): bool
    {
        if ($property->status === 'published' && $property->verification_status === 'approved') {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $this->manage($user, $property);
    }

    /**
     * Determine whether the user can update the property.
     */
    public function update(User $user, Property $property): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return $user->id === $property->owner_id;
    }

    /**
     * Determine whether the user can delete the property.
     */
    public function delete(User $user, Property $property): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return $user->id === $property->owner_id;
    }

    /**
     * Determine whether the user can manage the property (Owner or assigned Manager).
     */
    public function manage(User $user, Property $property): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $property->owner_id) {
            return true;
        }

        if ($user->isManager() || $user->isReceptionist() || $user->isAgent()) {
            return DB::table('property_user')
                ->where('property_id', $property->id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }
}
