<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    protected $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * Display contracts for the authenticated tenant.
     */
    public function tenantContracts()
    {
        $tenantId = auth()->id();
        $contracts = Contract::with(['property', 'room', 'bed'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10);

        return view('tenant.contracts.index', compact('contracts'));
    }

    /**
     * Display contracts for the property owner.
     */
    public function ownerContracts()
    {
        $ownerId = auth()->id();
        $contracts = Contract::whereHas('property', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })
        ->with(['property', 'tenant', 'room', 'bed'])
        ->latest()
        ->paginate(10);

        return view('owner.contracts.index', compact('contracts'));
    }

    /**
     * Display contracts for the property manager.
     */
    public function managerContracts()
    {
        $managerId = auth()->id();
        $contracts = Contract::whereHas('property.managers', function ($q) use ($managerId) {
            $q->where('users.id', $managerId);
        })
        ->with(['property', 'tenant', 'room', 'bed'])
        ->latest()
        ->paginate(10);

        return view('manager.contracts.index', compact('contracts'));
    }

    /**
     * Display a specific contract.
     */
    public function showContract($id)
    {
        $contract = Contract::with(['tenant.tenantDetail.institution', 'property', 'room', 'bed'])
            ->findOrFail($id);

        $this->authorizeContractAccess($contract);

        // Determine view path based on role
        if (auth()->user()->isTenant()) {
            $viewPath = 'tenant.contracts.show';
        } elseif (auth()->user()->isOwner()) {
            $viewPath = 'owner.contracts.show';
        } else {
            $viewPath = 'manager.contracts.show';
        }

        return view($viewPath, compact('contract'));
    }

    /**
     * Accept a tenancy contract (Tenant operation).
     */
    public function acceptContract($id)
    {
        $contract = Contract::where('tenant_id', auth()->id())
            ->where('status', 'pending_acceptance')
            ->findOrFail($id);

        $this->contractService->tenantAcceptContract($contract);

        return redirect()->route('tenant.contracts.show', $contract->id)
            ->with('success', 'You have successfully signed and accepted the tenancy lease contract! Please proceed to rent payments.');
    }

    /**
     * Download contract PDF.
     */
    public function downloadPdf($id)
    {
        $contract = Contract::with(['tenant.tenantDetail.institution', 'property', 'room', 'bed'])
            ->findOrFail($id);

        $this->authorizeContractAccess($contract);

        $pdf = Pdf::loadView('contracts.pdf', compact('contract'));
        return $pdf->download("Lease_Agreement_{$contract->contract_number}.pdf");
    }

    /**
     * Authorization check.
     */
    protected function authorizeContractAccess(Contract $contract)
    {
        $user = auth()->user();
        if ($user->isSuperadmin()) {
            return true;
        }
        if ($contract->tenant_id === $user->id) {
            return true;
        }
        if ($user->isOwner() && $contract->property->owner_id === $user->id) {
            return true;
        }
        if ($user->isManager() || $user->isReceptionist()) {
            $hasAssignment = \DB::table('property_user')
                ->where('property_id', $contract->property_id)
                ->where('user_id', $user->id)
                ->exists();
            if ($hasAssignment) {
                return true;
            }
        }

        abort(403, 'Unauthorized contract access.');
    }
}
