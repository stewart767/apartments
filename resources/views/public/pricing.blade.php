@extends('layouts.public')

@section('title', 'Pricing Plans & Property Management Packages - RelianceHomes Tanzania')
@section('meta_description', 'Explore flexible, transparent property management SaaS subscription plans for landlords, hostels, and apartment operators in Tanzania starting from TSh 25,000/month.')

@section('content')
<div class="bg-slate-50 min-h-screen py-16" x-data="{ billing: 'monthly' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60 uppercase tracking-wider mb-4">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Commercial SaaS Pricing
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight">
                Predictable Pricing for <br class="hidden sm:block"/>
                <span class="bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-800 bg-clip-text text-transparent">Tanzanian Property Operators</span>
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed">
                Streamline rent collections, occupancy tracking, tenant screening, and room inventory.
                Free for tenants to browse. Transparent monthly and annual plans for property owners.
            </p>

            <!-- Billing Interval Switcher -->
            <div class="mt-8 flex items-center justify-center gap-4">
                <span class="text-sm font-medium" :class="billing === 'monthly' ? 'text-slate-900 font-semibold' : 'text-slate-500'">Monthly Billing</span>
                <button type="button" 
                        @click="billing = billing === 'monthly' ? 'annual' : 'monthly'"
                        class="relative inline-flex h-7 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-indigo-600 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                    <span class="sr-only">Toggle billing interval</span>
                    <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                          :class="billing === 'annual' ? 'translate-x-7' : 'translate-x-0'"></span>
                </button>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium" :class="billing === 'annual' ? 'text-slate-900 font-semibold' : 'text-slate-500'">Annual Billing</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        2 Months Free (Save ~17%)
                    </span>
                </div>
            </div>
        </div>

        <!-- Plan Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-20 items-stretch">
            @foreach($plans as $plan)
                @php
                    $isPopular = $plan->slug === 'hostel';
                @endphp
                <div class="relative flex flex-col rounded-2xl bg-white border {{ $isPopular ? 'border-indigo-600 shadow-xl shadow-indigo-100/50 ring-2 ring-indigo-600' : 'border-slate-200 shadow-sm' }} p-7 transition-all duration-200 hover:shadow-md">
                    @if($isPopular)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-3 py-1 rounded-full text-xs font-bold tracking-wide shadow-sm">
                            MOST POPULAR IN TANZANIA
                        </div>
                    @endif

                    <div class="mb-5">
                        <h3 class="text-xl font-bold text-slate-900">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1 min-h-[32px]">{{ $plan->description }}</p>
                    </div>

                    <!-- Pricing Display -->
                    <div class="mb-6 pb-6 border-b border-slate-100">
                        <div x-show="billing === 'monthly'">
                            <span class="text-3xl font-extrabold text-slate-900">TSh {{ number_format($plan->price_monthly, 0) }}</span>
                            <span class="text-xs text-slate-500 font-medium">/ month</span>
                        </div>
                        <div x-show="billing === 'annual'" x-cloak>
                            <span class="text-3xl font-extrabold text-slate-900">TSh {{ number_format($plan->price_annual, 0) }}</span>
                            <span class="text-xs text-slate-500 font-medium">/ year</span>
                            <p class="text-xs text-emerald-600 font-semibold mt-1">
                                Save TSh {{ number_format($plan->annual_savings, 0) }} / year
                            </p>
                        </div>
                    </div>

                    <!-- Resource Limits -->
                    <div class="bg-slate-50 rounded-xl p-3.5 mb-6 text-xs text-slate-700 space-y-2 border border-slate-100">
                        <div class="flex items-center justify-between font-semibold">
                            <span class="flex items-center gap-1.5"><i data-lucide="layers" class="w-3.5 h-3.5 text-indigo-600"></i> Rental Units:</span>
                            <span class="text-indigo-900 font-bold">Up to {{ $plan->unit_limit }} units</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i data-lucide="building" class="w-3.5 h-3.5 text-slate-500"></i> Properties:</span>
                            <span>Max {{ $plan->property_limit }} property</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i data-lucide="users" class="w-3.5 h-3.5 text-slate-500"></i> Staff Accounts:</span>
                            <span>{{ $plan->staff_limit }} staff account(s)</span>
                        </div>
                    </div>

                    <!-- Feature List -->
                    <div class="flex-1 mb-8">
                        <p class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Included Capabilities:</p>
                        <ul class="space-y-2.5 text-xs text-slate-600">
                            @if($plan->hasFeature('basic_property_management'))
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Public marketplace listing & search</span>
                                </li>
                            @endif
                            @if($plan->hasFeature('booking_applications'))
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Tenant applications & reservation locks</span>
                                </li>
                            @endif
                            @if($plan->hasFeature('contracts_digital'))
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Digital lease agreements & PDF generation</span>
                                </li>
                            @endif
                            @if($plan->hasFeature('payment_verification'))
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>M-Pesa/Bank receipt verification workflows</span>
                                </li>
                            @endif
                            @if($plan->hasFeature('maintenance_tickets'))
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Maintenance ticketing with photo uploads</span>
                                </li>
                            @endif
                            @if($plan->hasFeature('checkin_checkout'))
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Digital Check-in, inventory handover & checkout</span>
                                </li>
                            @endif
                            @if($plan->hasFeature('room_bed_allocation'))
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Building -> Floor -> Room -> Bed hierarchy</span>
                                </li>
                            @endif
                            @if($plan->hasFeature('multi_staff_accounts'))
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Manager & receptionist assigned permissions</span>
                                </li>
                            @endif
                            @if($plan->slug === 'enterprise')
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Custom domain & API integration support</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                    <span>Dedicated account manager & 24/7 SLA</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- CTA Button -->
                    @auth
                        @if(auth()->user()->isOwner() || auth()->user()->isAgent())
                            <a href="{{ route('owner.subscription') }}" 
                               class="w-full py-2.5 px-4 text-center rounded-xl text-sm font-bold transition-colors {{ $isPopular ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-200' : 'bg-slate-900 text-white hover:bg-slate-800' }}">
                                Choose {{ $plan->name }}
                            </a>
                        @else
                            <a href="{{ route('register') }}?role=owner" 
                               class="w-full py-2.5 px-4 text-center rounded-xl text-sm font-bold transition-colors bg-slate-900 text-white hover:bg-slate-800">
                                Register as Landlord
                            </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}?role=owner" 
                           class="w-full py-2.5 px-4 text-center rounded-xl text-sm font-bold transition-colors {{ $isPopular ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-200' : 'bg-slate-900 text-white hover:bg-slate-800' }}">
                            Get Started
                        </a>
                    @endauth
                </div>
            @endforeach
        </div>

        <!-- Optional One-Time Onboarding Services Section -->
        <div class="mb-20">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200/60 uppercase tracking-wider mb-2">
                    <i data-lucide="wrench" class="w-3.5 h-3.5"></i> Optional Setup & Training
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Professional Onboarding Services</h2>
                <p class="text-sm text-slate-600 mt-2">
                    Need hands-on assistance setting up your property data, importing past tenant files, or training your hostel reception team?
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($onboardingServices as $srv)
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-slate-900 text-lg">{{ $srv['title'] }}</h4>
                                <span class="text-xs px-2.5 py-1 rounded-md bg-indigo-50 font-semibold text-indigo-700">{{ $srv['turnaround'] }}</span>
                            </div>
                            <p class="text-xs text-slate-600 mb-4">{{ $srv['description'] }}</p>
                            <p class="text-xs text-slate-500 font-medium"><strong>Best for:</strong> {{ $srv['ideal_for'] }}</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-sm font-extrabold text-slate-900">{{ $srv['price'] }}</span>
                            <a href="#enterprise-quote-form" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Request Quotation &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Featured Listing Promotion Packages -->
        <div class="mb-20 bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl">
            <div class="max-w-3xl mb-8">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-indigo-200 border border-white/20 uppercase tracking-wider mb-3">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Marketplace Boost
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Promote Your Stays with Paid Featured Badges</h2>
                <p class="text-indigo-200 text-sm mt-2">
                    Reach up to 4x more prospective students and apartment seekers by boosting your listing to the top of regional search results.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($featuredListingPricing as $feat)
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-bold text-white text-base">{{ $feat['title'] }}</h4>
                                <span class="text-xs px-2 py-0.5 rounded bg-indigo-500/30 text-indigo-200 font-semibold">{{ $feat['duration'] }}</span>
                            </div>
                            <p class="text-2xl font-extrabold text-amber-400 mb-4">{{ $feat['price'] }}</p>
                            <ul class="space-y-2 text-xs text-indigo-100">
                                @foreach($feat['features'] as $f)
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-amber-400"></i>
                                        <span>{{ $f }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="mt-6 pt-4 border-t border-white/10">
                            <span class="text-[11px] text-indigo-300">Available inside Owner Property Dashboard</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- FAQs Accordion -->
        <div class="max-w-4xl mx-auto mb-20">
            <div class="text-center mb-10">
                <h3 class="text-2xl font-bold text-slate-900">Frequently Asked Questions</h3>
                <p class="text-sm text-slate-500 mt-1">Everything you need to know about billing, subscription limits, and payments.</p>
            </div>

            <div class="space-y-4">
                @foreach($faqs as $faq)
                    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left font-bold text-slate-900 text-sm sm:text-base">
                            <span>{{ $faq['q'] }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-cloak class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Enterprise / Custom Quotation Contact Form -->
        <div id="enterprise-quote-form" class="max-w-3xl mx-auto bg-white rounded-3xl border border-slate-200 p-8 sm:p-10 shadow-lg">
            <div class="text-center mb-8">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60 uppercase tracking-wider mb-2">
                    <i data-lucide="message-square" class="w-3.5 h-3.5"></i> Custom Quotations
                </span>
                <h3 class="text-2xl font-extrabold text-slate-900">Managing Over 100 Units or Multi-Campus Hostels?</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Submit your accommodation portfolio size for customized volume pricing, API access, and tailored staff onboarding.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('pricing.quote') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Your Full Name *</label>
                        <input type="text" name="name" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="e.g. Salum Mwinyi">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address *</label>
                        <input type="email" name="email" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="salum@example.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone / WhatsApp *</label>
                        <input type="text" name="phone" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="+255 7XX XXX XXX">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Organization / Hostel Name</label>
                        <input type="text" name="company_name" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="e.g. Victoria Student Accommodation">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Estimated Total Beds / Units *</label>
                        <select name="estimated_units" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                            <option value="60 - 150 units">60 - 150 units / beds</option>
                            <option value="150 - 300 units">150 - 300 units / beds</option>
                            <option value="300 - 600 units">300 - 600 units / beds</option>
                            <option value="600+ units">600+ units / beds (Enterprise Multi-Campus)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Primary Accommodation Type</label>
                        <input type="text" name="property_types" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="e.g. Student Hostels, Studio Flats">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Custom Requirements / Notes</label>
                    <textarea name="message" rows="3" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="Let us know if you need on-site staff training, historical data migration, or integration..."></textarea>
                </div>

                <button type="submit" class="w-full py-3 px-6 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200">
                    Submit Quotation Request
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
