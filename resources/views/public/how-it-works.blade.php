@extends('layouts.public')

@section('title', 'How It Works - Simple, Safe & Verified Tenancy on RelianceHomes')
@section('meta_description', 'Discover how RelianceHomes connects students with verified hostels and apartments in Tanzania with 0% broker fee and instant digital tenancy agreements.')

@section('content')
<!-- Hero Section -->
<section class="bg-slate-950 py-20 lg:py-24 text-center relative overflow-hidden text-white">
    <div class="absolute inset-0 opacity-25 pointer-events-none">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-600 rounded-full blur-[140px]"></div>
    </div>
    
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10 space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold uppercase tracking-wider border border-indigo-400/20">
            <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-300"></i> Seamless Booking Journey
        </span>
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight">
            How RelianceHomes Works
        </h1>
        <p class="mt-4 text-slate-350 max-w-2xl mx-auto text-sm sm:text-base leading-relaxed">
            From discovering a room near your university to collecting your physical keys — we make renting simple, scam-free, and 100% digital.
        </p>
    </div>
</section>

<!-- Flow for Students & Tenants -->
<section class="py-20 bg-white border-b border-slate-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto space-y-2 mb-16">
            <span class="text-xs font-black uppercase tracking-wider text-indigo-600">For Tenants & Students</span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Rent Your Ideal Room in 4 Easy Steps</h2>
            <p class="text-xs sm:text-sm text-slate-500">No dalali fees, no walking under the blazing sun, no fake photos.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Step 1 -->
            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all space-y-4 relative">
                <span class="absolute top-4 right-4 text-3xl font-black text-indigo-100">01</span>
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20">
                    <i data-lucide="search" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">1. Search & Filter</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Filter verified hostels and rooms by your university campus, price range, private/shared bathroom, and amenities like Wi-Fi or AC.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all space-y-4 relative">
                <span class="absolute top-4 right-4 text-3xl font-black text-indigo-100">02</span>
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20">
                    <i data-lucide="check-square" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">2. Pick Bed & Apply</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Select your exact bed or room unit, specify your move-in date, and submit your reservation application in under 60 seconds.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all space-y-4 relative">
                <span class="absolute top-4 right-4 text-3xl font-black text-indigo-100">03</span>
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20">
                    <i data-lucide="file-check-2" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">3. Digital Contract</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Once the property manager approves your application, review and digitally sign your legally-binding Tenancy Lease Agreement.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all space-y-4 relative">
                <span class="absolute top-4 right-4 text-3xl font-black text-indigo-100">04</span>
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20">
                    <i data-lucide="key" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">4. Pay & Check In</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Pay securely via Lipa kwa M-Pesa or bank transfer with full receipt audit trail. Show your digital pass and collect keys!
                </p>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('properties.search') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs sm:text-sm shadow-xl shadow-indigo-600/20 transition-all hover:scale-105">
                <span>Start Searching Stays</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

<!-- Flow for Property Owners -->
<section class="py-20 bg-slate-50 border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto space-y-2 mb-16">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-600">For Property Owners & Landlords</span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Fill 100% of Your Vacancies Fast</h2>
            <p class="text-xs sm:text-sm text-slate-500">Reach verified student tenants directly and manage rent invoices effortlessly.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div class="p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                    <i data-lucide="building" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">1. List Rooms or Beds</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Configure your property, define buildings, floors, rooms, and individual bed pricing with photos and amenities.
                </p>
            </div>

            <div class="p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">2. Get Verified Badge</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Our inspection team reviews property standards and awards the "Physically Verified" badge, tripling booking conversion.
                </p>
            </div>

            <div class="p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">3. Automated Rent Collection</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Review tenant profiles, approve applications, generate contracts, and receive payments straight to your mobile money or bank.
                </p>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs sm:text-sm shadow-lg transition-all hover:scale-105">
                <span>List Your Property Today</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

<!-- FAQ Accordion (Interactive Alpine.js) -->
<section class="py-20 bg-white">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-8"
         x-data="{ activeFaq: null }">
        <div class="text-center space-y-2">
            <span class="text-xs font-black uppercase tracking-wider text-indigo-600">Frequently Asked Questions</span>
            <h2 class="text-3xl font-black text-slate-900">Common Questions Answered</h2>
        </div>

        <div class="space-y-4 pt-4">
            <!-- FAQ 1 -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <button type="button" @click="activeFaq = (activeFaq === 1) ? null : 1"
                        class="w-full p-5 text-left font-bold text-sm text-slate-900 flex justify-between items-center bg-slate-50/50 hover:bg-slate-50">
                    <span>Are properties physically verified before being listed?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="activeFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="activeFaq === 1" x-transition class="p-5 text-xs text-slate-600 border-t border-slate-100 bg-white leading-relaxed">
                    Yes! Every property featuring the green "Physically Inspected" badge has undergone direct on-site physical auditing by our compliance officers to verify water, electricity, security gates, and room cleanliness.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <button type="button" @click="activeFaq = (activeFaq === 2) ? null : 2"
                        class="w-full p-5 text-left font-bold text-sm text-slate-900 flex justify-between items-center bg-slate-50/50 hover:bg-slate-50">
                    <span>How does payment work? Can I pay via M-Pesa?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="activeFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="activeFaq === 2" x-transition class="p-5 text-xs text-slate-600 border-t border-slate-100 bg-white leading-relaxed">
                    Yes! We support direct mobile money payments via Lipa kwa M-Pesa, Tigo Pesa, Airtel Money, Halopesa, as well as bank transfers. You receive instant digital payment receipts saved on your dashboard.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <button type="button" @click="activeFaq = (activeFaq === 3) ? null : 3"
                        class="w-full p-5 text-left font-bold text-sm text-slate-900 flex justify-between items-center bg-slate-50/50 hover:bg-slate-50">
                    <span>Do I have to pay any middleman or dalali fee?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="activeFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="activeFaq === 3" x-transition class="p-5 text-xs text-slate-600 border-t border-slate-100 bg-white leading-relaxed">
                    No! RelianceHomes charges 0% broker fee. You deal directly with verified property managers, saving you the typical 1-month rent commission charged by traditional street brokers.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
