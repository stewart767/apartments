<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentPolicy
{
    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $payment->tenant_id) {
            return true;
        }

        if ($user->id === $payment->property->owner_id) {
            return true;
        }

        if ($user->isManager() || $user->isReceptionist() || $user->isAgent() || $user->isAccountant()) {
            return DB::table('property_user')
                ->where('property_id', $payment->property_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the tenant can submit proof for the payment.
     */
    public function submitProof(User $user, Payment $payment): bool
    {
        return $user->id === $payment->tenant_id && $payment->status === 'pending';
    }

    /**
     * Determine whether the user can verify the payment.
     */
    public function verify(User $user, Payment $payment): bool
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->id === $payment->property->owner_id) {
            return true;
        }

        if ($user->isManager() || $user->isAccountant()) {
            return DB::table('property_user')
                ->where('property_id', $payment->property_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }
}
