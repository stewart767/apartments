@extends('layouts.property_dashboard')

@section('property_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left panel: Register Asset & Handover forms -->
    <div class="space-y-6">
        <!-- Register Asset -->
        <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Register New Asset</h3>
            <form method="POST" action="{{ route('owner.properties.assets.store', $property->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="asset_name" class="block text-xs font-semibold text-slate-500">Asset Name</label>
                    <input id="asset_name" name="asset_name" type="text" required placeholder="e.g. Bed frame, Ergonomic Chair, AC Remote"
                           class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-xs font-semibold text-slate-500">Category</label>
                        <select id="category_id" name="category_id" required
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="condition" class="block text-xs font-semibold text-slate-500">Initial Condition</label>
                        <select id="condition" name="condition" required
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="new" selected>Brand New</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="serial_number" class="block text-xs font-semibold text-slate-500">Serial / Asset Number</label>
                    <input id="serial_number" name="serial_number" type="text" placeholder="e.g. SN-998877"
                           class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="room_id" class="block text-xs font-semibold text-slate-500">Assign Room</label>
                        <select id="room_id" name="room_id"
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">None / Storage</option>
                            @foreach($property->rooms as $rm)
                                <option value="{{ $rm->id }}">Room {{ $rm->room_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="bed_id" class="block text-xs font-semibold text-slate-500">Assign Bed</label>
                        <select id="bed_id" name="bed_id"
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">None</option>
                            @foreach($property->rooms as $rm)
                                @foreach($rm->beds as $bd)
                                    <option value="{{ $bd->id }}">Room {{ $rm->room_number }} - {{ $bd->bed_number }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Register Asset</span>
                </button>
            </form>
        </div>

        <!-- Issue Handover -->
        <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Issue Asset Handover</h3>
            <form method="POST" action="{{ route('owner.properties.assets.handover', $property->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="asset_id" class="block text-xs font-semibold text-slate-500">Select Registered Asset</label>
                    <select id="asset_id" name="asset_id" required
                            class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>Select...</option>
                        @foreach($assets as $as)
                            <option value="{{ $as->id }}">{{ $as->asset_name }} ({{ $as->serial_number ?? 'No SN' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tenant_id" class="block text-xs font-semibold text-slate-500">Assign Tenant</label>
                    <select id="tenant_id" name="tenant_id" required
                            class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>Select Tenant...</option>
                        @foreach($tenants as $tn)
                            <option value="{{ $tn->id }}">{{ $tn->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-500">Handover Notes</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Describe specific damages or components"
                              class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Send Handover Request</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Right panel: Asset Inventory & Inspection Log -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Assets Table -->
        <div class="bg-white border border-slate-100 p-6 sm:p-8 rounded-3xl shadow-sm shadow-slate-100/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6">Asset Inventory</h3>
            
            @if($assets->isEmpty())
                <p class="text-xs text-slate-400 py-10 text-center">No assets registered yet.</p>
            @else
                <div class="overflow-x-auto border border-slate-150 rounded-2xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-150 text-xs text-left">
                        <thead class="bg-slate-50 font-bold text-slate-700">
                            <tr>
                                <th scope="col" class="px-4 py-3">Asset</th>
                                <th scope="col" class="px-4 py-3">Condition</th>
                                <th scope="col" class="px-4 py-3">Room / Bed</th>
                                <th scope="col" class="px-4 py-3">Handover Status</th>
                                <th scope="col" class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($assets as $as)
                                <tr>
                                    <td class="px-4 py-3.5">
                                        <p class="font-bold text-slate-900">{{ $as->asset_name }}</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $as->serial_number ?? 'No SN' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider
                                            @if($as->condition === 'new') bg-emerald-50 text-emerald-700
                                            @elseif($as->condition === 'good') bg-indigo-50 text-indigo-700
                                            @elseif($as->condition === 'fair') bg-amber-50 text-amber-700
                                            @else bg-rose-50 text-rose-700
                                            @endif">
                                            {{ $as->condition }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-650 font-semibold">
                                        Room {{ $as->room->room_number ?? 'N/A' }}
                                        @if($as->bed)
                                            - Bed {{ $as->bed->bed_number }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @php
                                            $ho = $as->handoverRecords->whereNull('return_date')->first();
                                        @endphp
                                        @if($ho)
                                            <p class="font-semibold text-slate-950">{{ $ho->tenant->name }}</p>
                                            <span class="inline-flex items-center gap-0.5 text-[8px] font-bold uppercase tracking-wider {{ $ho->tenant_confirmed ? 'text-emerald-600' : 'text-amber-600' }}">
                                                {{ $ho->tenant_confirmed ? 'Confirmed' : 'Pending Confirmation' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">In Storage</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <!-- Log Inspection Trigger Button/Modal -->
                                        <form method="POST" action="{{ route('owner.properties.assets.inspect', $property->id) }}" class="inline-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="asset_id" value="{{ $as->id }}">
                                            <select name="condition" class="border border-slate-200 rounded px-1 text-[10px] bg-white">
                                                <option value="new">New</option>
                                                <option value="good" selected>Good</option>
                                                <option value="fair">Fair</option>
                                                <option value="poor">Poor</option>
                                            </select>
                                            <select name="type" class="border border-slate-200 rounded px-1 text-[10px] bg-white">
                                                <option value="routine" selected>Routine</option>
                                                <option value="checkin">Check-in</option>
                                                <option value="checkout">Check-out</option>
                                            </select>
                                            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold">Log</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
