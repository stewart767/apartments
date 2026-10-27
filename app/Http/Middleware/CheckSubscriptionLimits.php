<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SubscriptionEntitlementService;

class CheckSubscriptionLimits
{
    protected $entitlementService;

    public function __construct(SubscriptionEntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $action (e.g. 'property', 'unit', 'staff')
     */
    public function handle(Request $request, Closure $next, string $action = 'property'): Response
    {
        $user = $request->user();

        // Superadmins bypass limits
        if (!$user || $user->isSuperadmin()) {
            return $next($request);
        }

        // Only enforce on owners/agents
        if (!$user->isOwner() && !$user->isAgent()) {
            return $next($request);
        }

        $org = $user->resolveOrganization();

        $check = match($action) {
            'property' => $this->entitlementService->canCreateProperty($org),
            'unit' => $this->entitlementService->canAddUnits($org, 1),
            'staff' => $this->entitlementService->canAddStaff($org),
            default => ['allowed' => true],
        };

        if (!$check['allowed']) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'subscription_limit_exceeded',
                    'message' => $check['message'],
                    'upgrade_url' => route('owner.subscription'),
                ], 403);
            }

            return redirect()->route('owner.subscription')
                ->with('error', $check['message']);
        }

        return $next($request);
    }
}
