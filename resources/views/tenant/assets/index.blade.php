@extends('layouts.admin')

@section('title', 'My Allocated & Personal Assets')
@section('breadcrumb_title', 'My Assets')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto" x-data="{ openRegisterModal: false }">
    <div class="grid grid-cols-1 gap-8">
        
        <!-- SECTION 1: ALLOCATED HOSTEL ASSETS CHECKLIST -->
        <div class="bg-white border border-slate-100 p-8 sm:p-10 rounded-3xl shadow-xl shadow-slate-100/50">
            <h2 class="text-xl font-bold text-slate-900 mb-2">Hostel-Owned Allocated Assets</h2>
            <p class="text-xs text-slate-500 mb-6">Review the furniture and appliances allocated to your room by the management. Confirm receipt when verified.</p>

            @if($handovers->isEmpty())
                <div class="text-center py-12 text-slate-400 text-xs border-2 border-dashed border-slate-100 rounded-2xl">
                    <i data-lucide="package" class="w-12 h-12 mx-auto text-slate-200 mb-2"></i>
                    <p>No allocated assets currently assigned to you.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($handovers as $ho)
                        <div class="p-6 border rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all
                            {{ $ho->tenant_confirmed ? 'bg-slate-50/50 border-slate-200' : 'bg-amber-50/10 border-amber-100 ring-1 ring-amber-100/50' }}">
                            
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="p-1.5 rounded-lg text-slate-500 bg-white border border-slate-150">
                                        <i data-lucide="armchair" class="w-4 h-4"></i>
                                    </span>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $ho->asset->asset_name }}</h4>
                                    <span class="text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded-md font-bold uppercase">{{ $ho->asset->condition }}</span>
                                    <span class="text-[9px] bg-indigo-100 text-indigo-750 px-2 py-0.5 rounded-md font-bold uppercase">Property Asset</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Property: <span class="font-semibold text-slate-700">{{ $ho->asset->property->property_name }}</span></p>
                                <p class="text-[10px] text-slate-400">Handed over on: {{ \Carbon\Carbon::parse($ho->handover_date)->format('M d, Y H:i') }}</p>
                                @if($ho->notes)
                                    <p class="text-xs text-slate-650 bg-white border border-slate-100 p-2 rounded-xl mt-2 italic">Notes: {{ $ho->notes }}</p>
                                @endif
                            </div>

                            <div>
                                @if($ho->tenant_confirmed)
                                    <span class="inline-flex items-center gap-1 text-emerald-600 text-xs font-bold bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>Confirmed Receipt</span>
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('tenant.assets.confirm', $ho->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-500 transition-colors">
                                            <i data-lucide="signature" class="w-3.5 h-3.5"></i>
                                            <span>Confirm Receipt</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- SECTION 2: TENANT PERSONAL ASSETS -->
        <div class="bg-white border border-slate-100 p-8 sm:p-10 rounded-3xl shadow-xl shadow-slate-100/50">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xl font-bold text-slate-900">My Registered Personal Assets</h2>
                <button type="button" @click="openRegisterModal = true" class="inline-flex items-center gap-1 px-4 py-2 bg-indigo-650 text-white hover:bg-indigo-500 rounded-xl text-xs font-semibold transition-colors">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Register New Asset</span>
                </button>
            </div>
            <p class="text-xs text-slate-500 mb-6">Register your personal electronics, bicycles, laptops, or other valuable items for security and verification purposes.</p>

            @if($personalAssets->isEmpty())
                <div class="text-center py-12 text-slate-400 text-xs border-2 border-dashed border-slate-100 rounded-2xl">
                    <i data-lucide="laptop" class="w-12 h-12 mx-auto text-slate-200 mb-2"></i>
                    <p>You have not registered any personal assets yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($personalAssets as $pa)
                        <div class="p-6 bg-slate-50/50 border border-slate-200 rounded-2xl flex items-start justify-between gap-4 transition-all hover:bg-slate-50">
                            <div class="space-y-2 flex-1">
                                <div class="flex items-start gap-2 flex-wrap">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 rounded-lg text-slate-500 bg-white border border-slate-150">
                                            <i data-lucide="laptop" class="w-4 h-4"></i>
                                        </span>
                                        <h4 class="text-sm font-bold text-slate-900">{{ $pa->asset_name }}</h4>
                                    </div>
                                    <span class="text-[9px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded-md font-bold uppercase">{{ $pa->category }}</span>
                                    <span class="text-[9px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md font-bold uppercase">My Asset</span>
                                </div>

                                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-500">
                                    <div>Serial No: <span class="font-bold text-slate-750">{{ $pa->serial_number ?: '-' }}</span></div>
                                    <div>Condition: <span class="font-bold text-slate-750 capitalize">{{ $pa->condition }}</span></div>
                                    <div class="col-span-2">Registered: <span class="font-bold text-slate-750">{{ \Carbon\Carbon::parse($pa->registered_date)->format('M d, Y') }}</span></div>
                                </div>

                                @if($pa->description)
                                    <p class="text-xs text-slate-500 italic bg-white p-2 border border-slate-100 rounded-xl">{{ $pa->description }}</p>
                                @endif

                                @if($pa->photo_path)
                                    <div class="mt-2 aspect-video w-32 rounded-xl overflow-hidden border border-slate-200">
                                        <img src="{{ asset('storage/' . $pa->photo_path) }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('tenant.assets.personal.destroy', $pa->id) }}" onsubmit="return confirm('Are you sure you want to remove this personal asset?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-white border border-transparent hover:border-slate-200 transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- REGISTER PERSONAL ASSET MODAL -->
    <div x-show="openRegisterModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" style="display:none;" x-transition>
        <div class="bg-white border border-slate-100 p-8 rounded-3xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto" @click.away="openRegisterModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="laptop" class="w-5 h-5 text-indigo-600"></i>
                    <span>Register Personal Asset</span>
                </h3>
                <button type="button" @click="openRegisterModal = false" class="p-1 text-slate-400 hover:text-slate-650">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('tenant.assets.personal.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="asset_name" class="block text-xs font-bold text-slate-500 uppercase">Asset Name</label>
                    <input id="asset_name" name="asset_name" type="text" required placeholder="e.g. MacBook Pro, Giant Bicycle"
                           class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-650">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-xs font-bold text-slate-500 uppercase">Category</label>
                        <select id="category" name="category" required
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                            <option value="Electronics">Electronics</option>
                            <option value="Computers">Computers</option>
                            <option value="Mobile Phones">Mobile Phones</option>
                            <option value="Vehicles/Bicycle">Vehicles/Bicycle</option>
                            <option value="Books/Study">Books/Study</option>
                            <option value="Luggage">Luggage</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="condition" class="block text-xs font-bold text-slate-500 uppercase">Condition</label>
                        <select id="condition" name="condition" required
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                            <option value="new">New</option>
                            <option value="excellent">Excellent</option>
                            <option value="good" selected>Good</option>
                            <option value="fair">Fair</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="serial_number" class="block text-xs font-bold text-slate-500 uppercase">Serial Number (Optional)</label>
                    <input id="serial_number" name="serial_number" type="text" placeholder="e.g. C02X1234HFFF"
                           class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none">
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold text-slate-500 uppercase">Description / Identifier</label>
                    <textarea id="description" name="description" rows="2" placeholder="e.g. Space Gray, sticker on back cover..."
                              class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none"></textarea>
                </div>

                <div>
                    <label for="photo" class="block text-xs font-bold text-slate-500 uppercase">Upload Asset Photo</label>
                    <input id="photo" name="photo" type="file" accept="image/*"
                           class="mt-1 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-750 hover:file:bg-indigo-100">
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-500 uppercase">Additional Notes</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Purchase receipt details or warranty information..."
                              class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openRegisterModal = false" class="px-5 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-650 hover:bg-indigo-500 rounded-xl text-xs font-semibold text-white shadow-sm">
                        Register Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
