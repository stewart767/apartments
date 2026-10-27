@extends('layouts.admin')

@section('title', 'SaaS Subscriptions & Plan Management')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8" x-data="{ promoModalOpen: false, selectedOrgId: null, selectedOrgName: '' }">

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between border-b border-slate-200 pb-6 mb-8">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                Commercial Revenue Control
            </span>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">SaaS Subscriptions & Customer Billing</h1>
            <p class="mt-1 text-sm text-slate-500">
                Manage property owner plans, verify mobile money/bank subscription payments, and grant promotional trials.
            </p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- SaaS Metrics Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total SaaS Revenue</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600"><i data-lucide="banknote" class="w-4 h-4"></i></span>
            </div>
            <p class="text-2xl font-black text-slate-900">TSh {{ number_format($totalSaasRevenue, 0) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Excludes tenant rental payments</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Subscriptions</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $activeSubscriptionsCount }}</p>
            <p class="text-[11px] text-emerald-600 mt-1 font-semibold">Generating recurring revenue</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Promotional Trials</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600"><i data-lucide="sparkles" class="w-4 h-4"></i></span>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $trialingCount }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Trialing landlord accounts</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Verifications</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600"><i data-lucide="clock" class="w-4 h-4"></i></span>
            </div>
            <p class="text-2xl font-black {{ $pendingInvoicesCount > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $pendingInvoicesCount }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Invoices awaiting proof check</p>
        </div>
    </div>

    <!-- Pending Invoices Section -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm mb-12">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Subscription Invoices & Receipts</h3>
                <p class="text-xs text-slate-500">Verify mobile money / bank slips to activate customer plans.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">Invoice #</th>
                        <th class="py-3 px-6">Organization / Owner</th>
                        <th class="py-3 px-6">Plan / Interval</th>
                        <th class="py-3 px-6">Amount</th>
                        <th class="py-3 px-6">Reference</th>
                        <th class="py-3 px-6">Status</th>
                        <th class="py-3 px-6 text-right">Verification Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-6 font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-900">{{ $inv->organization->name }}</span>
                                <span class="block text-[11px] text-slate-400">{{ $inv->organization->owner->email ?? 'N/A' }}</span>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-semibold text-slate-800">{{ $inv->plan ? $inv->plan->name : 'N/A' }}</span>
                                <span class="text-slate-400">({{ $inv->billing_interval }})</span>
                            </td>
                            <td class="py-3.5 px-6 font-extrabold text-slate-900">{{ $inv->formatted_amount }}</td>
                            <td class="py-3.5 px-6">
                                <span class="font-mono text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ $inv->payment_reference ?? 'None' }}</span>
                                @if($inv->receipt_path)
                                    <a href="{{ asset('storage/' . $inv->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-indigo-600 font-semibold hover:underline ml-2">
                                        <i data-lucide="file" class="w-3 h-3"></i> Receipt
                                    </a>
                                @endif
                            </td>
                            <td class="py-3.5 px-6">
                                @if($inv->isPaid())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Paid</span>
                                @elseif($inv->isPending())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">{{ ucfirst($inv->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                @if($inv->isPending())
                                    <div class="inline-flex items-center gap-2">
                                        <form action="{{ route('admin.subscriptions.verify', $inv->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="py-1 px-3 rounded-lg bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow-sm">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.subscriptions.verify', $inv->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="py-1 px-3 rounded-lg bg-slate-200 text-slate-700 font-bold text-xs hover:bg-rose-100 hover:text-rose-700">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400">Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">No subscription invoices recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- Organizations & Subscriptions List -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Landlord Organizations & Entitlements</h3>
                <p class="text-xs text-slate-500">Monitor resource limits and grant promotional trials.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">Organization</th>
                        <th class="py-3 px-6">Owner</th>
                        <th class="py-3 px-6">Current Plan</th>
                        <th class="py-3 px-6">Units Used</th>
                        <th class="py-3 px-6">Properties</th>
                        <th class="py-3 px-6">Subscription Status</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($organizations as $org)
                        @php
                            $sub = $org->currentSubscription;
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-6 font-bold text-slate-900">{{ $org->name }}</td>
                            <td class="py-3.5 px-6">
                                <span class="font-semibold text-slate-800">{{ $org->owner->name ?? 'N/A' }}</span>
                                <span class="block text-[11px] text-slate-400">{{ $org->owner->phone ?? '' }}</span>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-900">{{ $sub && $sub->plan ? $sub->plan->name : 'None' }}</span>
                            </td>
                            <td class="py-3.5 px-6 font-semibold text-slate-800">
                                {{ $org->total_units_count }} units
                            </td>
                            <td class="py-3.5 px-6 font-semibold text-slate-800">
                                {{ $org->total_properties_count }} properties
                            </td>
                            <td class="py-3.5 px-6">
                                @if($sub && $sub->isActive())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                                @elseif($sub && $sub->isTrialing())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Trial</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">No Active Plan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <button type="button" 
                                        @click="selectedOrgId = {{ $org->id }}; selectedOrgName = '{{ addslashes($org->name) }}'; promoModalOpen = true"
                                        class="py-1 px-3 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs hover:bg-indigo-100">
                                    Grant Trial
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $organizations->links() }}
        </div>
    </div>

    <!-- Grant Promotional Trial Modal -->
    <div x-show="promoModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative" @click.outside="promoModalOpen = false">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Grant Promotional Trial Access</h3>
            <p class="text-xs text-slate-500 mb-4">Organization: <strong class="text-slate-800" x-text="selectedOrgName"></strong></p>

            <form action="{{ route('admin.subscriptions.promo') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="organization_id" :value="selectedOrgId">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Plan Tier *</label>
                    <select name="plan_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Up to {{ $p->unit_limit }} units)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Duration (Days) *</label>
                    <input type="number" name="days" value="14" min="1" max="90" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Administrative Reason *</label>
                    <input type="text" name="reason" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="e.g. Hostels onboarding promo campaign">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="promoModalOpen = false" class="py-2 px-4 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="submit" class="py-2 px-5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 shadow-md shadow-indigo-200">
                        Activate Trial
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
