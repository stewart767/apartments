<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'RelianceHomes - Premier Student Hostels & Modern Apartments Marketplace')</title>
    <meta name="description" content="@yield('meta_description', 'Discover, inspect and book verified student hostels, studio apartments, and rooms across Tanzania with instant digital contracts.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Instrument Sans', sans-serif;
        }
        [x-cloak] { display: none !important; }
        .glass-header {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .text-glow {
            text-shadow: 0 0 20px rgba(99, 102, 241, 0.35);
        }
        .badge-pulse {
            animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .75; transform: scale(1.05); }
        }
    </style>
    @yield('styles')
</head>
<body class="flex flex-col min-h-screen text-slate-800 antialiased selection:bg-indigo-500 selection:text-white"
      x-data="{
          mobileMenuOpen: false,
          wishlist: JSON.parse(localStorage.getItem('reliance_wishlist') || '[]'),
          savedItemsData: JSON.parse(localStorage.getItem('reliance_wishlist_data') || '[]'),
          wishlistDrawerOpen: false,
          toast: { show: false, message: '', type: 'success' },
          toggleWishlist(prop) {
              const id = typeof prop === 'object' ? prop.id : prop;
              const idx = this.wishlist.indexOf(id);
              if (idx > -1) {
                  this.wishlist.splice(idx, 1);
                  this.savedItemsData = this.savedItemsData.filter(item => item.id !== id);
                  this.showToast('Removed from saved stays', 'info');
              } else {
                  this.wishlist.push(id);
                  if (typeof prop === 'object') {
                      this.savedItemsData.push(prop);
                  }
                  this.showToast('❤️ Added to your saved stays!', 'success');
              }
              localStorage.setItem('reliance_wishlist', JSON.stringify(this.wishlist));
              localStorage.setItem('reliance_wishlist_data', JSON.stringify(this.savedItemsData));
              this.$nextTick(() => { lucide.createIcons(); });
          },
          isWishlisted(id) {
              return this.wishlist.includes(Number(id)) || this.wishlist.includes(String(id));
          },
          showToast(msg, type = 'success') {
              this.toast.message = msg;
              this.toast.type = type;
              this.toast.show = true;
              setTimeout(() => { this.toast.show = false; }, 4000);
          }
      }">

    <!-- 1. Top E-Commerce Promo Marquee & Notice Bar -->
    <div class="bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 text-white text-xs font-medium py-2.5 px-4 relative z-50 border-b border-indigo-900/40">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-hidden whitespace-nowrap text-xs">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-bold uppercase tracking-wider text-[10px] border border-indigo-400/20 badge-pulse">
                    <i data-lucide="sparkles" class="w-3 h-3 text-amber-300"></i>
                    Semester Special
                </span>
                <span class="text-slate-200 hidden sm:inline">
                    Early Bird Booking Discount: Use code <strong class="text-amber-300 font-semibold tracking-wider">CAMPUS2026</strong> for zero transaction fees!
                </span>
                <span class="text-slate-300 sm:hidden">
                    Zero transaction fee with code <strong class="text-amber-300">CAMPUS2026</strong>!
                </span>
            </div>

            <!-- Quick Trust Pills & Actions -->
            <div class="hidden lg:flex items-center gap-6 text-[11px] text-slate-300">
                <span class="inline-flex items-center gap-1.5 hover:text-white transition-colors">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                    100% Inspected & Verified
                </span>
                <span class="inline-flex items-center gap-1.5 hover:text-white transition-colors">
                    <i data-lucide="smartphone" class="w-3.5 h-3.5 text-indigo-400"></i>
                    M-Pesa • Tigo Pesa • Airtel
                </span>
                <a href="{{ route('properties.search') }}" class="font-semibold text-amber-300 hover:text-white underline underline-offset-2">
                    Browse All Stays &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Sticky Glassmorphic Header -->
    <header class="sticky top-0 z-40 w-full border-b border-slate-200/80 glass-header shadow-sm transition-all duration-300">
        <div class="mx-auto flex max-w-7xl h-20 items-center justify-between px-4 sm:px-6 lg:px-8 gap-4">
            
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="group flex items-center gap-2.5">
                    <div class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-300">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                        <span class="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-white text-[9px] font-black border-2 border-white shadow-sm">✓</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xl font-extrabold tracking-tight text-slate-900">Reliance<span class="text-indigo-600 font-black">Homes</span></span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 -mt-1">Verified Stays & Rentals</span>
                    </div>
                </a>
            </div>

            <!-- Marketplace Search Shortcut (E-Commerce Style in Header) -->
            <div class="hidden md:flex flex-1 max-w-md mx-6">
                <form action="{{ route('properties.search') }}" method="GET" class="w-full relative">
                    <div class="flex items-center w-full rounded-2xl border border-slate-200/90 bg-slate-50/70 hover:bg-white hover:border-indigo-300 focus-within:border-indigo-600 focus-within:bg-white focus-within:ring-4 focus-within:ring-indigo-500/10 transition-all duration-200 shadow-inner">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 ml-4 pointer-events-none"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by college, campus, area (e.g. Ubungo, UDSM)..."
                               class="w-full py-2.5 pl-3 pr-2 text-xs font-medium text-slate-800 placeholder-slate-400 bg-transparent border-none focus:outline-none focus:ring-0">
                        <button type="submit" class="m-1 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-all flex items-center gap-1">
                            <span>Find</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Navigation Links & Marketplace Utilities -->
            <nav class="hidden lg:flex items-center gap-6">
                <a href="{{ url('/') }}" class="text-sm font-semibold {{ request()->is('/') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }} transition-colors">
                    Home
                </a>
                <a href="{{ route('properties.search') }}" class="text-sm font-semibold {{ request()->routeIs('properties.search') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }} transition-colors flex items-center gap-1">
                    <span>Explore Stays</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-bold">HOT</span>
                </a>
                <a href="{{ route('pricing') }}" class="text-sm font-semibold {{ request()->routeIs('pricing') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }} transition-colors">
                    Pricing
                </a>
                <a href="{{ url('/how-it-works') }}" class="text-sm font-semibold {{ request()->is('how-it-works') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }} transition-colors">
                    How It Works
                </a>
                <a href="{{ url('/about') }}" class="text-sm font-semibold {{ request()->is('about') ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600' }} transition-colors">
                    About
                </a>
            </nav>

            <!-- Actions: Wishlist / Host CTA / Auth -->
            <div class="flex items-center gap-3">
                <!-- Wishlist Trigger Button -->
                <button type="button" @click="wishlistDrawerOpen = true"
                        class="relative p-2.5 rounded-2xl border border-slate-200/80 hover:border-rose-200 hover:bg-rose-50/50 text-slate-600 hover:text-rose-600 transition-all duration-200 shadow-sm"
                        title="View Saved Favorites">
                    <i data-lucide="heart" class="w-5 h-5"></i>
                    <span x-show="wishlist.length > 0" x-text="wishlist.length"
                          class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-white text-[10px] font-black border-2 border-white shadow-sm"
                          style="display: none;">0</span>
                </button>

                <!-- List Your Property (Promote Host Onboarding) -->
                <a href="{{ route('login') }}" class="hidden xl:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl border border-indigo-200 bg-indigo-50/60 hover:bg-indigo-100/70 text-indigo-700 text-xs font-bold transition-all duration-200">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                    <span>List Property</span>
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-800 text-xs font-bold rounded-2xl transition-all shadow-sm">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-indigo-600"></i>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex text-xs font-bold text-slate-700 hover:text-indigo-600 px-3 py-2 transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-bold rounded-2xl transition-all shadow-md shadow-indigo-500/20 hover:scale-[1.02]">
                        <span>Book a Room</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                @endauth

                <!-- Mobile Hamburger Button -->
                <div class="flex lg:hidden">
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-slate-600 hover:text-indigo-600 rounded-2xl hover:bg-slate-100 transition-colors">
                        <span class="sr-only">Toggle menu</span>
                        <i data-lucide="menu" class="w-6 h-6" x-show="!mobileMenuOpen"></i>
                        <i data-lucide="x" class="w-6 h-6" x-show="mobileMenuOpen" style="display: none;"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div class="lg:hidden border-b border-slate-200 bg-white px-5 pt-3 pb-6 space-y-4 shadow-xl" x-show="mobileMenuOpen" x-transition x-cloak style="display: none;">
            <!-- Mobile Search -->
            <form action="{{ route('properties.search') }}" method="GET" class="pt-2">
                <div class="flex items-center rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 mr-2"></i>
                    <input type="text" name="q" placeholder="Search hostels, campus, area..." class="w-full text-xs font-medium text-slate-800 bg-transparent border-none focus:outline-none">
                    <button type="submit" class="px-2.5 py-1 rounded-xl bg-indigo-600 text-white text-[11px] font-bold">Go</button>
                </div>
            </form>

            <div class="grid grid-cols-2 gap-2 pt-2">
                <a href="{{ url('/') }}" class="flex items-center gap-2 p-3 text-sm font-semibold rounded-2xl {{ request()->is('/') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="home" class="w-4 h-4"></i> Home
                </a>
                <a href="{{ route('properties.search') }}" class="flex items-center gap-2 p-3 text-sm font-semibold rounded-2xl {{ request()->routeIs('properties.search') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="compass" class="w-4 h-4"></i> Explore Stays
                </a>
                <a href="{{ url('/how-it-works') }}" class="flex items-center gap-2 p-3 text-sm font-semibold rounded-2xl text-slate-700 hover:bg-slate-50">
                    <i data-lucide="help-circle" class="w-4 h-4"></i> How It Works
                </a>
                <a href="{{ route('pricing') }}" class="flex items-center gap-2 p-3 text-sm font-semibold rounded-2xl {{ request()->routeIs('pricing') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="tag" class="w-4 h-4"></i> Pricing
                </a>
                <a href="{{ url('/about') }}" class="flex items-center gap-2 p-3 text-sm font-semibold rounded-2xl text-slate-700 hover:bg-slate-50">
                    <i data-lucide="info" class="w-4 h-4"></i> About Us
                </a>
            </div>

            <hr class="border-slate-100">

            <div class="space-y-2">
                <button type="button" @click="wishlistDrawerOpen = true; mobileMenuOpen = false" class="flex w-full items-center justify-between p-3 border border-rose-100 bg-rose-50/50 rounded-2xl text-xs font-bold text-rose-700">
                    <span class="flex items-center gap-2">
                        <i data-lucide="heart" class="w-4 h-4 text-rose-500"></i> My Saved Stays
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white text-[10px]" x-text="wishlist.length">0</span>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="flex w-full items-center justify-center gap-2 px-4 py-3 border border-slate-200 text-slate-800 text-sm font-bold rounded-2xl hover:bg-slate-50">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-indigo-600"></i> My Dashboard
                    </a>
                @else
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('login') }}" class="flex items-center justify-center px-4 py-3 border border-slate-200 text-slate-700 text-xs font-bold rounded-2xl hover:bg-slate-50">
                            Log In
                        </a>
                        <a href="{{ route('register') }}" class="flex items-center justify-center gap-1.5 px-4 py-3 bg-indigo-600 text-white text-xs font-bold rounded-2xl hover:bg-indigo-700">
                            Register
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- 3. Saved Stays / Wishlist Slide-Over Drawer -->
    <div x-show="wishlistDrawerOpen" x-cloak class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div x-show="wishlistDrawerOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="wishlistDrawerOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="wishlistDrawerOpen" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-md bg-white shadow-2xl flex flex-col">
                        
                        <!-- Drawer Header -->
                        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/70">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                                    <i data-lucide="heart" class="w-5 h-5 fill-rose-500"></i>
                                </span>
                                <div>
                                    <h2 class="text-base font-extrabold text-slate-900">Saved Accommodations</h2>
                                    <p class="text-xs text-slate-500"><span x-text="wishlist.length">0</span> rooms in your wishlist</p>
                                </div>
                            </div>
                            <button @click="wishlistDrawerOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <!-- Drawer Body -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <template x-if="wishlist.length === 0">
                                <div class="text-center py-16 space-y-4">
                                    <div class="w-16 h-16 mx-auto rounded-full bg-rose-50 flex items-center justify-center text-rose-400">
                                        <i data-lucide="heart-off" class="w-8 h-8"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">Your wishlist is empty</h3>
                                        <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">Click the heart icon on any hostel or room to save it for easy comparison and instant booking.</p>
                                    </div>
                                    <a href="{{ route('properties.search') }}" @click="wishlistDrawerOpen = false"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-700">
                                        <i data-lucide="compass" class="w-4 h-4"></i> Browse Verified Stays
                                    </a>
                                </div>
                            </template>

                            <template x-for="item in savedItemsData" :key="item.id">
                                <div class="flex items-center gap-3 p-3 bg-white border border-slate-200/80 rounded-2xl hover:shadow-md transition-all">
                                    <img :src="item.image || 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=400&q=80'" :alt="item.name" class="w-20 h-20 rounded-xl object-cover flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold text-slate-900 truncate" x-text="item.name">Property Name</h4>
                                        <p class="text-[11px] text-slate-500 truncate mt-0.5" x-text="item.location">Location</p>
                                        <p class="text-xs font-extrabold text-indigo-600 mt-1" x-text="'TZS ' + Number(item.price).toLocaleString() + '/' + (item.billing || 'mo')">TZS 150,000</p>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <a :href="'/property/' + item.id" class="p-2 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-600 rounded-xl transition-all" title="View Property">
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </a>
                                        <button @click="toggleWishlist(item)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-all" title="Remove">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Drawer Footer -->
                        <div class="p-6 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                            <button @click="wishlist = []; savedItemsData = []; localStorage.removeItem('reliance_wishlist'); localStorage.removeItem('reliance_wishlist_data'); showToast('Cleared all saved stays', 'info')"
                                    class="text-xs font-semibold text-slate-500 hover:text-rose-600" x-show="wishlist.length > 0">
                                Clear All
                            </button>
                            <a href="{{ route('properties.search') }}" @click="wishlistDrawerOpen = false" class="ml-auto inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-100">
                                <span>Find More Stays</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Main Body Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- 5. Modern E-Commerce Platform Footer -->
    <footer class="border-t border-slate-200 bg-white">
        <!-- Top Promotional Lead Banner -->
        <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 text-white py-14 px-4 sm:px-6 lg:px-8 border-b border-indigo-900/50 relative overflow-hidden">
            <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
            <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-8 relative z-10">
                <div class="space-y-2 text-center lg:text-left max-w-xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-bold uppercase tracking-wider">
                        <i data-lucide="bell" class="w-3 h-3"></i> Never Miss A Vacant Room
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Get First-Notice On New Verified Listings</h3>
                    <p class="text-sm text-slate-300">Join 15,000+ students receiving instant vacancy alerts near their colleges before peak semester rush.</p>
                </div>
                <div class="w-full max-w-md" x-data="{ email: '', subscribed: false }">
                    <form @submit.prevent="if(email) { subscribed = true; showToast('🎉 You are now subscribed to vacancy alerts!', 'success'); email = ''; }" class="flex gap-2">
                        <input type="email" x-model="email" required placeholder="Enter your email or phone number..."
                               class="w-full px-4 py-3 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-slate-950 text-xs font-extrabold shadow-lg shadow-amber-500/20 whitespace-nowrap transition-all">
                            Notify Me
                        </button>
                    </form>
                    <p class="text-[11px] text-slate-400 mt-2 text-center lg:text-left">No spam ever. 100% free alerts. Unsubscribe anytime.</p>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
                <!-- Branding & Mission -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 text-indigo-600 font-extrabold text-xl tracking-tight">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                        </div>
                        <span class="text-slate-900 font-extrabold">Reliance<span class="text-indigo-600">Homes</span></span>
                    </a>
                    <p class="text-sm text-slate-500 leading-relaxed max-w-sm">
                        Tanzania's premier verified accommodation marketplace. Eliminating predatory middleman broker fees with physical inspections, direct mobile money reservations, and legally binding digital tenancy contracts.
                    </p>
                    
                    <!-- Accepted Payments Badge -->
                    <div class="pt-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Supported Payment Channels</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700 text-xs font-bold border border-slate-200">M-PESA</span>
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700 text-xs font-bold border border-slate-200">TIGO PESA</span>
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700 text-xs font-bold border border-slate-200">AIRTEL MONEY</span>
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700 text-xs font-bold border border-slate-200">HALOPESA</span>
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-700 text-xs font-bold border border-slate-200">VISA / MASTERCARD</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Navigation -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 tracking-wider uppercase mb-4">Discover Stays</h4>
                    <ul class="space-y-2.5 text-sm text-slate-500">
                        <li><a href="{{ route('properties.search') }}" class="hover:text-indigo-600 transition-colors">Browse Marketplace</a></li>
                        <li><a href="{{ route('properties.search', ['rent_structure' => 'per_bed']) }}" class="hover:text-indigo-600 transition-colors">Student Hostels (Beds)</a></li>
                        <li><a href="{{ route('properties.search', ['rent_structure' => 'per_room']) }}" class="hover:text-indigo-600 transition-colors">Private Single Rooms</a></li>
                        <li><a href="{{ route('properties.search', ['rent_structure' => 'entire_property']) }}" class="hover:text-indigo-600 transition-colors">Entire Apartments & Houses</a></li>
                        <li><a href="{{ route('properties.search', ['verified' => 1]) }}" class="hover:text-indigo-600 transition-colors">100% Inspected Stays</a></li>
                    </ul>
                </div>

                <!-- Campuses & Regions -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 tracking-wider uppercase mb-4">Top Campuses</h4>
                    <ul class="space-y-2.5 text-sm text-slate-500">
                        <li><a href="{{ route('properties.search', ['q' => 'UDSM']) }}" class="hover:text-indigo-600 transition-colors">University of Dar es Salaam (UDSM)</a></li>
                        <li><a href="{{ route('properties.search', ['q' => 'Ardhi']) }}" class="hover:text-indigo-600 transition-colors">Ardhi University (ARU)</a></li>
                        <li><a href="{{ route('properties.search', ['q' => 'CBE']) }}" class="hover:text-indigo-600 transition-colors">College of Business Education (CBE)</a></li>
                        <li><a href="{{ route('properties.search', ['q' => 'DIT']) }}" class="hover:text-indigo-600 transition-colors">Dar Institute of Tech (DIT)</a></li>
                        <li><a href="{{ route('properties.search', ['q' => 'Mlimani']) }}" class="hover:text-indigo-600 transition-colors">Ubungo & Mwenge Hubs</a></li>
                    </ul>
                </div>

                <!-- Support & Safety -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 tracking-wider uppercase mb-4">Safety & Help</h4>
                    <ul class="space-y-2.5 text-sm text-slate-500">
                        <li><a href="{{ url('/how-it-works') }}" class="hover:text-indigo-600 transition-colors">Booking Guide</a></li>
                        <li><a href="{{ url('/about') }}" class="hover:text-indigo-600 transition-colors">Our Inspection Guarantee</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-indigo-600 transition-colors">24/7 Concierge Support</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-indigo-600 transition-colors">Owner & Host Login</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-indigo-600 transition-colors">Report Suspicious Listing</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Copyright & Socials -->
            <div class="mt-12 border-t border-slate-100 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <div class="flex items-center gap-3">
                    <p>&copy; {{ date('Y') }} RelianceHomes Technologies Ltd. All rights reserved.</p>
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ url('/about') }}" class="hover:text-indigo-600 transition-colors">Privacy Policy</a>
                    <a href="{{ url('/about') }}" class="hover:text-indigo-600 transition-colors">Terms of Service</a>
                    <a href="{{ url('/contact') }}" class="hover:text-indigo-600 transition-colors">Support Helpline: +255 712 345 678</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- 6. Mobile App-Style Floating Navigation Bar (Mobile only) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-6 py-2 shadow-2xl">
        <div class="flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex flex-col items-center gap-1 {{ request()->is('/') ? 'text-indigo-600 font-bold' : 'text-slate-500' }}">
                <i data-lucide="home" class="w-5 h-5"></i>
                <span class="text-[10px]">Home</span>
            </a>
            <a href="{{ route('properties.search') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('properties.search') ? 'text-indigo-600 font-bold' : 'text-slate-500' }}">
                <i data-lucide="search" class="w-5 h-5"></i>
                <span class="text-[10px]">Search</span>
            </a>
            <button type="button" @click="wishlistDrawerOpen = true" class="flex flex-col items-center gap-1 text-slate-500 relative">
                <i data-lucide="heart" class="w-5 h-5"></i>
                <span class="text-[10px]">Saved</span>
                <span x-show="wishlist.length > 0" x-text="wishlist.length" class="absolute -top-1 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-white text-[9px] font-black" style="display: none;">0</span>
            </button>
            @auth
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 text-slate-500">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span class="text-[10px]">Account</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 text-slate-500">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    <span class="text-[10px]">Login</span>
                </a>
            @endauth
        </div>
    </div>

    <!-- 7. Dynamic Toast Notification System -->
    <!-- Alpine Toast -->
    <div x-show="toast.show" x-cloak x-transition:enter="transform ease-out duration-300 transition" x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed bottom-16 md:bottom-6 right-4 md:right-6 z-50 flex items-center gap-3 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700 max-w-sm">
        <span class="p-1 rounded-lg bg-indigo-500/20 text-indigo-400">
            <i data-lucide="check" class="w-4 h-4"></i>
        </span>
        <p class="text-xs font-semibold" x-text="toast.message">Notification</p>
        <button @click="toast.show = false" class="text-slate-400 hover:text-white ml-auto">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>

    <!-- Backend Flash Messages -->
    @if(session('success') || session('error') || session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-16 md:bottom-6 right-4 md:right-6 z-50 flex items-center gap-3 bg-white p-4 border border-slate-100 rounded-2xl shadow-2xl shadow-slate-900/10 max-w-sm" x-transition>
            @if(session('success'))
                <span class="p-1.5 bg-emerald-50 rounded-xl text-emerald-600">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </span>
                <p class="text-xs font-bold text-slate-800">{{ session('success') }}</p>
            @elseif(session('error'))
                <span class="p-1.5 bg-rose-50 rounded-xl text-rose-600">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </span>
                <p class="text-xs font-bold text-slate-800">{{ session('error') }}</p>
            @else
                <span class="p-1.5 bg-indigo-50 rounded-xl text-indigo-600">
                    <i data-lucide="info" class="w-5 h-5"></i>
                </span>
                <p class="text-xs font-bold text-slate-800">{{ session('status') }}</p>
            @endif
            <button @click="show = false" class="text-slate-400 hover:text-slate-600 ml-auto p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
