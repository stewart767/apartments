@extends('layouts.admin')

@section('title', 'Booking Applications Management')
@section('page_title', 'All Booking Applications')
@section('breadcrumb_title', 'Applications')

@section('content')
<div class="space-y-6" x-data="{ 
    detailModalOpen: false,
    selectedApp: null
}">
    
    <!-- Filter Panel -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.applications') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Search field -->
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Search Applications</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="App number, tenant name/email, property..."
                           class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                </div>
            </div>

            <!-- Status filter -->
            <div>
                <label for="status" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Booking Status</label>
                <select name="status" id="status" 
                        class="block w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-750 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Apply Filters</span>
                </button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.applications') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 px-4 rounded-xl transition-all flex items-center justify-center">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Applications Table Panel -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-900">Submitted Bookings Queue</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-650">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">App Number</th>
                        <th class="px-6 py-4">Applicant</th>
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Allocated Room/Bed</th>
                        <th class="px-6 py-4">Dues Quote</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($applications as $app)
                        @php
                            // Prepare simple JSON data for Alpine
                            $appJson = json_encode([
                                'application_number' => $app->application_number,
                                'tenant_name' => $app->tenant->name,
                                'tenant_email' => $app->tenant->email,
                                'tenant_phone' => $app->tenant->phone,
                                'tenant_whatsapp' => $app->tenant->whatsapp,
                                'tenant_gender' => $app->tenant->gender,
                                'tenant_dob' => $app->tenant->date_of_birth ? $app->tenant->date_of_birth->format('d M, Y') : 'N/A',
                                'property_name' => $app->property->property_name,
                                'room_number' => $app->room->room_number ?? 'N/A',
                                'bed_number' => $app->bed->bed_number ?? null,
                                'start_date' => \Carbon\Carbon::parse($app->start_date)->format('d M Y'),
                                'end_date' => \Carbon\Carbon::parse($app->end_date)->format('d M Y'),
                                'price' => number_format($app->price),
                                'billing_period' => $app->billing_period,
                                'status' => $app->status,
                                'tenant_notes' => $app->tenant_notes ?? 'No notes provided.',
                                'owner_notes' => $app->owner_notes ?? 'No notes provided.',
                                'is_student' => $app->tenant->tenantDetail->is_student ?? false,
                                'institution' => $app->tenant->tenantDetail->institution->name ?? 'N/A',
                                'reg_number' => $app->tenant->tenantDetail->student_reg_number ?? 'N/A',
                                'emergency' => $app->tenant->tenantDetail->emergency_contact ?? 'N/A',
                            ]);
                        @endphp
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-950">{{ $app->application_number }}</td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $app->tenant->name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $app->tenant->email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-900 font-semibold">{{ $app->property->property_name }}</td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                @if($app->bed)
                                    Room {{ $app->room->room_number }} / Bed {{ $app->bed->bed_number }}
                                @elseif($app->room)
                                    Room {{ $app->room->room_number }}
                                @else
                                    Entire Unit
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">TZS {{ number_format($app->price) }}/{{ $app->billing_period }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                    @if($app->status === 'approved') bg-emerald-50 text-emerald-700
                                    @elseif($app->status === 'pending') bg-blue-50 text-blue-700
                                    @elseif($app->status === 'under_review') bg-amber-50 text-amber-700
                                    @else bg-rose-50 text-rose-700 @endif">
                                    {{ $app->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button type="button" 
                                        @click="selectedApp = {{ $appJson }}; detailModalOpen = true;"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-750 text-xs font-bold rounded-xl transition-all">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Details</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold">No booking applications found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

    <!-- Application Detail Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" 
         x-show="detailModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>
        <div class="bg-white rounded-3xl shadow-xl border border-slate-150 w-full max-w-2xl overflow-hidden" @click.away="detailModalOpen = false">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-900">Application Number: <span x-text="selectedApp ? selectedApp.application_number : ''" class="text-indigo-650"></span></h3>
                <button type="button" @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto" x-if="selectedApp">
                <!-- Grid for Applicant and Request details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Applicant Details -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-slate-450 uppercase tracking-wider">Applicant Information</h4>
                        <div class="bg-slate-50 rounded-2xl p-4 space-y-2.5 text-sm">
                            <div>
                                <span class="text-xs text-slate-400">Full Name</span>
                                <p class="font-bold text-slate-900" x-text="selectedApp?.tenant_name"></p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400">Email</span>
                                <p class="font-semibold text-slate-800" x-text="selectedApp?.tenant_email"></p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400">Phone & WhatsApp</span>
                                <p class="text-slate-800">
                                    Phone: <span x-text="selectedApp?.tenant_phone"></span><br>
                                    WhatsApp: <span x-text="selectedApp?.tenant_whatsapp || 'N/A'"></span>
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400">Gender & DOB</span>
                                <p class="text-slate-800"><span class="capitalize" x-text="selectedApp?.tenant_gender"></span>, born <span x-text="selectedApp?.tenant_dob"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-slate-450 uppercase tracking-wider">Accommodation Requested</h4>
                        <div class="bg-slate-50 rounded-2xl p-4 space-y-2.5 text-sm">
                            <div>
                                <span class="text-xs text-slate-400">Property</span>
                                <p class="font-bold text-slate-900" x-text="selectedApp?.property_name"></p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400">Allocation Target</span>
                                <p class="font-semibold text-slate-800">
                                    Room: <span x-text="selectedApp?.room_number"></span>
                                    <template x-if="selectedApp?.bed_number">
                                        <span> / Bed Space: <span x-text="selectedApp?.bed_number"></span></span>
                                    </template>
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400">Duration Dates</span>
                                <p class="text-slate-800"><span x-text="selectedApp?.start_date"></span> to <span x-text="selectedApp?.end_date"></span></p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400">Cost Rate</span>
                                <p class="font-bold text-indigo-650">TZS <span x-text="selectedApp?.price"></span> / <span class="capitalize" x-text="selectedApp?.billing_period"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Education profile if student -->
                <template x-if="selectedApp?.is_student">
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-450 uppercase tracking-wider">Student Profile Details</h4>
                        <div class="bg-slate-50 rounded-2xl p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-slate-400">Institution</span>
                                <p class="font-semibold text-slate-850" x-text="selectedApp?.institution"></p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400">Registration Number</span>
                                <p class="font-semibold text-slate-850" x-text="selectedApp?.reg_number"></p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400">Emergency Contact</span>
                                <p class="font-semibold text-slate-850" x-text="selectedApp?.emergency"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Notes -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-slate-450 uppercase tracking-wider">Application Notes & Decision Log</h4>
                    <div class="bg-slate-55 border border-slate-100 rounded-2xl p-4 space-y-3 text-sm">
                        <div>
                            <span class="text-xs font-bold text-slate-450 uppercase">Applicant Message:</span>
                            <p class="text-slate-650 italic mt-1" x-text="selectedApp?.tenant_notes"></p>
                        </div>
                        <div class="border-t border-slate-100 pt-3">
                            <span class="text-xs font-bold text-slate-450 uppercase">Approver / Decision Notes:</span>
                            <p class="text-slate-650 italic mt-1" x-text="selectedApp?.owner_notes"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 p-6 bg-slate-50/50">
                <button type="button" @click="detailModalOpen = false" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-slate-200">
                    Close Details
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
