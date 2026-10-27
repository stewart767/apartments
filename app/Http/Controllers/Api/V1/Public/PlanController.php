<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class PlanController extends Controller
{
    /**
     * List active SaaS subscription plans.
     */
    public function index()
    {
        $plans = Plan::active()->get();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plans retrieved successfully.',
            'data' => $plans,
        ]);
    }
}
