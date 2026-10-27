@extends('layouts.admin')

@section('title', 'Subscription & Plan Entitlements')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8" x-data="{ upgradeModalOpen: false, selectedPlanId: {{ $subscription ? $subscription->plan_id : $plans->first()->id }}, selectedInterval: '{{ $subscription ? $subscription->billing_interval : 'monthly' }}', cancelModalOpen: false }">

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between border-b border-slate-200 pb-6 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $org->name }}
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-slate-500">Commercial SaaS Account</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Subscription & Resource Entitlements</h1>
            <p class="mt-1 text-sm text-slate-500">
                Monitor your rental capacity, staff limits, subscription invoices, and upgrade your management tier.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            <button type="button" @click="upgradeModalOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                <i data-lucide="zap" class="w-4 h-4"></i>
                <span>{{ $subscription ? 'Upgrade / Switch Plan' : 'Choose Subscription Plan' }}</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="mb-6 p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-sm font-medium flex items-center gap-3">
            <i data-lucide="info" class="w-5 h-5 text-sky-600 shrink-0"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Current Subscription & Usage Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">

        <!-- Active Plan Summary Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Current Subscription</span>
                    @if($subscription && $subscription->isActive())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                    @elseif($subscription && $subscription->isTrialing())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Promotional Trial
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive / Expired
                        </span>
                    @endif
                </div>

                <h2 class="text-2xl font-extrabold text-slate-900">
                    {{ $subscription ? $subscription->plan->name : 'No Active Plan' }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $subscription ? $subscription->plan->description : 'Subscribe to a plan to list properties and manage bookings.' }}
                </p>

                <div class="mt-6 pt-4 border-t border-slate-100 space-y-2.5 text-xs text-slate-600">
                    @if($subscription)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Billing Interval:</span>
                            <span class="font-bold text-slate-800 capitalize">{{ $subscription->billing_interval }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Recurring Price:</span>
                            <span class="font-extrabold text-slate-900">TSh {{ number_format($subscription->price, 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Renews / Ends On:</span>
                            <span class="font-semibold text-slate-800">{{ $subscription->current_period_ends_at ? $subscription->current_period_ends_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Days Remaining:</span>
                            <span class="font-bold {{ $subscription->days_remaining < 5 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $subscription->days_remaining }} days</span>
                        </div>
                    @else
                        <p class="text-slate-500 italic">Select a package to unlock full accommodation management.</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <button type="button" @click="upgradeModalOpen = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    {{ $subscription ? 'Change Plan' : 'Select Plan' }} &rarr;
                </button>
                @if($subscription && $subscription->isActive())
                    <button type="button" @click="cancelModalOpen = true" class="text-xs font-medium text-slate-400 hover:text-rose-600">
                        Cancel Renewal
                    </button>
                @endif
            </div>
        </div>

        <!-- Resource Usage & Limits Card (2 Columns) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Plan Quota & Resource Usage</span>
                    <span class="text-xs text-slate-500 font-medium">Automatic Enforcement Active</span>
                </div>

                <div class="space-y-6">
                    <!-- Rental Units Progress -->
                    @php
                        $unitsPct = $unitsLimit > 0 ? min(100, round(($unitsUsed / $unitsLimit) * 100)) : 0;
                        $unitsOver = $unitsLimit > 0 && $unitsUsed >= $unitsLimit;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <i data-lucide="layers" class="w-4 h-4 text-indigo-600"></i> Rental Units Managed:
                            </span>
                            <span class="font-bold {{ $unitsOver ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $unitsUsed }} / {{ $unitsLimit }} units ({{ $unitsPct }}%)
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full {{ $unitsOver ? 'bg-rose-500' : ($unitsPct > 80 ? 'bg-amber-500' : 'bg-indigo-600') }}" style="width: {{ $unitsPct }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Unit Rule: 1 house = 1 unit | 1 private room = 1 unit | 1 hostel bed = 1 unit.
                        </p>
                    </div>

                    <!-- Properties Progress -->
                    @php
                        $propPct = $propertiesLimit > 0 ? min(100, round(($propertiesUsed / $propertiesLimit) * 100)) : 0;
                        $propOver = $propertiesLimit > 0 && $propertiesUsed >= $propertiesLimit;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <i data-lucide="building" class="w-4 h-4 text-indigo-600"></i> Properties Listed:
                            </span>
                            <span class="font-bold {{ $propOver ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $propertiesUsed }} / {{ $propertiesLimit }} property(ies) ({{ $propPct }}%)
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full {{ $propOver ? 'bg-rose-500' : ($propPct > 80 ? 'bg-amber-500' : 'bg-emerald-600') }}" style="width: {{ $propPct }}%"></div>
                        </div>
                    </div>

                    <!-- Staff Accounts Progress -->
                    @php
                        $staffPct = $staffLimit > 0 ? min(100, round(($staffUsed / $staffLimit) * 100)) : 0;
                        $staffOver = $staffLimit > 0 && $staffUsed >= $staffLimit;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <i data-lucide="users" class="w-4 h-4 text-indigo-600"></i> Staff Accounts (Managers/Receptionists):
                            </span>
                            <span class="font-bold {{ $staffOver ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $staffUsed }} / {{ $staffLimit }} account(s)
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full {{ $staffOver ? 'bg-rose-500' : 'bg-sky-600' }}" style="width: {{ $staffPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Need more units or hostel campuses?</span>
                <button type="button" @click="upgradeModalOpen = true" class="font-bold text-indigo-600 hover:text-indigo-800">
                    Expand Limits Now &rarr;
                </button>
            </div>
        </div>

    </div>

    <!-- Subscription Invoices & Payment History -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm mb-12">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Subscription Invoices & Receipts</h3>
                <p class="text-xs text-slate-500">Platform software fee records (distinct from tenant rental payments).</p>
            </div>
        </div>

        @if($invoices->isEmpty())
            <div class="text-center py-12 text-slate-400 text-xs">
                <i data-lucide="receipt" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                <p>No subscription invoices on record yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-6">Invoice #</th>
                            <th class="py-3 px-6">Plan / Interval</th>
                            <th class="py-3 px-6">Amount (TZS)</th>
                            <th class="py-3 px-6">Due Date</th>
                            <th class="py-3 px-6">Payment Ref</th>
                            <th class="py-3 px-6">Status</th>
                            <th class="py-3 px-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-6 font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                                <td class="py-3.5 px-6">
                                    <span class="font-semibold text-slate-800">{{ $inv->plan ? $inv->plan->name : 'Custom Plan' }}</span>
                                    <span class="text-slate-400">({{ $inv->billing_interval }})</span>
                                </td>
                                <td class="py-3.5 px-6 font-extrabold text-slate-900">{{ $inv->formatted_amount }}</td>
                                <td class="py-3.5 px-6">{{ $inv->due_date ? $inv->due_date->format('M d, Y') : '-' }}</td>
                                <td class="py-3.5 px-6">
                                    <span class="font-mono text-slate-700">{{ $inv->payment_reference ?? 'Pending' }}</span>
                                </td>
                                <td class="py-3.5 px-6">
                                    @if($inv->isPaid())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            Paid & Verified
                                        </span>
                                    @elseif($inv->isPending())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                            Awaiting Verification
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    @if($inv->receipt_path)
                                        <a href="{{ asset('storage/' . $inv->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> Receipt
                                        </a>
                                    @elseif(!$inv->isPaid())
                                        <button type="button" @click="upgradeModalOpen = true" class="text-xs font-bold text-amber-700 hover:underline">
                                            Submit Proof
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <!-- Upgrade / Switch Plan Modal -->
    <div x-show="upgradeModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl relative" @click.outside="upgradeModalOpen = false">
            <button type="button" @click="upgradeModalOpen = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-900">Select Subscription Plan</h3>
                <p class="text-xs text-slate-500 mt-1">Choose your preferred tier and submit payment details for administrative activation.</p>
            </div>

            <form action="{{ route('owner.subscription.subscribe') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                
                <!-- Interval Toggle -->
                <div class="flex items-center justify-center gap-4 bg-slate-50 p-2 rounded-xl border border-slate-200">
                    <button type="button" @click="selectedInterval = 'monthly'" class="py-1.5 px-4 rounded-lg text-xs font-bold transition-colors" :class="selectedInterval === 'monthly' ? 'bg-white shadow text-slate-900' : 'text-slate-500'">
                        Monthly Billing
                    </button>
                    <button type="button" @click="selectedInterval = 'annual'" class="py-1.5 px-4 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5" :class="selectedInterval === 'annual' ? 'bg-white shadow text-indigo-700' : 'text-slate-500'">
                        <span>Annual Billing</span>
                        <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded font-extrabold">2 Mos Free</span>
                    </button>
                    <input type="hidden" name="billing_interval" :value="selectedInterval">
                </div>

                <!-- Plan Selection Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($plans as $p)
                        <label class="cursor-pointer border rounded-xl p-3 text-center transition-all flex flex-col justify-between"
                               :class="selectedPlanId === {{ $p->id }} ? 'border-indigo-600 bg-indigo-50/50 ring-2 ring-indigo-600' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="plan_id" value="{{ $p->id }}" class="sr-only" x-model="selectedPlanId">
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $p->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $p->unit_limit }} units</p>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-200/60">
                                <span class="text-xs font-extrabold text-indigo-900" x-show="selectedInterval === 'monthly'">TSh {{ number_format($p->price_monthly, 0) }}</span>
                                <span class="text-xs font-extrabold text-indigo-900" x-show="selectedInterval === 'annual'" x-cloak>TSh {{ number_format($p->price_annual, 0) }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <!-- Payment Details -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-xs text-slate-700 space-y-2">
                    <p class="font-bold text-slate-900">Tanzania Payment Instructions:</p>
                    <p><strong>Mobile Money:</strong> M-Pesa / TigoPesa / Airtel Money to <code>+255 712 345 678</code> (RelianceHomes)</p>
                    <p><strong>Bank Deposit:</strong> CRDB Bank | Acc: <code>0150 2894 1100</code> | Name: RelianceHomes TZ</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Method *</label>
                        <select name="payment_method" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                            <option value="mpesa">Vodacom M-Pesa</option>
                            <option value="tigopesa">TigoPesa</option>
                            <option value="airtelmoney">Airtel Money</option>
                            <option value="bank_transfer">CRDB / NMB Bank Transfer</option>
                            <option value="cash">Direct Cash / Office Payment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Transaction Reference Code *</label>
                        <input type="text" name="payment_reference" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="e.g. QK8912KL99">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Upload Receipt Screenshot (JPG, PNG, PDF)</label>
                    <input type="file" name="receipt" accept="image/*,.pdf" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="upgradeModalOpen = false" class="py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200">
                        Confirm Subscription Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancellation Confirmation Modal -->
    <div x-show="cancelModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative" @click.outside="cancelModalOpen = false">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Request Cancellation</h3>
            <p class="text-xs text-slate-500 mb-4">
                Are you sure you want to cancel automatic renewal? You will retain uninterrupted access to your properties until {{ $subscription && $subscription->current_period_ends_at ? $subscription->current_period_ends_at->format('M d, Y') : 'the end of your period' }}.
            </p>

            <form action="{{ route('owner.subscription.cancel') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reason for Cancellation</label>
                    <textarea name="cancellation_reason" required rows="2" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" placeholder="Please let us know how we can improve..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="cancelModalOpen = false" class="py-2 px-4 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Keep Subscription
                    </button>
                    <button type="submit" class="py-2 px-4 rounded-xl bg-rose-600 text-white font-bold text-xs hover:bg-rose-700">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
