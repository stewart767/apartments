@extends('layouts.auth')

@section('title', 'Sign Up')
@section('header_title', 'Create your account')
@section('header_subtitle', 'Get started with renting or managing spaces on RelianceHomes')

@section('content')
<form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ role: 'tenant', isStudent: {{ old('is_student') ? 'true' : 'false' }} }">
    @csrf

    <!-- Full Name -->
    <div>
        <label for="name" class="block text-sm font-semibold text-slate-700">Full Name</label>
        <div class="mt-1.5 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                <i data-lucide="user" class="w-4 h-4"></i>
            </span>
            <input id="name" name="name" type="text" required value="{{ old('name') }}" autocomplete="name" 
                   class="block w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
        </div>
        @error('name')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Email Address -->
    <div>
        <label for="email" class="block text-sm font-semibold text-slate-700">Email Address</label>
        <div class="mt-1.5 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </span>
            <input id="email" name="email" type="email" required value="{{ old('email') }}" autocomplete="email" 
                   class="block w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
        </div>
        @error('email')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Role Selection -->
    <div>
        <label class="block text-sm font-semibold text-slate-700">I want to register as a:</label>
        <div class="mt-2 grid grid-cols-2 gap-4">
            <label class="relative flex cursor-pointer rounded-xl border p-3 focus:outline-none transition-all"
                   :class="role === 'tenant' ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600' : 'border-slate-200 bg-white hover:bg-slate-50'">
                <input type="radio" name="role" value="tenant" x-model="role" class="sr-only" required>
                <div class="flex flex-col">
                    <span class="block text-sm font-bold text-slate-900" :class="role === 'tenant' ? 'text-indigo-900' : 'text-slate-900'">Tenant / Student</span>
                    <span class="mt-1 text-xs text-slate-500">Find and book places</span>
                </div>
            </label>

            <label class="relative flex cursor-pointer rounded-xl border p-3 focus:outline-none transition-all"
                   :class="role === 'owner' ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600' : 'border-slate-200 bg-white hover:bg-slate-50'">
                <input type="radio" name="role" value="owner" x-model="role" class="sr-only" required>
                <div class="flex flex-col">
                    <span class="block text-sm font-bold text-slate-900" :class="role === 'owner' ? 'text-indigo-900' : 'text-slate-900'">Property Owner</span>
                    <span class="mt-1 text-xs text-slate-500">List and manage rooms</span>
                </div>
            </label>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-2 gap-4">
        <!-- Phone -->
        <div>
            <label for="phone" class="block text-sm font-semibold text-slate-700">Phone Number</label>
            <input id="phone" name="phone" type="text" required value="{{ old('phone') }}" placeholder="e.g. +255..."
                   class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
            @error('phone')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- WhatsApp -->
        <div>
            <label for="whatsapp" class="block text-sm font-semibold text-slate-700">WhatsApp (Optional)</label>
            <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp') }}" placeholder="e.g. +255..."
                   class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
            @error('whatsapp')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <!-- Gender -->
        <div>
            <label for="gender" class="block text-sm font-semibold text-slate-700">Gender</label>
            <select id="gender" name="gender" required 
                    class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                <option value="" disabled selected>Select...</option>
                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('gender')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Date of Birth -->
        <div>
            <label for="date_of_birth" class="block text-sm font-semibold text-slate-700">Date of Birth</label>
            <input id="date_of_birth" name="date_of_birth" type="date" required value="{{ old('date_of_birth') }}"
                   class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
            @error('date_of_birth')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    
    <!-- Tenant Details Section (Conditional) -->
    <div x-show="role === 'tenant'" x-transition class="space-y-4 border-t border-slate-100 pt-4 mt-4">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tenant Profile Details</h4>
        
        <div class="grid grid-cols-2 gap-4">
            <!-- National ID / Passport -->
            <div>
                <label for="national_id_passport" class="block text-sm font-semibold text-slate-700">National ID / Passport</label>
                <input id="national_id_passport" name="national_id_passport" type="text" :required="role === 'tenant'" value="{{ old('national_id_passport') }}" placeholder="ID or Passport Number"
                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                @error('national_id_passport')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Emergency Contact -->
            <div>
                <label for="emergency_contact" class="block text-sm font-semibold text-slate-700">Emergency Contact</label>
                <input id="emergency_contact" name="emergency_contact" type="text" :required="role === 'tenant'" value="{{ old('emergency_contact') }}" placeholder="e.g. parent phone"
                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                @error('emergency_contact')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Student Status Switch -->
        <div class="flex items-center justify-between bg-slate-50 p-3 rounded-2xl border border-slate-100">
            <div class="flex flex-col">
                <span class="text-sm font-bold text-slate-900">Are you a Student?</span>
                <span class="text-xs text-slate-505">Toggle to link your university details</span>
            </div>
            <!-- Switch toggler -->
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_student" value="1" x-model="isStudent" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>

        <!-- Conditional Student Fields -->
        <div x-show="isStudent" x-transition class="grid grid-cols-2 gap-4 bg-slate-50 p-4 border border-slate-100 rounded-2xl">
            <!-- Institution Selection / Custom input -->
            <div>
                <label for="institution_name" class="block text-sm font-semibold text-slate-700">Select/Type Institution</label>
                <input id="institution_name" name="institution_name" list="institutions_list" :required="role === 'tenant' && isStudent" value="{{ old('institution_name') }}" placeholder="Select or type..."
                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                <datalist id="institutions_list">
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->name }}"></option>
                    @endforeach
                </datalist>
                @error('institution_name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Student ID -->
            <div>
                <label for="student_id" class="block text-sm font-semibold text-slate-700">Student ID</label>
                <input id="student_id" name="student_id" type="text" :required="role === 'tenant' && isStudent" value="{{ old('student_id') }}" placeholder="Student ID Number"
                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                @error('student_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Password Grid -->
    <div class="grid grid-cols-2 gap-4" x-data="{ showPass: false }">
        <!-- Password -->
        <div class="relative">
            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
            <div class="mt-1.5 relative">
                <input id="password" name="password" :type="showPass ? 'text' : 'password'" required 
                       class="block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
            </div>
            @error('password')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm Password</label>
            <div class="mt-1.5 relative">
                <input id="password_confirmation" name="password_confirmation" :type="showPass ? 'text' : 'password'" required 
                       class="block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="eye" class="w-4 h-4" x-show="!showPass"></i>
                    <i data-lucide="eye-off" class="w-4 h-4" x-show="showPass" style="display: none;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="pt-2">
        <button type="submit" class="flex w-full justify-center items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
            <span>Create Account</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
    </div>
</form>

<div class="mt-5 text-center text-sm">
    <span class="text-slate-500">Already have an account?</span>
    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 transition-colors">Sign in instead</a>
</div>
@endsection
