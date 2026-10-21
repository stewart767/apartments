@extends('layouts.admin')

@section('title', 'Review Booking Application')
@section('page_title', 'Application Details')
@section('breadcrumb_title', 'Review')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Details Panel (Left 2 cols) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Applicant Details -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i data-lucide="user" class="w-4 h-4"></i></span>
                <span>Applicant Information</span>
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Full Name</span>
                    <span class="block font-bold text-slate-900 mt-1">{{ $application->tenant->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Email Address</span>
                    <span class="block text-slate-700 mt-1">{{ $application->tenant->email }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Phone & WhatsApp</span>
                    <span class="block text-slate-700 mt-1">
                        Phone: {{ $application->tenant->phone }}
                        @if($application->tenant->whatsapp)
                            <br>WhatsApp: {{ $application->tenant->whatsapp }}
                        @endif
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Gender & Date of Birth</span>
                    <span class="block text-slate-700 mt-1 capitalize">
                        {{ $application->tenant->gender }}, born {{ $application->tenant->date_of_birth ? $application->tenant->date_of_birth->format('d M, Y') : 'N/A' }}
                    </span>
                </div>
            </div>

            <!-- Student Profile details if student -->
            @if($application->tenant->tenantDetail)
                <div class="border-t border-slate-100 mt-6 pt-6 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Education Status</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Is Student?</span>
                            <span class="block font-semibold mt-1">
                                {{ $application->tenant->tenantDetail->is_student ? 'Yes, Student' : 'No, Professional' }}
                            </span>
                        </div>
                        @if($application->tenant->tenantDetail->is_student)
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Institution</span>
                                <span class="block text-slate-700 mt-1">
                                    {{ $application->tenant->tenantDetail->institution->name ?? 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Reg Number</span>
                                <span class="block text-slate-700 mt-1">
                                    {{ $application->tenant->tenantDetail->student_reg_number ?? 'N/A' }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Emergency Contact</span>
                            <span class="block text-slate-700 mt-1">
                                {{ $application->tenant->tenantDetail->emergency_contact ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Target Accommodation Selection details -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i data-lucide="building" class="w-4 h-4"></i></span>
                <span>Accommodation Requested</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Property Name</span>
                    <span class="block font-bold text-slate-900 mt-1">{{ $application->property->property_name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Allocation Target</span>
                    <span class="block text-slate-700 mt-1">
                        @if($application->bed)
                            Room {{ $application->room->room_number }} / Bed Space {{ $application->bed->bed_number }}
                        @elseif($application->room)
                            Room Unit {{ $application->room->room_number }}
                        @else
                            Entire Property
                        @endif
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Duration dates</span>
                    <span class="block text-slate-700 mt-1">
                        {{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($application->end_date)->format('d M Y') }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Cost rate</span>
                    <span class="block font-bold text-indigo-650 mt-1">TZS {{ number_format($application->price) }}/{{ $application->billing_period }}</span>
                </div>
            </div>
            
            @if($application->tenant_notes)
                <div class="border-t border-slate-100 mt-6 pt-6">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Applicant Notes</span>
                    <p class="text-sm text-slate-600 mt-2 italic">"{{ $application->tenant_notes }}"</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Review Action Sidebar (Right 1 col) -->
    <div class="lg:col-span-1">
        <div class="sticky top-20 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-slate-950">Review Decision</h3>

            @if($application->status === 'pending')
                @php
                    $processRoute = route('manager.applications.process', $application->id);
                @endphp
                <form action="{{ $processRoute }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="owner_notes" class="block text-xs font-semibold text-slate-700">Add Decision Notes</label>
                        <textarea id="owner_notes" name="owner_notes" rows="3" placeholder="e.g. Approved. Contract terms generated."
                                  class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600"></textarea>
                    </div>

                    <div class="space-y-2 pt-2">
                        <button type="submit" name="status" value="approved" 
                                class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-emerald-50">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Approve Booking</span>
                        </button>
                        
                        <button type="submit" name="status" value="rejected" 
                                class="w-full inline-flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold py-2.5 px-4 rounded-xl transition-all border border-rose-100">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span>Reject Booking</span>
                        </button>
                    </div>
                </form>
            @else
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-center space-y-2">
                    <span class="inline-flex px-3 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded-full capitalize">{{ $application->status }}</span>
                    <p class="text-xs text-slate-500">This application was processed. Decision notes: "{{ $application->owner_notes ?? 'No notes provided.' }}"</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
