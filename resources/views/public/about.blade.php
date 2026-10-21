@extends('layouts.public')

@section('title', 'About Us - RelianceHomes')
@section('meta_description', 'Learn about RelianceHomes, Tanzania’s premium hostel and accommodation management platform.')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden bg-slate-950 py-24 sm:py-32">
    <!-- Glowing background elements -->
    <div class="absolute -top-40 -left-40 h-[600px] w-[600px] rounded-full bg-indigo-600/10 blur-3xl"></div>
    <div class="absolute top-1/2 right-0 -translate-y-1/2 h-[500px] w-[500px] rounded-full bg-purple-600/10 blur-3xl"></div>
    
    <div class="mx-auto max-w-7xl px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-400 ring-1 ring-inset ring-indigo-500/25">
            About RelianceHomes
        </span>
        <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-6xl bg-gradient-to-r from-white via-slate-100 to-indigo-200 bg-clip-text text-transparent">
            Our Mission & Vision
        </h1>
        <p class="mt-6 text-lg leading-8 text-slate-400 max-w-2xl mx-auto">
            We are revolutionizing accommodation leasing in East Africa through physical verification, digital transparency, and seamless automated contracts.
        </p>
    </div>
</div>

<!-- Interactive Stats Section -->
<div class="bg-white py-16 sm:py-24 border-b border-slate-100">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-y-12 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 text-center">
            <!-- Stat 1 -->
            <div class="group relative bg-slate-50 hover:bg-indigo-50/40 p-8 rounded-3xl border border-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-100/50">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 mx-auto group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <i data-lucide="home" class="w-6 h-6"></i>
                </div>
                <dd class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">1,200+</dd>
                <dt class="mt-2 text-sm font-medium text-slate-500">Verified Beds</dt>
            </div>
            <!-- Stat 2 -->
            <div class="group relative bg-slate-50 hover:bg-indigo-50/40 p-8 rounded-3xl border border-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-100/50">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 mx-auto group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <dd class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">3+</dd>
                <dt class="mt-2 text-sm font-medium text-slate-500">Active Regions</dt>
            </div>
            <!-- Stat 3 -->
            <div class="group relative bg-slate-50 hover:bg-indigo-50/40 p-8 rounded-3xl border border-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-100/50">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 mx-auto group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <dd class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">100%</dd>
                <dt class="mt-2 text-sm font-medium text-slate-500">Physical Verification</dt>
            </div>
            <!-- Stat 4 -->
            <div class="group relative bg-slate-50 hover:bg-indigo-50/40 p-8 rounded-3xl border border-slate-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-100/50">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 mx-auto group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <dd class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900">2,500+</dd>
                <dt class="mt-2 text-sm font-medium text-slate-500">Active Tenants</dt>
            </div>
        </div>
    </div>
</div>

<!-- Mission Details Section -->
<div class="bg-slate-50 py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="space-y-6">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Who We Are & What We Solve
                </h2>
                <p class="text-base leading-7 text-slate-600">
                    RelianceHomes is a leading accommodation management platform built to bridge the trust gap between property owners, managers, and tenants across East Africa. We operate out of Dar es Salaam, targeting key rental hurdles: listing fraud, pricing manipulation, manually handled complex contracts, and inefficient check-in protocols.
                </p>
                <p class="text-base leading-7 text-slate-600">
                    By combining state-of-the-art software systems with ground-level physical inspections, we ensure every listed bed is safe, secure, and correctly mapped to nearby colleges, institutions, and major transit links.
                </p>
            </div>
            <!-- Dynamic visual showcase card -->
            <div class="relative bg-white p-8 sm:p-10 border border-slate-100 rounded-3xl shadow-xl shadow-slate-100 overflow-hidden group">
                <div class="absolute top-0 right-0 h-32 w-32 bg-indigo-50 rounded-bl-[100px] -z-0 group-hover:bg-indigo-100 transition-colors duration-500"></div>
                <div class="relative z-10 space-y-6">
                    <div class="inline-flex p-3 bg-indigo-50 rounded-2xl text-indigo-600">
                        <i data-lucide="shield-alert" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">The Verification Guarantee</h3>
                    <p class="text-sm leading-relaxed text-slate-550">
                        Our compliance agents conduct hands-on inspections of properties. We review ownership records, verify facilities (electricity grids, water pressure, backup power), evaluate guard security setup, and measure distance metrics from institutions. We only attach the <strong class="text-indigo-650 font-semibold">Verified</strong> badge when a property meets 100% of our safety checklists.
                    </p>
                    <div class="pt-4 border-t border-slate-100 flex items-center gap-4">
                        <div class="flex -space-x-2">
                            <span class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-slate-250 flex items-center justify-center text-xs font-semibold text-slate-700">M</span>
                            <span class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-indigo-105 flex items-center justify-center text-xs font-semibold text-indigo-700">V</span>
                            <span class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-purple-105 flex items-center justify-center text-xs font-semibold text-purple-700">T</span>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">Trusted by 10+ Universities in TZ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="bg-white py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Platform Features</h2>
            <p class="text-slate-500">Built to deliver complete control and peace of mind for both tenants and landlords.</p>
        </div>
        
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
            <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                <!-- Pillar 1 -->
                <div class="flex flex-col bg-slate-50 hover:bg-slate-100/50 p-8 rounded-3xl border border-slate-100/85 transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                    <dt class="flex items-center gap-x-3 text-base font-bold leading-7 text-slate-900">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white">
                            <i data-lucide="file-signature" class="w-5 h-5"></i>
                        </span>
                        Digital Leasing
                    </dt>
                    <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-500">
                        <p class="flex-auto text-sm">Leases are dynamically compiled based on landlord policies and local regulations. Sign them legally online instantly.</p>
                    </dd>
                </div>
                <!-- Pillar 2 -->
                <div class="flex flex-col bg-slate-50 hover:bg-slate-100/50 p-8 rounded-3xl border border-slate-100/85 transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                    <dt class="flex items-center gap-x-3 text-base font-bold leading-7 text-slate-900">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white">
                            <i data-lucide="calendar" class="w-5 h-5"></i>
                        </span>
                        Double Booking Shield
                    </dt>
                    <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-500">
                        <p class="flex-auto text-sm">Our automated room occupancy engine locks down bookings on the spot, preventing double-allocations on any room or bed.</p>
                    </dd>
                </div>
                <!-- Pillar 3 -->
                <div class="flex flex-col bg-slate-50 hover:bg-slate-100/50 p-8 rounded-3xl border border-slate-100/85 transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                    <dt class="flex items-center gap-x-3 text-base font-bold leading-7 text-slate-900">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white">
                            <i data-lucide="wrench" class="w-5 h-5"></i>
                        </span>
                        Maintenance Dispatch
                    </dt>
                    <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-slate-500">
                        <p class="flex-auto text-sm">Tenants upload photos of needed repairs. System tags the corresponding manager/owner and tracks resolution time.</p>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
