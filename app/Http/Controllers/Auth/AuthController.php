<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TenantDetail;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'user_logged_in',
                'description' => 'User logged in successfully.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        $institutions = \App\Models\Institution::orderBy('name')->get();
        return view('auth.register', compact('institutions'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:tenant,owner'],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            
            // Conditional Tenant fields
            'national_id_passport' => ['required_if:role,tenant', 'nullable', 'string', 'max:50'],
            'emergency_contact' => ['required_if:role,tenant', 'nullable', 'string', 'max:20'],
            'is_student' => ['nullable'],
            'institution_name' => ['required_if:is_student,1', 'nullable', 'string', 'max:255'],
            'student_id' => ['required_if:is_student,1', 'nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
        ]);

        // If tenant, create tenant details
        if ($user->isTenant()) {
            $isStudent = $request->boolean('is_student');
            $institutionId = null;

            if ($isStudent && $request->filled('institution_name')) {
                $institution = \App\Models\Institution::firstOrCreate([
                    'name' => trim($request->institution_name)
                ]);
                $institutionId = $institution->id;
            }

            TenantDetail::create([
                'user_id' => $user->id,
                'is_student' => $isStudent,
                'institution_id' => $institutionId,
                'student_reg_number' => $isStudent ? $request->student_id : null,
                'national_id_passport' => $request->national_id_passport,
                'emergency_contact' => $request->emergency_contact,
            ]);
        }

        // Log registration
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'user_registered',
            'description' => "User registered as '{$user->role}'.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    public function logout(Request $request)
    {
        // Log logout before destroying session
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'user_logged_out',
                'description' => 'User logged out.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
