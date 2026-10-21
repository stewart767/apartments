@extends('layouts.property_dashboard')

@section('property_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left panel: Building & Floor Configuration Forms -->
    <div class="space-y-6">
        <!-- Add Building Block -->
        <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Add Building Block</h3>
            <form method="POST" action="{{ route('owner.properties.buildings.store', $property->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="building_name" class="block text-xs font-semibold text-slate-500">Building Name</label>
                    <input id="building_name" name="building_name" type="text" required placeholder="e.g. Block A, Wing B"
                           class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="w-full flex justify-center items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Building</span>
                </button>
            </form>
        </div>

        <!-- Add Floor -->
        <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Add Floor to Building</h3>
            <form method="POST" action="{{ route('owner.properties.floors.store', $property->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="building_id" class="block text-xs font-semibold text-slate-500">Select Building Block</label>
                    <select id="building_id" name="building_id" required
                            class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="" disabled selected>Select...</option>
                        @foreach($property->buildings as $b)
                            <option value="{{ $b->id }}">{{ $b->building_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="floor_number_name" class="block text-xs font-semibold text-slate-500">Floor Name / Number</label>
                    <input id="floor_number_name" name="floor_number_name" type="text" required placeholder="e.g. Ground Floor, 1st Floor"
                           class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="w-full flex justify-center items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Floor</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Right panel: Structure Tree View -->
    <div class="lg:col-span-2 bg-white border border-slate-100 p-6 sm:p-8 rounded-3xl shadow-sm shadow-slate-100/50">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6">Property Structural Layout</h3>
        
        @if($property->buildings->isEmpty())
            <div class="text-center py-20 text-slate-400">
                <i data-lucide="layers" class="w-12 h-12 mx-auto text-slate-200 mb-2"></i>
                <p class="text-xs">No buildings or blocks registered. Get started by adding a building block on the left panel.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($property->buildings as $b)
                    <div class="border border-slate-150 rounded-2xl overflow-hidden shadow-sm">
                        <!-- Building Header -->
                        <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-150">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 bg-indigo-50 rounded-lg text-indigo-600">
                                    <i data-lucide="building" class="w-4 h-4"></i>
                                </span>
                                <span class="text-sm font-bold text-slate-900">{{ $b->building_name }}</span>
                                <span class="text-[10px] bg-slate-200 text-slate-650 px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">{{ $b->building_code }}</span>
                            </div>
                        </div>

                        <!-- Floors inside Building -->
                        <div class="p-4 bg-white">
                            @if($b->floors->isEmpty())
                                <p class="text-xs text-slate-400 py-2">No floors added to this block yet.</p>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($b->floors as $f)
                                        <div class="p-3 border border-slate-100 bg-slate-50/20 rounded-xl flex items-center gap-2 text-xs font-semibold text-slate-700">
                                            <i data-lucide="layers" class="w-4 h-4 text-slate-400"></i>
                                            <span>{{ $f->floor_number_name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
