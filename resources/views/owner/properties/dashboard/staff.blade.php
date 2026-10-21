@extends('layouts.property_dashboard')

@section('property_content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left panel: Assign Staff Form -->
    <div>
        <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Assign Staff / Team</h3>
            <p class="text-[10px] text-slate-500 mb-4">Enter a registered staff email address to assign them to this property.</p>
            <form method="POST" action="{{ route('owner.properties.staff.store', $property->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-500">Staff Email Address</label>
                    <input id="email" name="email" type="email" required placeholder="e.g. staff@reliance.com"
                           class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="role" class="block text-xs font-semibold text-slate-500">Management Role</label>
                    <select id="role" name="role" required
                            class="mt-1 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-950 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="manager">Property Manager</option>
                        <option value="receptionist">Receptionist / Caretaker</option>
                        <option value="caretaker">Caretaker / Operations</option>
                        <option value="accountant">Accountant / Finance</option>
                        <option value="agent">Authorized Agent</option>
                    </select>
                </div>
                <button type="submit" class="w-full flex justify-center items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>Assign Member</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Right panel: Assigned Staff List -->
    <div class="lg:col-span-2 bg-white border border-slate-100 p-6 sm:p-8 rounded-3xl shadow-sm shadow-slate-100/50">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6">Assigned Property Team</h3>

        @if($property->managers->isEmpty())
            <div class="text-center py-20 text-slate-400">
                <i data-lucide="users" class="w-12 h-12 mx-auto text-slate-200 mb-2"></i>
                <p class="text-xs">No team members assigned to this property yet. Add staff on the left panel.</p>
            </div>
        @else
            <div class="overflow-hidden border border-slate-150 rounded-2xl shadow-sm bg-white">
                <table class="min-w-full divide-y divide-slate-150 text-xs text-left">
                    <thead class="bg-slate-50 font-bold text-slate-700">
                        <tr>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Role</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($property->managers as $m)
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $m->name }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $m->email }}</td>
                                <td class="px-6 py-4 capitalize font-semibold text-slate-700">
                                    <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[10px]">{{ $m->pivot->role }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('owner.properties.staff.remove', [$property->id, $m->id]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-rose-600 hover:text-rose-900 font-semibold hover:underline">Remove</button>
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
@endsection
