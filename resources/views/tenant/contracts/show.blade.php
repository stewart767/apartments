@extends('layouts.admin')

@section('title', 'Review Tenancy Agreement')
@section('page_title', 'Lease Contract Terms')
@section('breadcrumb_title', 'Agreement')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Details Panel (Left 2 cols) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Header Info Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lease Reference</span>
                <h3 class="text-lg font-bold text-slate-900 mt-0.5">{{ $contract->contract_number }}</h3>
                <p class="text-xs text-slate-500 mt-1">Property: <span class="font-semibold text-slate-800">{{ $contract->property->property_name }}</span></p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-xs font-bold rounded-full capitalize
                    @if($contract->status === 'active') bg-emerald-50 text-emerald-700
                    @elseif($contract->status === 'pending_acceptance') bg-amber-50 text-amber-700
                    @elseif($contract->status === 'accepted') bg-blue-50 text-blue-700
                    @else bg-slate-50 text-slate-700 @endif">
                    {{ $contract->status }}
                </span>
                
                @if($contract->tenant_accepted)
                    <a href="{{ route('contracts.download', $contract->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all border border-slate-200">
                        <i data-lucide="download" class="w-4 h-4 text-slate-500"></i>
                        <span>Download PDF Copy</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Lease Specifications -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i data-lucide="info" class="w-4 h-4"></i></span>
                <span>Tenancy Details</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Lessor (Owner)</span>
                    <span class="block font-bold text-slate-900 mt-1">{{ $contract->property->owner->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Lessee (Tenant)</span>
                    <span class="block font-bold text-slate-900 mt-1">{{ $contract->tenant->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Duration</span>
                    <span class="block text-slate-700 mt-1 font-semibold">
                        {{ \Carbon\Carbon::parse($contract->start_date)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($contract->end_date)->format('F d, Y') }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Physical Allocation</span>
                    <span class="block text-slate-700 mt-1 font-semibold">
                        @if($contract->bed)
                            Room {{ $contract->room->room_number }} / Bed Space {{ $contract->bed->bed_number }}
                        @elseif($contract->room)
                            Room {{ $contract->room->room_number }}
                        @else
                            Entire Property
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Financial Obligations -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl"><i data-lucide="coins" class="w-4 h-4"></i></span>
                <span>Financial Dues Summary</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Rent Dues Rate</span>
                    <span class="block text-base font-extrabold text-slate-900 mt-1">TZS {{ number_format($contract->rent_amount) }} / {{ $contract->billing_frequency }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Refundable Security Deposit</span>
                    <span class="block text-base font-extrabold text-slate-900 mt-1">TZS {{ number_format($contract->deposit_amount) }}</span>
                </div>
            </div>
        </div>

        <!-- Rules & Terms -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">General Rules & Code of Conduct</h4>
                <div class="text-xs text-slate-600 leading-relaxed bg-slate-50 border border-slate-100 p-4 rounded-2xl whitespace-pre-line">
                    {{ $contract->rules }}
                </div>
            </div>
            
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Legal Tenancy Terms & Conditions</h4>
                <div class="text-xs text-slate-600 leading-relaxed bg-slate-50 border border-slate-100 p-4 rounded-2xl whitespace-pre-line">
                    {{ $contract->terms_and_conditions }}
                </div>
            </div>
        </div>

        @if(in_array($contract->status, ['active', 'expired', 'terminated']) && !$contract->reviews()->where('tenant_id', auth()->id())->exists())
            <!-- Submit Review Card -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl"><i data-lucide="star" class="w-4 h-4"></i></span>
                    <span>Submit Tenancy Review & Rating</span>
                </h3>
                
                <p class="text-xs text-slate-500">As a verified tenant, your feedback helps other students find suitable accommodation. Please rate your experience:</p>

                <form action="{{ route('tenant.contracts.review.store', $contract->id) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Cleanliness -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">Cleanliness</label>
                            <select name="cleanliness" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>

                        <!-- Security -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">Security</label>
                            <select name="security" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>

                        <!-- Water -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">Water Supply</label>
                            <select name="water" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>

                        <!-- Electricity -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">Electricity Reliability</label>
                            <select name="electricity" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>

                        <!-- Management -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">Management & Support</label>
                            <select name="management" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">Location Convenience</label>
                            <select name="location" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>

                        <!-- Value for Money -->
                        <div class="space-y-1 sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">Value for Money</label>
                            <select name="value_for_money" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Written Feedback / Comment</label>
                        <textarea name="comment" rows="4" placeholder="Share your experience staying here (e.g. noise levels, nearby amenities, room state)..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650"></textarea>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-all shadow-md shadow-indigo-100">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Submit My Review</span>
                    </button>
                </form>
            </div>
        @elseif(in_array($contract->status, ['active', 'expired', 'terminated']) && $contract->reviews()->where('tenant_id', auth()->id())->exists())
            <!-- Review Already Submitted -->
            @php
                $myReview = $contract->reviews()->where('tenant_id', auth()->id())->first();
            @endphp
            <div class="bg-emerald-50/40 border border-emerald-100/50 rounded-3xl p-6 md:p-8 space-y-3">
                <div class="flex items-center gap-2 text-emerald-800 font-bold">
                    <span class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
                    <span>Review Submitted</span>
                </div>
                <p class="text-xs text-emerald-700 leading-relaxed">You submitted a review for this property, rating it <strong class="text-emerald-950">{{ $myReview->average_rating }} / 5.0</strong>. Thank you for sharing your feedback with the community!</p>
            </div>
        @endif
    </div>

    <!-- Right Acceptance Sidebar -->
    <div class="lg:col-span-1">
        <div class="sticky top-20 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-slate-950">Contract Status</h3>

            @if($contract->status === 'pending_acceptance')
                <div class="p-4 bg-amber-50/50 border border-amber-100/50 rounded-2xl text-slate-700 text-xs leading-relaxed space-y-3">
                    <p class="font-bold text-slate-900">Lease Awaiting Signature</p>
                    <p>By clicking accept below, you digitally sign and bind yourself to the terms, code of conduct, and financial payment dues of this residential lease agreement.</p>
                </div>

                <form action="{{ route('tenant.contracts.accept', $contract->id) }}" method="POST" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-all shadow-md shadow-indigo-100">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                        <span>Digitally Sign & Accept</span>
                    </button>
                </form>
            @elseif($contract->status === 'accepted')
                <div class="p-4 bg-blue-50/60 border border-blue-100/50 rounded-2xl space-y-3 text-xs leading-relaxed text-slate-700">
                    <p class="font-bold text-slate-900 text-blue-900">Lease Accepted &bull; Pending Payment</p>
                    <p>You have signed the lease contract! To complete your reservation and activate the tenancy, please submit the deposit and first rent payment invoices.</p>
                    <a href="{{ route('tenant.payments') }}" class="inline-flex w-full justify-center items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-all mt-2">
                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                        <span>Pay Initial Invoices</span>
                    </a>
                </div>
            @elseif($contract->status === 'active')
                <div class="p-4 bg-emerald-50/50 border border-emerald-100/50 rounded-2xl text-slate-700 text-xs leading-relaxed space-y-2">
                    <p class="font-bold text-emerald-800 flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4"></i> Active Lease Tenancy</p>
                    <p>This lease is officially active. You have checked in to your room allocation. Please follow all rules listed in section 5.</p>
                </div>
            @else
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-center text-xs text-slate-400 capitalize">
                    Lease Agreement: {{ $contract->status }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
