@extends('layouts.admin')

@section('title', 'Front Desk Operations')
@section('page_title', 'Check-In & Check-Out Management')
@section('breadcrumb_title', 'Front Desk')

@section('content')
<div class="space-y-6" x-data="{ checkinId: null, checkoutId: null, defaultDeposit: 0 }">
    
    <!-- Tab Controls -->
    @php
        $tab = request('tab', 'checkin');
    @endphp
    <div class="flex border-b border-slate-200 gap-4">
        <a href="{{ route('manager.checkins', ['tab' => 'checkin']) }}" 
           class="py-2.5 px-4 text-sm font-semibold border-b-2 transition-all {{ $tab === 'checkin' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Awaiting Check-In
        </a>
        <a href="{{ route('manager.checkins', ['tab' => 'checkout']) }}" 
           class="py-2.5 px-4 text-sm font-semibold border-b-2 transition-all {{ $tab === 'checkout' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Active Tenancies (Awaiting Check-Out)
        </a>
    </div>

    <!-- Data Container -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-650">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Contract ID</th>
                        <th class="px-6 py-4">Tenant</th>
                        <th class="px-6 py-4">Allocation</th>
                        <th class="px-6 py-4">StartDate</th>
                        <th class="px-6 py-4">Rent Dues</th>
                        <th class="px-6 py-4">Deposit</th>
                        <th class="px-6 py-4 font-bold text-right pr-8">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($contracts as $con)
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-950">{{ $con->contract_number }}</td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $con->tenant->name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $con->tenant->phone }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                                @if($con->bed)
                                    Room {{ $con->room->room_number }} / Bed {{ $con->bed->bed_number }}
                                @elseif($con->room)
                                    Room {{ $con->room->room_number }}
                                @else
                                    Entire House
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs">{{ \Carbon\Carbon::parse($con->start_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4">TZS {{ number_format($con->rent_amount) }}</td>
                            <td class="px-6 py-4">TZS {{ number_format($con->deposit_amount) }}</td>
                            <td class="px-6 py-4 text-right pr-8">
                                @if($tab === 'checkin')
                                    <button @click="checkinId = '{{ $con->id }}'; defaultDeposit = '{{ $con->deposit_amount }}'"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm shadow-indigo-100">
                                        <i data-lucide="door-open" class="w-3.5 h-3.5"></i>
                                        <span>Check-In</span>
                                    </button>
                                @else
                                    <button @click="checkoutId = '{{ $con->id }}'"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold rounded-xl transition-all border border-rose-100">
                                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                        <span>Check-Out</span>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- CHECKIN MODAL TEMPLATE -->
                        <template x-if="checkinId == '{{ $con->id }}'">
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4 overflow-y-auto">
                                <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 max-w-lg w-full shadow-2xl space-y-5 my-8"
                                     @click.away="checkinId = null">
                                    
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                        <h3 class="text-base font-bold text-slate-950">Record Tenant Check-In</h3>
                                        <button @click="checkinId = null" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                                    </div>

                                    <form action="{{ route('manager.checkins.in', $con->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700">Check-In Date</label>
                                                <input type="date" name="check_in_date" required value="{{ date('Y-m-d') }}"
                                                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700">Deposit TZS</label>
                                                <input type="number" name="deposit_received" required :value="defaultDeposit"
                                                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700">Keys Handover (Count)</label>
                                            <input type="number" name="keys_count" required value="1" min="1"
                                                   class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                        </div>

                                        <!-- Baseline Property Assets Checklist -->
                                        @if($con->room && $con->room->propertyAssets->isNotEmpty())
                                            <div class="space-y-2 mt-4 bg-slate-50 p-4 border border-slate-200 rounded-2xl max-h-48 overflow-y-auto">
                                                <span class="block text-xs font-bold text-indigo-900 uppercase tracking-wider mb-2">Room Property Assets Checklist</span>
                                                @foreach($con->room->propertyAssets as $asset)
                                                    <div class="flex items-center justify-between text-xs border-b border-slate-200/50 pb-2 mb-2">
                                                        <span class="font-bold text-slate-800">{{ $asset->asset_name }} ({{ $asset->serial_number ?: 'No SN' }})</span>
                                                        <select name="property_assets[{{ $asset->id }}]" class="border border-slate-200 rounded px-1.5 py-0.5 text-xs bg-white">
                                                            <option value="new">New</option>
                                                            <option value="excellent">Excellent</option>
                                                            <option value="good" selected>Good</option>
                                                            <option value="fair">Fair</option>
                                                            <option value="damaged">Damaged</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Baseline Tenant Assets Checklist -->
                                        @if($con->tenant->tenantAssets->isNotEmpty())
                                            <div class="space-y-2 mt-4 bg-slate-50 p-4 border border-slate-200 rounded-2xl max-h-48 overflow-y-auto">
                                                <span class="block text-xs font-bold text-indigo-900 uppercase tracking-wider mb-2">Tenant Registered Assets Checklist</span>
                                                @foreach($con->tenant->tenantAssets as $tAsset)
                                                    <div class="flex items-center justify-between text-xs border-b border-slate-200/50 pb-2 mb-2">
                                                        <span class="font-bold text-slate-800">{{ $tAsset->asset_name }} ({{ $tAsset->category }})</span>
                                                        <select name="tenant_assets[{{ $tAsset->id }}]" class="border border-slate-200 rounded px-1.5 py-0.5 text-xs bg-white">
                                                            <option value="new">New</option>
                                                            <option value="excellent">Excellent</option>
                                                            <option value="good" selected>Good</option>
                                                            <option value="fair">Fair</option>
                                                            <option value="damaged">Damaged</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700">Room Condition Inspection Notes</label>
                                            <textarea name="room_condition_notes" rows="2" placeholder="e.g. All furniture present and in good condition."
                                                      class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600"></textarea>
                                        </div>

                                        <div class="pt-4 border-t border-slate-100 flex gap-3">
                                            <button type="button" @click="checkinId = null" 
                                                    class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                                            <button type="submit" 
                                                    class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-50">Log Check-In</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>

                        <!-- CHECKOUT MODAL TEMPLATE -->
                        <template x-if="checkoutId == '{{ $con->id }}'">
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4 overflow-y-auto">
                                <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 max-w-lg w-full shadow-2xl space-y-5 my-8"
                                     @click.away="checkoutId = null">
                                    
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                        <h3 class="text-base font-bold text-slate-950">Record Tenant Check-Out</h3>
                                        <button @click="checkoutId = null" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                                    </div>

                                    <form action="{{ route('manager.checkins.out', $con->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700">Check-Out Date</label>
                                                <input type="date" name="check_out_date" required value="{{ date('Y-m-d') }}"
                                                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700">Outstanding Settled?</label>
                                                <select name="outstanding_settled" required
                                                        class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 bg-white">
                                                    <option value="1">Yes, Settled</option>
                                                    <option value="0">No, Dues Remain</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Compare Property Assets Conditions -->
                                        @if($con->room && $con->room->propertyAssets->isNotEmpty())
                                            <div class="space-y-2 mt-4 bg-slate-50 p-4 border border-slate-200 rounded-2xl max-h-48 overflow-y-auto">
                                                <span class="block text-xs font-bold text-indigo-900 uppercase tracking-wider mb-2">Compare Property Assets Condition</span>
                                                @php
                                                    $baselineProp = $con->checkIn->inventory_json['property_assets'] ?? [];
                                                @endphp
                                                @foreach($con->room->propertyAssets as $asset)
                                                    @php
                                                        $baseCond = $baselineProp[$asset->id] ?? $asset->condition;
                                                    @endphp
                                                    <div class="flex items-center justify-between text-xs border-b border-slate-200/50 pb-2 mb-2">
                                                        <div class="flex flex-col">
                                                            <span class="font-bold text-slate-800">{{ $asset->asset_name }}</span>
                                                            <span class="text-[10px] text-slate-500">Checkin baseline: <strong class="uppercase text-slate-700">{{ $baseCond }}</strong></span>
                                                        </div>
                                                        <select name="checkout_property_assets[{{ $asset->id }}]" class="border border-slate-200 rounded px-1.5 py-0.5 text-xs bg-white">
                                                            <option value="new" {{ $asset->condition === 'new' ? 'selected' : '' }}>New</option>
                                                            <option value="excellent" {{ $asset->condition === 'excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="good" {{ $asset->condition === 'good' ? 'selected' : '' }}>Good</option>
                                                            <option value="fair" {{ $asset->condition === 'fair' ? 'selected' : '' }}>Fair</option>
                                                            <option value="damaged" {{ $asset->condition === 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                            <option value="needs_repair" {{ $asset->condition === 'needs_repair' ? 'selected' : '' }}>Needs Repair</option>
                                                            <option value="missing" {{ $asset->condition === 'missing' ? 'selected' : '' }}>Missing</option>
                                                            <option value="disposed" {{ $asset->condition === 'disposed' ? 'selected' : '' }}>Disposed</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Compare Tenant Assets Conditions -->
                                        @if($con->tenant->tenantAssets->isNotEmpty())
                                            <div class="space-y-2 mt-4 bg-slate-50 p-4 border border-slate-200 rounded-2xl max-h-48 overflow-y-auto">
                                                <span class="block text-xs font-bold text-indigo-900 uppercase tracking-wider mb-2">Compare Tenant Personal Assets Condition</span>
                                                @php
                                                    $baselineTen = $con->checkIn->inventory_json['tenant_assets'] ?? [];
                                                @endphp
                                                @foreach($con->tenant->tenantAssets as $tAsset)
                                                    @php
                                                        $baseCond = $baselineTen[$tAsset->id] ?? $tAsset->condition;
                                                    @endphp
                                                    <div class="flex items-center justify-between text-xs border-b border-slate-200/50 pb-2 mb-2">
                                                        <div class="flex flex-col">
                                                            <span class="font-bold text-slate-800">{{ $tAsset->asset_name }}</span>
                                                            <span class="text-[10px] text-slate-500">Checkin baseline: <strong class="uppercase text-slate-700">{{ $baseCond }}</strong></span>
                                                        </div>
                                                        <select name="checkout_tenant_assets[{{ $tAsset->id }}]" class="border border-slate-200 rounded px-1.5 py-0.5 text-xs bg-white">
                                                            <option value="new" {{ $tAsset->condition === 'new' ? 'selected' : '' }}>New</option>
                                                            <option value="excellent" {{ $tAsset->condition === 'excellent' ? 'selected' : '' }}>Excellent</option>
                                                            <option value="good" {{ $tAsset->condition === 'good' ? 'selected' : '' }}>Good</option>
                                                            <option value="fair" {{ $tAsset->condition === 'fair' ? 'selected' : '' }}>Fair</option>
                                                            <option value="damaged" {{ $tAsset->condition === 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700">Damage charges TZS</label>
                                                <input type="number" name="damage_charges" required value="0" min="0"
                                                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700">Refund Deposit TZS</label>
                                                <input type="number" name="deposit_refunded" required value="{{ $con->deposit_amount }}" min="0"
                                                       class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700">Check-Out Room Inspection Notes</label>
                                            <textarea name="room_condition_notes" rows="2" placeholder="e.g. Mattress damaged, key returned."
                                                      class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-650"></textarea>
                                        </div>

                                        <div class="pt-4 border-t border-slate-100 flex gap-3">
                                            <button type="button" @click="checkoutId = null" 
                                                    class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                                            <button type="submit" 
                                                    class="flex-1 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-rose-50">Log Check-Out</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>

                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="door-closed" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold">No records found for this section.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contracts->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-55/30">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
