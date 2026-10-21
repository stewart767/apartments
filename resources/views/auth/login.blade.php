@extends('layouts.auth')

@section('title', 'Log In')
@section('header_title', 'Welcome back')
@section('header_subtitle', 'Sign in to your account to manage your bookings and properties')

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-6">
    @csrf

    <!-- Email Address -->
    <div>
        <label for="email" class="block text-sm font-semibold text-slate-700">Email address</label>
        <div class="mt-1.5 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </span>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" 
                   class="block w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm @error('email') border-rose-300 ring-2 ring-rose-100 @enderror">
        </div>
        @error('email')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Password -->
    <div x-data="{ show: false }">
        <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
            <div class="text-sm">
                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">Forgot your password?</a>
            </div>
        </div>
        <div class="mt-1.5 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </span>
            <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password" 
                   class="block w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="eye" class="w-4 h-4" x-show="!show"></i>
                <i data-lucide="eye-off" class="w-4 h-4" x-show="show" style="display: none;"></i>
            </button>
        </div>
        @error('password')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Remember Me -->
    <div class="flex items-center">
        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        <label for="remember" class="ml-2 block text-sm text-slate-600 select-none">Remember my device</label>
    </div>

    <!-- Submit -->
    <div>
        <button type="submit" class="flex w-full justify-center items-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">
            <span>Sign In</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
    </div>
</form>

<div class="mt-6 text-center text-sm">
    <span class="text-slate-500">New to RelianceHomes?</span>
    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 transition-colors">Create an account</a>
</div>

<!-- Quick Login Helper -->
<div class="mt-8 pt-6 border-t border-slate-100 space-y-3" x-data="{
    fill(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
        document.querySelector('form').submit();
    }
}">
    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Quick Login (Demo)</p>
    <div class="grid grid-cols-3 gap-2">
        <button type="button" @click="fill('admin@platform.com', 'password')" class="px-2.5 py-1.5 bg-indigo-50/50 hover:bg-indigo-50 text-indigo-600 text-xs font-semibold rounded-xl border border-indigo-100/50 transition-colors text-center">
            Admin
        </button>
        <button type="button" @click="fill('owner@platform.com', 'password')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100/80 text-slate-600 text-xs font-semibold rounded-xl border border-slate-200/50 transition-colors text-center">
            Owner
        </button>
        <button type="button" @click="fill('tenant@platform.com', 'password')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100/80 text-slate-600 text-xs font-semibold rounded-xl border border-slate-200/50 transition-colors text-center">
            Tenant
        </button>
    </div>
</div>
@endsection
