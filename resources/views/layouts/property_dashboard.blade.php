@extends('layouts.admin')

@section('title', $property->property_name)

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Property Info Header Card -->
    <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('owner.properties') }}" class="p-1.5 hover:bg-slate-50 border border-slate-150 rounded-xl text-slate-500 hover:text-indigo-650 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">{{ $property->propertyType->name }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-2">{{ $property->property_name }}</h1>
            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>{{ $property->address }}, {{ $property->ward->name }}, {{ $property->district->name }}, {{ $property->region->name }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold uppercase border {{ $property->status === 'published' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-50 text-slate-700 border-slate-150' }}">
                {{ $property->status }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold uppercase border {{ $property->verification_status === 'approved' ? 'bg-indigo-50 text-indigo-700 border-indigo-150' : 'bg-amber-50 text-amber-700 border-amber-150' }}">
                {{ $property->verification_status }}
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-8">
        <nav class="-mb-px flex gap-6 overflow-x-auto" aria-label="Tabs">
            <a href="{{ route('owner.properties.dashboard', $property->id) }}" 
               class="shrink-0 border-b-2 px-1 pb-4 text-sm font-semibold transition-all {{ request()->routeIs('owner.properties.dashboard') ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                <i data-lucide="bar-chart-2" class="w-4 h-4 inline-block mr-1.5 align-text-bottom"></i>
                Overview
            </a>

            <a href="{{ route('owner.properties.buildings-floors', $property->id) }}" 
               class="shrink-0 border-b-2 px-1 pb-4 text-sm font-semibold transition-all {{ request()->routeIs('owner.properties.buildings-floors') ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                <i data-lucide="layers" class="w-4 h-4 inline-block mr-1.5 align-text-bottom"></i>
                Buildings & Floors
            </a>

            <a href="{{ route('owner.properties.rooms-beds', $property->id) }}" 
               class="shrink-0 border-b-2 px-1 pb-4 text-sm font-semibold transition-all {{ request()->routeIs('owner.properties.rooms-beds') ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                <i data-lucide="layout-grid" class="w-4 h-4 inline-block mr-1.5 align-text-bottom"></i>
                Rooms & Beds
            </a>

            <!-- Assets tab -->
            <a href="{{ route('owner.properties.assets', $property->id) }}" 
               class="shrink-0 border-b-2 px-1 pb-4 text-sm font-semibold transition-all {{ request()->routeIs('owner.properties.assets*') ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                <i data-lucide="package" class="w-4 h-4 inline-block mr-1.5 align-text-bottom"></i>
                Assets
            </a>

            <!-- Staff tab -->
            <a href="{{ route('owner.properties.staff', $property->id) }}" 
               class="shrink-0 border-b-2 px-1 pb-4 text-sm font-semibold transition-all {{ request()->routeIs('owner.properties.staff*') ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                <i data-lucide="users" class="w-4 h-4 inline-block mr-1.5 align-text-bottom"></i>
                Staff / Team
            </a>
        </nav>
    </div>

    <!-- Tab Content -->
    @yield('property_content')
</div>
@endsection
