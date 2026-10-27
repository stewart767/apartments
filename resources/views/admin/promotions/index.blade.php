@extends('layouts.admin')

@section('title', 'Featured Property Promotions')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between border-b border-slate-200 pb-6 mb-8">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                Marketplace Monetization
            </span>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Featured Property Promotions</h1>
            <p class="mt-1 text-sm text-slate-500">
                Review and approve paid marketplace promotions to boost properties in search and category rankings.
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

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900">Promotion Orders</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">Property</th>
                        <th class="py-3 px-6">Landlord</th>
                        <th class="py-3 px-6">Tier & Duration</th>
                        <th class="py-3 px-6">Price</th>
                        <th class="py-3 px-6">Payment Ref</th>
                        <th class="py-3 px-6">Status</th>
                        <th class="py-3 px-6">Active Period</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($promotions as $promo)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-6 font-bold text-slate-900">
                                <a href="{{ route('properties.show', $promo->property_id) }}" target="_blank" class="hover:text-indigo-600">
                                    {{ $promo->property->property_name ?? 'Property #' . $promo->property_id }}
                                </a>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-semibold text-slate-800">{{ $promo->organization->name ?? ($promo->property->owner->name ?? 'N/A') }}</span>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-bold text-slate-900">{{ $promo->tier_name }}</span>
                                <span class="text-slate-400 block text-[11px]">({{ $promo->duration_days }} days)</span>
                            </td>
                            <td class="py-3.5 px-6 font-extrabold text-slate-900">
                                {{ $promo->formatted_amount }}
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-mono text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ $promo->payment_reference }}</span>
                                @if($promo->receipt_path)
                                    <a href="{{ asset('storage/' . $promo->receipt_path) }}" target="_blank" class="text-indigo-600 font-bold hover:underline block text-[11px] mt-1">
                                        View Slip
                                    </a>
                                @endif
                            </td>
                            <td class="py-3.5 px-6">
                                @if($promo->isActive())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                                @elseif($promo->status === 'pending_payment')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Pending Review</span>
                                @elseif($promo->isExpired())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">Expired</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">{{ ucfirst($promo->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-[11px]">
                                @if($promo->starts_at && $promo->expires_at)
                                    <span>{{ $promo->starts_at->format('M d') }} - {{ $promo->expires_at->format('M d, Y') }}</span>
                                @else
                                    <span class="text-slate-400">Pending activation</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                @if($promo->status === 'pending_payment')
                                    <div class="inline-flex items-center gap-2">
                                        <form action="{{ route('admin.promotions.verify', $promo->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="py-1 px-3 rounded-lg bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow-sm">
                                                Activate
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.promotions.verify', $promo->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="py-1 px-3 rounded-lg bg-slate-200 text-slate-700 font-bold text-xs hover:bg-rose-100 hover:text-rose-700">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @elseif($promo->isActive())
                                    <form action="{{ route('admin.promotions.expire', $promo->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">
                                            Revoke / Expire
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">No featured promotion orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $promotions->links() }}
        </div>
    </div>

</div>
@endsection
