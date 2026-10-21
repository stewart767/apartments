<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Authentication') - RelianceHomes</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>
<body class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8 text-slate-800 antialiased bg-slate-50 relative overflow-y-auto">
    <!-- Beautiful Background Decorative Gradients -->
    <div class="absolute top-0 left-0 w-80 h-80 bg-indigo-200/30 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-200/30 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="flex items-center justify-center gap-2 text-indigo-600 font-bold text-2xl tracking-tight mb-6">
            <span class="p-2.5 bg-indigo-50 rounded-2xl text-indigo-600">
                <i data-lucide="home" class="w-6 h-6"></i>
            </span>
            <span>Reliance<span class="text-slate-900 font-semibold text-lg">Homes</span></span>
        </a>
        <h2 class="text-center text-2xl font-bold tracking-tight text-slate-900">
            @yield('header_title')
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
            @yield('header_subtitle')
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10 px-4 sm:px-0">
        <div class="bg-white px-6 py-8 border border-slate-100 rounded-3xl shadow-xl shadow-slate-200/50">
            @yield('content')
        </div>
    </div>

    <!-- Toast Notification -->
    @if(session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed bottom-4 right-4 z-50 flex items-center gap-3 bg-white p-4 border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 max-w-sm" x-transition>
            @if(session('success'))
                <span class="p-1 bg-emerald-50 rounded-lg text-emerald-600">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </span>
                <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
            @else
                <span class="p-1 bg-rose-50 rounded-lg text-rose-600">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </span>
                <p class="text-sm font-medium text-slate-700">{{ session('error') }}</p>
            @endif
            <button @click="show = false" class="text-slate-400 hover:text-slate-600 ml-auto">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
