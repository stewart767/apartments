@extends('layouts.admin')

@section('title', 'Submit Maintenance Request')
@section('page_title', 'Report Room Repair')
@section('breadcrumb_title', 'Submit Ticket')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Info alert banner -->
    <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-4 flex gap-3 text-xs text-slate-700 leading-relaxed">
        <span class="p-1 bg-indigo-50 text-indigo-600 rounded-lg h-fit"><i data-lucide="info" class="w-4 h-4"></i></span>
        <div>
            <p class="font-bold text-slate-900">Auto-Linked Tenancy Allocation</p>
            <p class="mt-0.5">This ticket is automatically registered for room allocation <span class="font-semibold text-slate-800">Room {{ $activeLease->room->room_number }}</span> at property <span class="font-semibold text-slate-800">{{ $activeLease->property->property_name }}</span>.</p>
        </div>
    </div>

    <!-- Form block -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm">
        <form action="{{ route('tenant.maintenance.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-semibold text-slate-700">Issue Title / Summary</label>
                <input type="text" id="title" name="title" required placeholder="e.g. Toilet flush not working, Leaking ceiling"
                       class="mt-1.5 block w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
            </div>

            <!-- Priority -->
            <div>
                <label for="priority" class="block text-xs font-semibold text-slate-700">Priority Rating</label>
                <select id="priority" name="priority" required
                        class="mt-1.5 block w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 bg-white">
                    <option value="low">Low (Non-urgent repair, minor nuisance)</option>
                    <option value="medium" selected>Medium (Standard repair, doesn't block room use)</option>
                    <option value="high">High (Urgent, blocks vital room facility)</option>
                    <option value="emergency">Emergency (Safety risk, flooding, total power cut)</option>
                </select>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700">Detailed Description</label>
                <textarea id="description" name="description" rows="5" required placeholder="Please describe the issue in detail, indicating when it started and what room section is affected."
                          class="mt-1.5 block w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600"></textarea>
            </div>

            <!-- Photos -->
            <div>
                <label for="photos" class="block text-xs font-semibold text-slate-700">Attach Damage Photos (Up to 3 images)</label>
                <input type="file" id="photos" name="photos[]" multiple accept="image/*"
                       class="mt-1.5 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-slate-100 flex gap-3">
                <a href="{{ route('tenant.maintenance') }}" 
                   class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">Cancel</a>
                <button type="submit" 
                        class="flex-1 inline-flex justify-center items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-100">
                    <i data-lucide="check-square" class="w-4 h-4"></i>
                    <span>Submit Ticket</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
