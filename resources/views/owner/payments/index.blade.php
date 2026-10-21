@extends('layouts.admin')

@section('title', 'Payments Ledger')
@section('page_title', 'Tenant Payments Ledger')
@section('breadcrumb_title', 'Ledger')

@section('content')
<div class="space-y-6" x-data="{ openConfirmId: null, receiptUrl: '', refCode: '' }">

    <!-- Payment List -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-900">Statements Log</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-650">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Reference</th>
                        <th class="px-6 py-4">Tenant</th>
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Method</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 font-bold text-right pr-8">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($payments as $pay)
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-950">{{ $pay->payment_reference }}</td>
                            <td class="px-6 py-4">{{ $pay->tenant->name }}</td>
                            <td class="px-6 py-4">{{ $pay->property->property_name }}</td>
                            <td class="px-6 py-4 font-bold">TZS {{ number_format($pay->amount) }}</td>
                            <td class="px-6 py-4 text-xs capitalize">{{ $pay->payment_method ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                    @if($pay->status === 'paid') bg-emerald-50 text-emerald-700
                                    @elseif($pay->status === 'partially_paid') bg-blue-50 text-blue-700
                                    @elseif($pay->status === 'pending') bg-amber-50 text-amber-700
                                    @else bg-rose-50 text-rose-700 @endif">
                                    @if($pay->status === 'partially_paid')
                                        Awaiting Verification
                                    @else
                                        {{ $pay->status }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right pr-8">
                                @if($pay->status === 'partially_paid')
                                    <button @click="openConfirmId = '{{ $pay->id }}'; receiptUrl = '{{ asset('storage/' . $pay->receipt_path) }}'; refCode = '{{ $pay->payment_reference }}'"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-650 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 text-xs font-bold rounded-xl transition-all border border-indigo-150">
                                        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                                        <span>Verify Receipt</span>
                                    </button>
                                @elseif($pay->receipt_path)
                                    <a href="{{ asset('storage/' . $pay->receipt_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all">
                                        <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>Receipt</span>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">N/A</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal verification template -->
                        <template x-if="openConfirmId == '{{ $pay->id }}'">
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4">
                                <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 max-w-lg w-full shadow-2xl space-y-6"
                                     @click.away="openConfirmId = null">
                                    
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                        <div>
                                            <h3 class="text-base font-bold text-slate-950">Verify Bank / Mobile Receipt</h3>
                                            <p class="text-xs text-slate-450 mt-0.5" x-text="'Ref: ' + refCode"></p>
                                        </div>
                                        <button @click="openConfirmId = null" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                                    </div>

                                    <!-- Receipt Image Screen -->
                                    <div class="w-full h-64 bg-slate-50 border rounded-2xl overflow-hidden relative">
                                        <img :src="receiptUrl" class="w-full h-full object-contain" alt="Receipt Screenshot">
                                    </div>

                                    <form action="{{ route('owner.payments.verify', $pay->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label for="notes" class="block text-xs font-semibold text-slate-700">Verification Notes</label>
                                            <textarea id="notes" name="notes" rows="2" placeholder="e.g. Reference confirmed on bank statement."
                                                      class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600"></textarea>
                                        </div>

                                        <div class="pt-4 border-t border-slate-100 flex gap-3">
                                            <button type="submit" name="action" value="reject"
                                                    class="flex-1 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-sm font-semibold rounded-xl transition-all border border-rose-100">
                                                Reject Proof
                                            </button>
                                            <button type="submit" name="action" value="approve" 
                                                    class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-emerald-50">
                                                Approve Payment
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>

                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="banknote" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold">No payments recorded.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
