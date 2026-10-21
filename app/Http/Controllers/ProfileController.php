<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TenantDetail;
use App\Models\OwnerDetail;
use App\Models\Institution;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the user profile page.
     */
    public function show()
    {
        $user = auth()->user();
        
        $institutions = collect();
        $regions = collect();
        $districts = collect();
        $wards = collect();

        if ($user->isTenant()) {
            $institutions = Institution::orderBy('name')->get();
        } elseif ($user->isOwner()) {
            $regions = Region::orderBy('name')->get();
            $districts = District::orderBy('name')->get();
            $wards = Ward::orderBy('name')->get();
        }

        return view('profile.index', compact('user', 'institutions', 'regions', 'districts', 'wards'));
    }

    /**
     * Update the user profile details.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        // Build dynamic validation rules based on user role
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date|before:today',
            'avatar' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        if ($user->isTenant()) {
            $rules['national_id_passport'] = 'required|string|max:50';
            $rules['emergency_contact'] = 'required|string|max:20';
            $rules['is_student'] = 'nullable';
            $rules['institution_name'] = 'required_if:is_student,1|nullable|string|max:255';
            $rules['student_id'] = 'required_if:is_student,1|nullable|string|max:50';
        } elseif ($user->isOwner() && $user->ownerDetail) {
            $rules['full_legal_name'] = 'required|string|max:255';
            $rules['nida'] = 'required|string|max:50';
            $rules['nationality'] = 'required|string|max:100';
            $rules['region_id'] = 'required|exists:regions,id';
            $rules['district_id'] = 'required|exists:districts,id';
            $rules['ward_id'] = 'required|exists:wards,id';
            $rules['street_address'] = 'nullable|string';
            $rules['profile_photo'] = 'nullable|image|max:2048';
            $rules['id_document'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['supporting_document'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        $validated = $request->validate($rules);

        // 1. Update base User details
        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->whatsapp = $validated['whatsapp'] ?? null;
        $user->gender = $validated['gender'];
        $user->date_of_birth = $validated['date_of_birth'];

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        // 2. Update Tenant details if applicable
        if ($user->isTenant()) {
            $isStudent = $request->boolean('is_student');
            $institutionId = null;

            if ($isStudent && $request->filled('institution_name')) {
                $institution = Institution::firstOrCreate([
                    'name' => trim($request->institution_name)
                ]);
                $institutionId = $institution->id;
            }

            TenantDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_student' => $isStudent,
                    'institution_id' => $institutionId,
                    'student_reg_number' => $isStudent ? $request->student_id : null,
                    'national_id_passport' => $validated['national_id_passport'],
                    'emergency_contact' => $validated['emergency_contact'],
                ]
            );
        }

        // 3. Update Owner details if applicable
        if ($user->isOwner() && $user->ownerDetail) {
            $detail = $user->ownerDetail;

            $ownerData = [
                'full_legal_name' => $validated['full_legal_name'],
                'nida' => $validated['nida'],
                'nationality' => $validated['nationality'],
                'phone' => $validated['phone'], // Sync with user phone
                'whatsapp' => $validated['whatsapp'] ?? null, // Sync with user whatsapp
                'region_id' => $validated['region_id'],
                'district_id' => $validated['district_id'],
                'ward_id' => $validated['ward_id'],
                'street_address' => $validated['street_address'] ?? null,
            ];

            if ($request->hasFile('profile_photo')) {
                if ($detail->profile_photo) {
                    Storage::disk('public')->delete($detail->profile_photo);
                }
                $ownerData['profile_photo'] = $request->file('profile_photo')->store('onboarding/photos', 'public');
            }

            if ($request->hasFile('id_document')) {
                if ($detail->id_document_path) {
                    Storage::disk('public')->delete($detail->id_document_path);
                }
                $ownerData['id_document_path'] = $request->file('id_document')->store('onboarding/documents', 'public');
            }

            if ($request->hasFile('supporting_document')) {
                if ($detail->supporting_document_path) {
                    Storage::disk('public')->delete($detail->supporting_document_path);
                }
                $ownerData['supporting_document_path'] = $request->file('supporting_document')->store('onboarding/documents', 'public');
            }

            $detail->update($ownerData);
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
