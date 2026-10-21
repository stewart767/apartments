<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssetPolicy
{
    /**
     * Determine whether the user can manage the asset.
     */
    public function manage(User $user, Asset $asset): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $asset->property->owner_id) {
            return true;
        }

        if ($user->isManager() || $user->isReceptionist()) {
            return DB::table('property_user')
                ->where('property_id', $asset->property_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }
}
