@extends('layouts.property_dashboard')

@section('property_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="roomsBedsManager()">
    <!-- Left panel: Add Room & Add Bed Forms -->
    <div class="space-y-6">
        <!-- Add Room Form -->
        <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Add Room / Unit</h3>
            <form method="POST" action="{{ route('owner.properties.rooms.store', $property->id) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="building_id" class="block text-xs font-semibold text-slate-500">Building Block</label>
                        <select id="building_id" name="building_id" x-model="buildingId" @change="floorId = ''"
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">None</option>
                            @foreach($property->buildings as $b)
                                <option value="{{ $b->id }}">{{ $b->building_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="floor_id" class="block text-xs font-semibold text-slate-500">Floor</label>
                        <select id="floor_id" name="floor_id" x-model="floorId" :disabled="!buildingId"
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                            <option value="">None</option>
                            <template x-for="f in filteredFloors" :key="f.id">
                                <option :value="f.id" x-text="f.floor_number_name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="room_number" class="block text-xs font-semibold text-slate-500">Room/Unit Number</label>
                        <input id="room_number" name="room_number" type="text" required placeholder="e.g. A101, Rm 5"
                               class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="room_type_id" class="block text-xs font-semibold text-slate-500">Room Category</label>
                        <select id="room_type_id" name="room_type_id" required
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="capacity" class="block text-xs font-semibold text-slate-500">Max Capacity</label>
                        <input id="capacity" name="capacity" type="number" required min="1" placeholder="Max occupants"
                               class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-semibold text-slate-500">Price (TZS)</label>
                        <input id="price" name="price" type="number" required placeholder="Rent amount"
                               class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="billing_period" class="block text-xs font-semibold text-slate-500">Billing Cycle</label>
                        <select id="billing_period" name="billing_period" required
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="semester">Semester</option>
                            <option value="six_months">Six Months</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>

                    <div>
                        <label for="gender_accommodation" class="block text-xs font-semibold text-slate-500">Gender Restriction</label>
                        <select id="gender_accommodation" name="gender_accommodation" required
                                class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="any">Co-ed / Any</option>
                            <option value="male">Male Only</option>
                            <option value="female">Female Only</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label for="furnished_status" class="block text-[10px] font-semibold text-slate-500">Furnishing</label>
                        <select id="furnished_status" name="furnished_status" required
                                class="mt-1 block w-full px-2 py-1.5 border border-slate-200 bg-white rounded-lg text-slate-950 text-[10px] focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="unfurnished">Unfurnished</option>
                            <option value="semi_furnished">Semi</option>
                            <option value="fully_furnished">Fully</option>
                        </select>
                    </div>

                    <div>
                        <label for="bathroom_type" class="block text-[10px] font-semibold text-slate-500">Bathroom</label>
                        <select id="bathroom_type" name="bathroom_type" required
                                class="mt-1 block w-full px-2 py-1.5 border border-slate-200 bg-white rounded-lg text-slate-950 text-[10px] focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="private">Private</option>
                            <option value="shared">Shared</option>
                        </select>
                    </div>

                    <div>
                        <label for="kitchen_type" class="block text-[10px] font-semibold text-slate-500">Kitchen</label>
                        <select id="kitchen_type" name="kitchen_type" required
                                class="mt-1 block w-full px-2 py-1.5 border border-slate-200 bg-white rounded-lg text-slate-950 text-[10px] focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="private">Private</option>
                            <option value="shared">Shared</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Create Room</span>
                </button>
            </form>
        </div>

        <!-- Add Bed Form -->
        <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Add Bed to Hostel Room</h3>
            <form method="POST" action="{{ route('owner.properties.beds.store', $property->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="room_id" class="block text-xs font-semibold text-slate-500">Select Room</label>
                    <select id="room_id" name="room_id" required
                            class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>Select Room...</option>
                        @foreach($property->rooms as $rm)
                            <option value="{{ $rm->id }}">Room {{ $rm->room_number }} ({{ $rm->roomType->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="bed_number" class="block text-xs font-semibold text-slate-500">Bed Designation / Number</label>
                    <input id="bed_number" name="bed_number" type="text" required placeholder="e.g. Bed 1, A101-B1"
                           class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="w-full flex justify-center items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Bed Space</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Right panel: Rooms & Beds Tree View -->
    <div class="lg:col-span-2 bg-white border border-slate-100 p-6 sm:p-8 rounded-3xl shadow-sm shadow-slate-100/50">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6">Listed Rooms & Beds Spaces</h3>

        @if($property->rooms->isEmpty())
            <div class="text-center py-20 text-slate-400">
                <i data-lucide="door-closed" class="w-12 h-12 mx-auto text-slate-200 mb-2"></i>
                <p class="text-xs">No rooms listed under this property. Get started by adding a room on the left panel.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($property->rooms as $rm)
                    <div class="border border-slate-150 rounded-2xl overflow-hidden shadow-sm">
                        <!-- Room Header -->
                        <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-150 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 bg-indigo-50 rounded-lg text-indigo-600">
                                    <i data-lucide="door-open" class="w-4 h-4"></i>
                                </span>
                                <span class="font-bold text-slate-900">Room {{ $rm->room_number }}</span>
                                <span class="text-[10px] bg-slate-200 text-slate-650 px-2 py-0.5 rounded-md font-bold uppercase">{{ $rm->roomType->name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-slate-900">TZS {{ number_format($rm->price) }}</span>
                                <span class="text-slate-400">/ {{ $rm->billing_period }}</span>
                            </div>
                        </div>

                        <!-- Beds inside Room -->
                        <div class="p-4 bg-white">
                            @if($rm->beds->isEmpty())
                                <p class="text-xs text-slate-400 py-1">No beds registered. Single room occupancy applies.</p>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach($rm->beds as $bd)
                                        <div class="p-3 border rounded-xl flex flex-col justify-between gap-2 text-[10px] font-semibold border-slate-150"
                                             :class="'{{ $bd->status }}' == 'occupied' ? 'bg-rose-50/20 text-rose-700' : 'bg-emerald-50/20 text-emerald-700'">
                                            <div class="flex items-center gap-1">
                                                <i data-lucide="bed" class="w-3.5 h-3.5"></i>
                                                <span>{{ $bd->bed_number }}</span>
                                            </div>
                                            <span class="capitalize text-[8px] font-bold tracking-wider opacity-75">{{ $bd->status }}</span>
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

@section('scripts')
<script>
    function roomsBedsManager() {
        return {
            buildingId: '',
            floorId: '',
            buildings: @json($property->buildings),

            get filteredFloors() {
                if (!this.buildingId) return [];
                const b = this.buildings.find(item => item.id == this.buildingId);
                return b ? b.floors : [];
            }
        }
    }
</script>
@endsection
