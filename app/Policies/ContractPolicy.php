<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContractPolicy
{
    /**
     * Determine whether the user can view the contract.
     */
    public function view(User $user, Contract $contract): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $contract->tenant_id) {
            return true;
        }

        if ($user->id === $contract->property->owner_id) {
            return true;
        }

        if ($user->isManager() || $user->isReceptionist() || $user->isAgent()) {
            return DB::table('property_user')
                ->where('property_id', $contract->property_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the tenant can accept the contract.
     */
    public function accept(User $user, Contract $contract): bool
    {
        return $user->id === $contract->tenant_id && $contract->status === 'pending_acceptance';
    }
}
