@extends('layouts.admin')

@section('title', 'Amenities Administration')
@section('page_title', 'Amenities Setup & Manager')
@section('breadcrumb_title', 'Amenities')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Amenities List (Left 2 cols) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900">Current Registered System Amenities</h3>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-indigo-50 text-indigo-700">{{ $amenities->count() }} total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-650">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Preview & Icon</th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Category Scope</th>
                            <th class="px-6 py-4 text-center">Properties Used</th>
                            <th class="px-6 py-4 text-center">Rooms Used</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($amenities as $am)
                            <tr>
                                <!-- Icon Preview -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 border border-slate-150 text-slate-600 shadow-sm" title="{{ $am->icon }}">
                                        @if($am->icon)
                                            <i data-lucide="{{ $am->icon }}" class="w-4.5 h-4.5"></i>
                                        @else
                                            <i data-lucide="sparkles" class="w-4.5 h-4.5"></i>
                                        @endif
                                    </span>
                                </td>
                                <!-- Name -->
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $am->name }}</td>
                                <!-- Category -->
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full capitalize
                                        @if($am->category === 'property') bg-purple-50 text-purple-700
                                        @elseif($am->category === 'room') bg-teal-50 text-teal-700
                                        @else bg-blue-50 text-blue-700 @endif">
                                        {{ $am->category }}
                                    </span>
                                </td>
                                <!-- Property count -->
                                <td class="px-6 py-4 text-center font-semibold text-slate-800">{{ $am->properties_count }}</td>
                                <!-- Room count -->
                                <td class="px-6 py-4 text-center font-semibold text-slate-800">{{ $am->rooms_count }}</td>
                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <form action="{{ route('admin.amenities.destroy', $am->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this amenity? It will be removed from all associated properties and rooms.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition-all border border-rose-100">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <i data-lucide="sparkles" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                    <p class="text-sm font-semibold">No amenities registered yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Amenity Form (Right 1 col) -->
    <div class="lg:col-span-1">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm sticky top-6 space-y-6">
            <div>
                <h3 class="text-sm font-bold text-slate-950 flex items-center gap-2">
                    <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl"><i data-lucide="plus-circle" class="w-4 h-4"></i></span>
                    <span>Add New Amenity</span>
                </h3>
                <p class="text-xs text-slate-400 mt-2">Create global amenities that property owners can select for their listings and room options.</p>
            </div>

            <form action="{{ route('admin.amenities.store') }}" method="POST" class="space-y-4">
                @csrf
                <!-- Name field -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">Amenity Name</label>
                    <input type="text" name="name" id="name" required placeholder="e.g. Free Unlimited WiFi" value="{{ old('name') }}"
                           class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category Scope selection -->
                <div>
                    <label for="category" class="block text-xs font-bold text-slate-700 mb-1.5">Category Scope</label>
                    <select name="category" id="category" required
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                        <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General Scope</option>
                        <option value="property" {{ old('category') === 'property' ? 'selected' : '' }}>Property Level Scope</option>
                        <option value="room" {{ old('category') === 'room' ? 'selected' : '' }}>Room Level Scope</option>
                    </select>
                    @error('category')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Icon Identifier -->
                <div>
                    <label for="icon" class="block text-xs font-bold text-slate-700 mb-1.5">Lucide Icon Class</label>
                    <input type="text" name="icon" id="icon" placeholder="e.g. wifi, wind, tv, sparkles" value="{{ old('icon') }}"
                           class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                    <p class="text-[10px] text-slate-400 mt-1.5 leading-relaxed">
                        Use any valid Lucide icon name. Common icons: <br>
                        <strong>wifi</strong>, <strong>wind</strong> (AC), <strong>tv</strong>, <strong>droplet</strong> (Water), <strong>zap</strong> (Electricity), <strong>shield</strong> (Security), <strong>dumbbell</strong> (Gym), <strong>car</strong> (Parking).
                    </p>
                    @error('icon')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit button -->
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-750 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-indigo-50">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Register Amenity</span>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
