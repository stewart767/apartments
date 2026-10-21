@extends('layouts.admin')

@section('title', 'My Rent & Invoices')
@section('page_title', 'My Invoices')
@section('breadcrumb_title', 'Invoices')

@section('content')
<div class="space-y-6" x-data="{ openUploadId: null }">

    <!-- Payment List -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-900">Due Invoices & Statements</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-650">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Reference</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Due Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 font-bold text-right pr-8">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($payments as $pay)
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-950">{{ $pay->payment_reference }}</td>
                            <td class="px-6 py-4 capitalize">{{ $pay->payment_type }}</td>
                            <td class="px-6 py-4">TZS {{ number_format($pay->amount) }}</td>
                            <td class="px-6 py-4 text-xs">
                                {{ $pay->due_date ? \Carbon\Carbon::parse($pay->due_date)->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                    @if($pay->status === 'paid') bg-emerald-50 text-emerald-700
                                    @elseif($pay->status === 'partially_paid') bg-blue-50 text-blue-700
                                    @elseif($pay->status === 'pending') bg-amber-50 text-amber-700
                                    @else bg-rose-50 text-rose-700 @endif">
                                    @if($pay->status === 'partially_paid')
                                        Awaiting Review
                                    @else
                                        {{ $pay->status }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right pr-8">
                                @if($pay->status === 'pending')
                                    <button @click="openUploadId = '{{ $pay->id }}'" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                        <span>Submit Proof</span>
                                    </button>
                                @elseif($pay->receipt_path)
                                    <a href="{{ asset('storage/' . $pay->receipt_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all">
                                        <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>View Receipt</span>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">No actions</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Manual Upload Form Modal (Alpine.js powered) -->
                        <template x-if="openUploadId == '{{ $pay->id }}'">
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4">
                                <div class="bg-white border border-slate-100 rounded-3xl p-6 md:p-8 max-w-md w-full shadow-2xl space-y-6"
                                     @click.away="openUploadId = null">
                                    
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                        <h3 class="text-base font-bold text-slate-950">Submit Rent Payment Proof</h3>
                                        <button @click="openUploadId = null" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                                    </div>

                                    <form action="{{ route('tenant.payments.submit', $pay->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label for="payment_method" class="block text-xs font-semibold text-slate-700">Payment Channel/Method</label>
                                            <select id="payment_method" name="payment_method" required
                                                    class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 bg-white">
                                                <option value="M-Pesa">M-Pesa Mobile Money</option>
                                                <option value="Tigo Pesa">Tigo Pesa Mobile Money</option>
                                                <option value="Airtel Money">Airtel Money Mobile Money</option>
                                                <option value="Bank Transfer">CRDB/NMB Bank Transfer</option>
                                                <option value="Cash">Cash Deposit</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="payment_reference" class="block text-xs font-semibold text-slate-700">Transaction Reference Code / ID</label>
                                            <input type="text" id="payment_reference" name="payment_reference" required placeholder="e.g. PP260825XX"
                                                   class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                        </div>

                                        <div>
                                            <label for="receipt" class="block text-xs font-semibold text-slate-700">Upload Receipt Screenshot (Max 3MB)</label>
                                            <input type="file" id="receipt" name="receipt" required accept="image/*"
                                                   class="mt-1.5 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                        </div>

                                        <div class="pt-4 border-t border-slate-100 flex gap-3">
                                            <button type="button" @click="openUploadId = null" 
                                                    class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                                            <button type="submit" 
                                                    class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-50">Submit Proof</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>

                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="wallet" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold">No invoices generated.</p>
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
