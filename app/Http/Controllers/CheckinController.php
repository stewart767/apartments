<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\CheckinService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    protected $checkinService;
    protected $checkoutService;

    public function __construct(CheckinService $checkinService, CheckoutService $checkoutService)
    {
        $this->checkinService = $checkinService;
        $this->checkoutService = $checkoutService;
    }

    /**
     * List contracts for check-in / check-out processing.
     */
    public function index(Request $request)
    {
        $managerId = auth()->id();
        
        $propertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        if (empty($propertyIds)) {
            $contracts = collect();
            return view('manager.checkins.index', compact('contracts'));
        }

        $query = Contract::with(['property', 'tenant.tenantAssets', 'room.propertyAssets', 'bed', 'checkIn'])
            ->whereIn('property_id', $propertyIds);

        // Filter: checkin tab or checkout tab
        if ($request->boolean('checkout') || $request->input('tab') === 'checkout') {
            $query->where('status', 'active');
        } else {
            $query->where('status', 'accepted');
        }

        $contracts = $query->latest()->paginate(10);

        return view('manager.checkins.index', compact('contracts'));
    }

    /**
     * Process Tenant Check-in.
     */
    public function checkIn(Request $request, $contractId)
    {
        $request->validate([
            'check_in_date' => 'required|date',
            'deposit_received' => 'required|numeric|min:0',
            'room_condition_notes' => 'nullable|string|max:500',
            'keys_count' => 'required|integer|min:1',
            'property_assets' => 'nullable|array',
            'tenant_assets' => 'nullable|array',
        ]);

        $contract = Contract::findOrFail($contractId);
        $this->authorizePropertyManagement($contract->property_id);

        $this->checkinService->processCheckin($contract, $request->all(), auth()->user());

        return redirect()->route('manager.checkins')
            ->with('success', 'Tenant check-in logged successfully! Baseline inventory verified.');
    }

    /**
     * Process Tenant Check-out.
     */
    public function checkOut(Request $request, $contractId)
    {
        $request->validate([
            'check_out_date' => 'required|date',
            'room_condition_notes' => 'nullable|string|max:500',
            'damage_charges' => 'required|numeric|min:0',
            'deposit_refunded' => 'required|numeric|min:0',
            'outstanding_settled' => 'required|boolean',
            'checkout_property_assets' => 'nullable|array',
            'checkout_tenant_assets' => 'nullable|array',
        ]);

        $contract = Contract::findOrFail($contractId);
        $this->authorizePropertyManagement($contract->property_id);

        $this->checkoutService->processCheckout($contract, $request->all(), auth()->user());

        return redirect()->route('manager.checkins', ['tab' => 'checkout'])
            ->with('success', 'Tenant check-out logged successfully! Room allocation freed and damage costs recorded.');
    }

    /**
     * Authorize manager checks.
     */
    protected function authorizePropertyManagement(int $propertyId)
    {
        $user = auth()->user();
        if ($user->isSuperadmin()) {
            return true;
        }
        
        $hasAssignment = DB::table('property_user')
            ->where('property_id', $propertyId)
            ->where('user_id', $user->id)
            ->exists();
            
        if ($hasAssignment) {
            return true;
        }

        abort(403, 'Unauthorized manager check-in operation.');
    }
}
