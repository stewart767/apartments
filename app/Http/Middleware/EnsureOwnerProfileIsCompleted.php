<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerProfileIsCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isOwner()) {
            $route = $request->route();
            if ($route && (in_array($route->getName(), [
                'owner.onboarding',
                'owner.onboarding.save-draft',
                'owner.onboarding.submit',
                'owner.subscription',
                'owner.subscription.subscribe',
                'owner.subscription.submit-payment',
                'owner.subscription.cancel',
                'logout'
            ]) || str_starts_with($route->getName() ?? '', 'owner.subscription'))) {
                return $next($request);
            }

            $detail = $user->ownerDetail;

            // If no details, or if onboarding is not submitted (i.e. we don't have the status or it's empty)
            if (!$detail) {
                return redirect()->route('owner.onboarding');
            }
        }

        return $next($request);
    }
}
