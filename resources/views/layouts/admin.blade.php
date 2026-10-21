<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - RelianceHomes</title>

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
        [x-cloak] { display: none !important; }
    </style>
    @yield('styles')
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm md:hidden" 
         x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak></div>

    <!-- Sidebar Component -->
    <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white md:sticky md:flex"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
           x-transition:enter="transition ease-out duration-300 transform"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-300 transform"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           x-cloak>
        
        <!-- Sidebar Brand Logo -->
        <div class="flex h-16 items-center gap-2 border-b border-slate-200 px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-indigo-600 font-bold text-xl tracking-tight">
                <span class="p-1.5 bg-indigo-50 rounded-xl text-indigo-600">
                    <i data-lucide="home" class="w-5 h-5"></i>
                </span>
                <span>Reliance<span class="text-slate-900 font-semibold text-sm">Homes</span></span>
            </a>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-grow space-y-1.5 px-4 py-6 overflow-y-auto">
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Dashboard</span>
            </a>

            <!-- SUPER ADMIN Links -->
            @if(auth()->user()->isSuperadmin())
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">System Administration</div>
                <a href="{{ route('admin.properties') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.properties*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>All Properties</span>
                </a>
                <a href="{{ route('admin.applications') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.applications*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="file-check-2" class="w-4 h-4"></i>
                    <span>All Bookings</span>
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.users*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>User Accounts</span>
                </a>
                <a href="{{ route('admin.amenities') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.amenities*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Amenities Manager</span>
                </a>
                <a href="{{ route('admin.reports') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.reports*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>Reports & Analytics</span>
                </a>
            @endif

            <!-- PROPERTY OWNER & AGENT Links -->
            @if(auth()->user()->isOwner() || auth()->user()->isAgent())
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Property Operations</div>
                <a href="{{ route('owner.properties') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('owner.properties*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="building" class="w-4 h-4"></i>
                    <span>My Properties</span>
                </a>
                <a href="{{ route('owner.applications') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('owner.applications*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    <span>Applications</span>
                </a>
                <a href="{{ route('owner.contracts') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('owner.contracts*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="file-signature" class="w-4 h-4"></i>
                    <span>Tenancy Contracts</span>
                </a>
                <a href="{{ route('owner.payments') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('owner.payments*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    <span>Payments Received</span>
                </a>
                <a href="{{ route('owner.maintenance') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('owner.maintenance*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    <span>Maintenance Tickets</span>
                </a>
                <a href="{{ route('owner.reports') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('owner.reports*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>Reports & Analytics</span>
                </a>
            @endif

            <!-- PROPERTY MANAGER & RECEPTION Links -->
            @if(auth()->user()->isManager() || auth()->user()->isReceptionist())
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Hostel Management</div>
                <a href="{{ route('manager.properties') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('manager.properties*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="building" class="w-4 h-4"></i>
                    <span>Managed Properties</span>
                </a>
                <a href="{{ route('manager.applications') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('manager.applications*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    <span>Applications</span>
                </a>
                <a href="{{ route('manager.contracts') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('manager.contracts*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="file-signature" class="w-4 h-4"></i>
                    <span>Tenancy Contracts</span>
                </a>
                <a href="{{ route('manager.checkins') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('manager.checkins*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="door-open" class="w-4 h-4"></i>
                    <span>Check-In / Check-Out</span>
                </a>
                <a href="{{ route('manager.maintenance') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('manager.maintenance*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    <span>Maintenance Tickets</span>
                </a>
            @endif

            <!-- TENANT Links -->
            @if(auth()->user()->isTenant())
                <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">My Tenancy</div>
                <a href="{{ route('tenant.applications') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('tenant.applications*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>My Applications</span>
                </a>
                <a href="{{ route('tenant.contracts') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('tenant.contracts*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="file-signature" class="w-4 h-4"></i>
                    <span>My Contracts</span>
                </a>
                <a href="{{ route('tenant.payments') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('tenant.payments*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span>My Payments</span>
                </a>
                <a href="{{ route('tenant.maintenance') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('tenant.maintenance*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    <span>Maintenance Tickets</span>
                </a>
            @endif

            <!-- GENERAL Links -->
            <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Account Settings</div>
            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('profile') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i data-lucide="user-cog" class="w-4 h-4"></i>
                <span>Profile Management</span>
            </a>
        </nav>

        <!-- Sidebar User Profile Info & Sign Out -->
        <div class="border-t border-slate-200 p-4">
            <div class="flex items-center gap-3 mb-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </span>
                <div class="overflow-hidden">
                    <p class="text-sm font-semibold text-slate-950 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 capitalize">{{ auth()->user()->role }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-rose-600 border border-rose-100 hover:bg-rose-50 rounded-xl transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Workspace Area -->
    <div class="flex flex-1 flex-col overflow-hidden">
        
        <!-- Top bar Header -->
        <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6">
            <!-- Mobile Toggle Button -->
            <button type="button" @click="sidebarOpen = true" class="p-2 text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-50 md:hidden">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            <!-- Breadcrumbs -->
            <div class="hidden sm:block">
                <nav class="flex text-sm text-slate-500 gap-2 items-center">
                    <a href="{{ url('/') }}" class="hover:text-indigo-600">Home</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                    <span class="text-slate-800 font-medium">@yield('breadcrumb_title', 'Dashboard')</span>
                </nav>
            </div>

            <!-- Profile Actions / Notifications trigger -->
            <div class="flex items-center gap-4">
                <!-- Notifications Bell -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl relative transition-all">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-1 right-1 flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                            </span>
                        @endif
                    </button>
                    <!-- Dropdown content -->
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-xl py-3 z-50 text-sm" x-transition>
                        <div class="px-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                            <span class="font-bold text-slate-900">Notifications</span>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <a href="{{ route('notifications.markAllRead') }}" class="text-xs text-indigo-600 hover:underline">Mark all read</a>
                            @endif
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            @forelse(auth()->user()->unreadNotifications as $n)
                                <div class="px-4 py-3 hover:bg-slate-50 border-b border-slate-100">
                                    <p class="text-slate-700 text-xs font-medium">{{ $n->data['message'] ?? 'Notification received' }}</p>
                                    <span class="text-[10px] text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-center text-slate-400">
                                    <i data-lucide="bell-off" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                    <p class="text-xs">No unread notifications</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="flex-1 overflow-y-auto px-6 py-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mb-6">@yield('page_title')</h1>
            
            @yield('content')
        </main>
    </div>

    <!-- Toast Success/Error message notifications -->
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
    @yield('scripts')
</body>
</html>
