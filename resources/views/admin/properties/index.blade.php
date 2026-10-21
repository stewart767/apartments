@extends('layouts.admin')

@section('title', 'Properties Management')
@section('page_title', 'Properties Administration')
@section('breadcrumb_title', 'Properties')

@section('content')
<div class="space-y-6" x-data="{ verifyModalOpen: false, actionUrl: '', propertyName: '', currentNotes: '', currentStatus: 'pending' }">
    
    <!-- Filter Panel -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.properties') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Search field -->
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Search Properties</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Property name, owner name or email..."
                           class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                </div>
            </div>

            <!-- Verification Status filter -->
            <div>
                <label for="verification_status" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Verification Status</label>
                <select name="verification_status" id="verification_status" 
                        class="block w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('verification_status') === 'approved' ? 'selected' : '' }}>Approved (Verified)</option>
                    <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="changes_requested" {{ request('verification_status') === 'changes_requested' ? 'selected' : '' }}>Changes Requested</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-750 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Apply Filters</span>
                </button>
                @if(request()->anyFilled(['search', 'verification_status', 'status']))
                    <a href="{{ route('admin.properties') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 px-4 rounded-xl transition-all flex items-center justify-center">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Properties Table Panel -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-900">Registered Properties Listings</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-650">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Owner</th>
                        <th class="px-6 py-4">Location</th>
                        <th class="px-6 py-4">Structure & Price</th>
                        <th class="px-6 py-4">Verification</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($properties as $prop)
                        <tr>
                            <!-- Property info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($prop->primaryImage)
                                        <img src="{{ asset('storage/' . $prop->primaryImage->file_path) }}" alt="" class="w-12 h-12 object-cover rounded-xl border border-slate-150">
                                    @else
                                        <span class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                                            <i data-lucide="building" class="w-5 h-5"></i>
                                        </span>
                                    @endif
                                    <div>
                                        <a href="{{ route('properties.show', $prop->id) }}" target="_blank" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors">
                                            {{ $prop->property_name }}
                                        </a>
                                        <p class="text-xs text-slate-400 mt-0.5 capitalize">{{ $prop->propertyType->name ?? 'Accommodation' }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Owner info -->
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $prop->owner->name }}</p>
                                    <p class="text-xs text-slate-450">{{ $prop->owner->email }}</p>
                                </div>
                            </td>
                            <!-- Location -->
                            <td class="px-6 py-4 text-xs text-slate-600">
                                {{ $prop->ward->name ?? 'N/A' }}, {{ $prop->district->name ?? 'N/A' }}, {{ $prop->region->name ?? 'N/A' }}
                            </td>
                            <!-- Pricing -->
                            <td class="px-6 py-4">
                                <div>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 capitalize">{{ str_replace('_', ' ', $prop->rent_structure) }}</span>
                                    @if($prop->price)
                                        <p class="font-bold text-indigo-650 mt-1">TZS {{ number_format($prop->price) }}/{{ $prop->billing_period }}</p>
                                    @else
                                        <p class="text-xs text-slate-400 mt-1">Varies per unit</p>
                                    @endif
                                </div>
                            </td>
                            <!-- Verification Status -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize inline-flex items-center gap-1
                                    @if($prop->verification_status === 'approved') bg-emerald-50 text-emerald-700
                                    @elseif($prop->verification_status === 'pending') bg-amber-50 text-amber-700
                                    @elseif($prop->verification_status === 'changes_requested') bg-blue-50 text-blue-700
                                    @else bg-rose-50 text-rose-700 @endif">
                                    @if($prop->verification_status === 'approved')
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                    @elseif($prop->verification_status === 'pending')
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                    @endif
                                    {{ str_replace('_', ' ', $prop->verification_status) }}
                                </span>
                                @if($prop->verification_notes)
                                    <p class="text-[10px] text-slate-450 mt-1 max-w-[180px] truncate" title="{{ $prop->verification_notes }}">
                                        Notes: {{ $prop->verification_notes }}
                                    </p>
                                @endif
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" 
                                            @click="verifyModalOpen = true; 
                                                    actionUrl = '{{ route('admin.properties.verify', $prop->id) }}'; 
                                                    propertyName = '{{ addslashes($prop->property_name) }}'; 
                                                    currentNotes = '{{ addslashes($prop->verification_notes ?? '') }}';
                                                    currentStatus = '{{ $prop->verification_status }}';"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-750 text-xs font-bold rounded-xl transition-all">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                        <span>Verify</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="building" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold">No registered properties match the filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($properties->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $properties->links() }}
            </div>
        @endif
    </div>

    <!-- Verification Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" 
         x-show="verifyModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>
        <div class="bg-white rounded-3xl shadow-xl border border-slate-150 w-full max-w-lg overflow-hidden" @click.away="verifyModalOpen = false">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-900">Verify Property: <span x-text="propertyName" class="text-indigo-650"></span></h3>
                <button type="button" @click="verifyModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form :action="actionUrl" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Verification Decision</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="border rounded-xl p-3 flex flex-col items-center gap-1.5 cursor-pointer transition-all hover:bg-slate-50"
                               :class="currentStatus === 'approved' ? 'border-emerald-500 bg-emerald-50/20 text-emerald-800' : 'border-slate-200'">
                            <input type="radio" name="status" value="approved" x-model="currentStatus" class="sr-only">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                            <span class="text-xs font-bold">Approve</span>
                        </label>
                        <label class="border rounded-xl p-3 flex flex-col items-center gap-1.5 cursor-pointer transition-all hover:bg-slate-50"
                               :class="currentStatus === 'changes_requested' ? 'border-blue-500 bg-blue-50/20 text-blue-800' : 'border-slate-200'">
                            <input type="radio" name="status" value="changes_requested" x-model="currentStatus" class="sr-only">
                            <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                            <span class="text-xs font-bold">Need Changes</span>
                        </label>
                        <label class="border rounded-xl p-3 flex flex-col items-center gap-1.5 cursor-pointer transition-all hover:bg-slate-50"
                               :class="currentStatus === 'rejected' ? 'border-rose-500 bg-rose-50/20 text-rose-800' : 'border-slate-200'">
                            <input type="radio" name="status" value="rejected" x-model="currentStatus" class="sr-only">
                            <i data-lucide="x-circle" class="w-5 h-5 text-rose-600"></i>
                            <span class="text-xs font-bold">Reject</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Verification Notes / Feedback</label>
                    <textarea id="notes" name="notes" rows="4" x-model="currentNotes" placeholder="Write feedback, reasons for rejection, or details of requested changes..."
                              class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600"></textarea>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="verifyModalOpen = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-650 hover:bg-slate-50 transition-all">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-750 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-50">Save Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
