<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OwnerDetail;
use App\Models\WizardDraft;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OwnerOnboardingController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        // If they already have owner details, check status
        if ($user->ownerDetail) {
            return redirect()->route('dashboard')->with('info', 'Your profile onboarding is complete.');
        }

        // Fetch draft or initialize
        $draft = WizardDraft::firstOrCreate(
            ['user_id' => $user->id, 'wizard_type' => 'owner_onboarding'],
            ['current_step' => 2, 'data' => []]
        );

        $regions = Region::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $wards = Ward::orderBy('name')->get();

        return view('owner.onboarding.wizard', compact('draft', 'regions', 'districts', 'wards'));
    }

    public function saveDraft(Request $request)
    {
        $user = auth()->user();
        $draft = WizardDraft::firstOrCreate(
            ['user_id' => $user->id, 'wizard_type' => 'owner_onboarding'],
            ['current_step' => 2, 'data' => []]
        );

        $action = $request->input('action'); // 'next', 'previous', 'save_draft'
        $currentStep = (int) $request->input('current_step', 2);
        
        // Merge request data with existing draft data
        $existingData = $draft->data ?? [];
        $newData = $request->except(['_token', 'action', 'current_step', 'id_document', 'supporting_document', 'profile_photo']);
        $mergedData = array_merge($existingData, $newData);

        // Save uploaded profile photo to draft if present
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('onboarding/photos', 'public');
            $mergedData['profile_photo'] = $path;
        }

        // Save uploaded files to draft if present
        if ($request->hasFile('id_document')) {
            $path = $request->file('id_document')->store('onboarding/documents', 'public');
            $mergedData['id_document_path'] = $path;
        }

        if ($request->hasFile('supporting_document')) {
            $path = $request->file('supporting_document')->store('onboarding/documents', 'public');
            $mergedData['supporting_document_path'] = $path;
        }

        $draft->data = $mergedData;

        // Determine step progression
        if ($action === 'next') {
            // Validation for Step 2
            if ($currentStep === 2) {
                $request->validate([
                    'full_legal_name' => 'required|string|max:255',
                    'nida' => 'required|string|max:50',
                    'nationality' => 'required|string|max:100',
                    'phone' => 'required|string|max:20',
                    'email' => 'required|email|max:255',
                    'region_id' => 'required|exists:regions,id',
                    'district_id' => 'required|exists:districts,id',
                    'ward_id' => 'required|exists:wards,id',
                ]);
                $draft->current_step = 3;
            }
        } elseif ($action === 'previous') {
            if ($currentStep === 3) {
                $draft->current_step = 2;
            }
        }

        $draft->save();

        if ($action === 'save_draft') {
            return redirect()->route('dashboard')->with('success', 'Onboarding progress saved as draft successfully.');
        }

        return redirect()->route('owner.onboarding');
    }

    public function submit(Request $request)
    {
        $user = auth()->user();
        $draft = WizardDraft::firstOrCreate(
            ['user_id' => $user->id, 'wizard_type' => 'owner_onboarding'],
            ['current_step' => 3, 'data' => []]
        );

        $data = $draft->data ?? [];

        // Final validation
        $validatedData = validator(array_merge($data, $request->all()), [
            'full_legal_name' => 'required|string|max:255',
            'nida' => 'required|string|max:50',
            'nationality' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'ward_id' => 'required|exists:wards,id',
            'street_address' => 'nullable|string',
            'profile_photo' => 'nullable|string', // URL path from draft
            'id_document_path' => 'nullable|string', // URL path from draft
        ])->validate();

        // Ensure documents are uploaded (either previously in draft or now in request)
        $idDocumentPath = $data['id_document_path'] ?? null;
        if ($request->hasFile('id_document')) {
            $idDocumentPath = $request->file('id_document')->store('onboarding/documents', 'public');
        }

        $supportingDocumentPath = $data['supporting_document_path'] ?? null;
        if ($request->hasFile('supporting_document')) {
            $supportingDocumentPath = $request->file('supporting_document')->store('onboarding/documents', 'public');
        }

        $profilePhotoPath = $data['profile_photo'] ?? null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('onboarding/photos', 'public');
        }

        if (!$idDocumentPath) {
            return back()->withErrors(['id_document' => 'The National ID / Passport document is required.'])->withInput();
        }

        // Create the Owner profile in database
        DB::transaction(function () use ($user, $validatedData, $idDocumentPath, $supportingDocumentPath, $profilePhotoPath, $draft) {
            OwnerDetail::create([
                'user_id' => $user->id,
                'full_legal_name' => $validatedData['full_legal_name'],
                'nida' => $validatedData['nida'],
                'nationality' => $validatedData['nationality'],
                'phone' => $validatedData['phone'],
                'whatsapp' => $validatedData['whatsapp'] ?? null,
                'email' => $validatedData['email'],
                'region_id' => $validatedData['region_id'],
                'district_id' => $validatedData['district_id'],
                'ward_id' => $validatedData['ward_id'],
                'street_address' => $validatedData['street_address'] ?? null,
                'profile_photo' => $profilePhotoPath,
                'verification_status' => 'pending',
                'id_document_path' => $idDocumentPath,
                'supporting_document_path' => $supportingDocumentPath,
            ]);

            // Delete the draft
            $draft->delete();

            // Audit Log
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'owner_onboarding_submitted',
                'description' => 'Owner completed and submitted onboarding profile for verification.',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect()->route('dashboard')->with('success', 'Your onboarding profile has been submitted and is under review by our administration.');
    }
}
