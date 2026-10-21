@extends('layouts.admin')

@section('title', 'User Accounts Management')
@section('page_title', 'All System Users')
@section('breadcrumb_title', 'Users')

@section('content')
<div class="space-y-6" x-data="{ ownerModalOpen: false, selectedOwner: null, actionUrl: '' }">
    
    <!-- Filter Panel -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.users') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Search field -->
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Search Users</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name, email or phone..."
                           class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                </div>
            </div>

            <!-- Role filter -->
            <div>
                <label for="role" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">User Role</label>
                <select name="role" id="role" 
                        class="block w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                    <option value="">All Roles</option>
                    <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Property Owner</option>
                    <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Property Manager</option>
                    <option value="receptionist" {{ request('role') === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                    <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent</option>
                    <option value="tenant" {{ request('role') === 'tenant' ? 'selected' : '' }}>Tenant</option>
                </select>
            </div>

            <!-- Status filter -->
            <div>
                <label for="status" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Account Status</label>
                <select name="status" id="status" 
                        class="block w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-750 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Apply Filters</span>
                </button>
                @if(request()->anyFilled(['search', 'role', 'status']))
                    <a href="{{ route('admin.users') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 px-4 rounded-xl transition-all flex items-center justify-center">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table Panel -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-900">System Registered Users</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-650">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">System Role / Verification</th>
                        <th class="px-6 py-4">Contact Info</th>
                        <th class="px-6 py-4">Registered Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($users as $user)
                        <tr>
                            <!-- User info details -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-405 mt-0.5">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Role & Verification Status -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                    @if($user->role === 'superadmin') bg-indigo-50 text-indigo-700
                                    @elseif($user->role === 'owner') bg-purple-50 text-purple-700
                                    @elseif($user->role === 'manager') bg-teal-50 text-teal-700
                                    @elseif($user->role === 'receptionist') bg-orange-50 text-orange-700
                                    @elseif($user->role === 'agent') bg-pink-50 text-pink-700
                                    @else bg-blue-50 text-blue-700 @endif">
                                    {{ $user->role === 'superadmin' ? 'Super Admin' : $user->role }}
                                </span>
                                @if($user->isOwner())
                                    @if($user->ownerDetail)
                                        <div class="mt-1">
                                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md capitalize
                                                @if($user->ownerDetail->verification_status === 'verified') bg-emerald-50 text-emerald-700
                                                @elseif($user->ownerDetail->verification_status === 'pending') bg-amber-50 text-amber-700
                                                @else bg-rose-50 text-rose-700 @endif">
                                                Profile: {{ $user->ownerDetail->verification_status }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="mt-1">
                                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-500">
                                                Profile: Not Onboarded
                                            </span>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <!-- Contacts -->
                            <td class="px-6 py-4 text-xs text-slate-700 space-y-0.5">
                                @if($user->phone)
                                    <p class="flex items-center gap-1"><i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>{{ $user->phone }}</p>
                                @endif
                                @if($user->whatsapp)
                                    <p class="flex items-center gap-1"><i data-lucide="message-square" class="w-3.5 h-3.5 text-emerald-500"></i>{{ $user->whatsapp }}</p>
                                @endif
                                @if(!$user->phone && !$user->whatsapp)
                                    <p class="text-slate-400">N/A</p>
                                @endif
                            </td>
                            <!-- Date Registered -->
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $user->created_at ? $user->created_at->format('d M Y') : 'N/A' }}
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $user->created_at ? $user->created_at->diffForHumans() : '' }}</p>
                            </td>
                            <!-- Status Badge -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                    @if($user->status === 'active') bg-emerald-50 text-emerald-700
                                    @elseif($user->status === 'suspended') bg-rose-50 text-rose-700
                                    @else bg-slate-100 text-slate-600 @endif">
                                    {{ $user->status }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <!-- Suspend/Activate action -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl transition-all border
                                                        @if($user->status === 'active') bg-rose-50 hover:bg-rose-100 text-rose-750 border-rose-100
                                                        @else bg-emerald-50 hover:bg-emerald-100 text-emerald-750 border-emerald-100 @endif">
                                                @if($user->status === 'active')
                                                    <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                                                    <span>Suspend</span>
                                                @else
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                                    <span>Activate</span>
                                                @endif
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Self Account</span>
                                    @endif

                                    <!-- Verify owner detail action -->
                                    @if($user->isOwner() && $user->ownerDetail)
                                        @php
                                            $ownerJson = json_encode([
                                                'name' => $user->name,
                                                'full_legal_name' => $user->ownerDetail->full_legal_name,
                                                'nida' => $user->ownerDetail->nida,
                                                'nationality' => $user->ownerDetail->nationality,
                                                'phone' => $user->ownerDetail->phone,
                                                'whatsapp' => $user->ownerDetail->whatsapp,
                                                'email' => $user->ownerDetail->email,
                                                'region' => $user->ownerDetail->region->name ?? 'N/A',
                                                'district' => $user->ownerDetail->district->name ?? 'N/A',
                                                'ward' => $user->ownerDetail->ward->name ?? 'N/A',
                                                'street_address' => $user->ownerDetail->street_address ?? 'N/A',
                                                'id_document' => $user->ownerDetail->id_document_path ? asset('storage/' . $user->ownerDetail->id_document_path) : null,
                                                'supporting_document' => $user->ownerDetail->supporting_document_path ? asset('storage/' . $user->ownerDetail->supporting_document_path) : null,
                                                'verification_status' => $user->ownerDetail->verification_status,
                                                'rejection_reason' => $user->ownerDetail->rejection_reason ?? '',
                                            ]);
                                        @endphp
                                        <button type="button" 
                                                @click="selectedOwner = {{ $ownerJson }}; actionUrl = '{{ route('admin.users.verify-owner', $user->id) }}'; ownerModalOpen = true;"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-750 text-xs font-bold rounded-xl transition-all border border-purple-100">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                            <span>Verify Profile</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold">No registered users match the filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Owner Verification Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" 
         x-show="ownerModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>
        <div class="bg-white rounded-3xl shadow-xl border border-slate-150 w-full max-w-2xl overflow-hidden" @click.away="ownerModalOpen = false">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-900">Verify Owner Profile: <span x-text="selectedOwner ? selectedOwner.name : ''" class="text-purple-650"></span></h3>
                <button type="button" @click="ownerModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form :action="actionUrl" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm bg-slate-50 rounded-2xl p-4">
                    <div>
                        <span class="text-xs text-slate-400">Legal Name</span>
                        <p class="font-bold text-slate-900" x-text="selectedOwner?.full_legal_name || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400">National ID / NIDA</span>
                        <p class="font-bold text-slate-900" x-text="selectedOwner?.nida || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400">Nationality</span>
                        <p class="font-semibold text-slate-800" x-text="selectedOwner?.nationality || 'N/A'"></p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400">Address Details</span>
                        <p class="text-slate-800">
                            <span x-text="selectedOwner?.street_address"></span>, 
                            <span x-text="selectedOwner?.ward"></span>, 
                            <span x-text="selectedOwner?.district"></span>, 
                            <span x-text="selectedOwner?.region"></span>
                        </p>
                    </div>
                </div>

                <!-- Verification Documents -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-450 uppercase tracking-wider">Uploaded Documents</label>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-if="selectedOwner?.id_document">
                            <a :href="selectedOwner.id_document" target="_blank" class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl hover:bg-slate-50 text-xs font-semibold text-indigo-650 transition-colors">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                <span>View National ID / Passport</span>
                            </a>
                        </template>
                        <template x-if="selectedOwner?.supporting_document">
                            <a :href="selectedOwner.supporting_document" target="_blank" class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl hover:bg-slate-50 text-xs font-semibold text-indigo-650 transition-colors">
                                <i data-lucide="file" class="w-4 h-4"></i>
                                <span>View Supporting Document</span>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Status Action -->
                <div>
                    <label class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Profile Verification Status</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="border rounded-xl p-3 flex flex-col items-center gap-1.5 cursor-pointer transition-all hover:bg-slate-50"
                               :class="selectedOwner?.verification_status === 'verified' ? 'border-emerald-500 bg-emerald-50/20 text-emerald-800' : 'border-slate-200'">
                            <input type="radio" name="status" value="verified" x-model="selectedOwner.verification_status" class="sr-only">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                            <span class="text-xs font-bold">Approve & Verify</span>
                        </label>
                        <label class="border rounded-xl p-3 flex flex-col items-center gap-1.5 cursor-pointer transition-all hover:bg-slate-50"
                               :class="selectedOwner?.verification_status === 'rejected' ? 'border-rose-500 bg-rose-50/20 text-rose-800' : 'border-slate-200'">
                            <input type="radio" name="status" value="rejected" x-model="selectedOwner.verification_status" class="sr-only">
                            <i data-lucide="x-circle" class="w-5 h-5 text-rose-600"></i>
                            <span class="text-xs font-bold">Reject Onboarding</span>
                        </label>
                    </div>
                </div>

                <!-- Rejection notes -->
                <div x-show="selectedOwner?.verification_status === 'rejected'">
                    <label for="rejection_reason" class="block text-xs font-bold text-slate-450 uppercase tracking-wider mb-2">Rejection Reason</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" x-model="selectedOwner.rejection_reason" placeholder="Explain why this profile is rejected (e.g. documents blurry, incorrect NIDA)..."
                              class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600"></textarea>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="ownerModalOpen = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-650 hover:bg-slate-50 transition-all">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-750 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-50">Save Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
