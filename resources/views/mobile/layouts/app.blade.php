<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <title>@yield('title', 'Arena Booking') • Champion Sports Arena</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        /* Custom scrollbar hiding for horizontal carousels */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col justify-between antialiased selection:bg-emerald-500 selection:text-white">

    <!-- Mobile Device Container -->
    <div class="w-full max-w-md mx-auto min-h-screen bg-slate-900 shadow-2xl relative flex flex-col border-x border-slate-800 pb-20">

        <!-- Top Header / App Bar -->
        <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur-md border-b border-slate-800 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @hasSection('back_url')
                    <a href="@yield('back_url')" class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @else
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black shadow-lg shadow-emerald-500/20">
                        ⚡
                    </div>
                @endif
                <div>
                    <h1 class="text-sm font-bold tracking-tight text-white leading-tight">@yield('header_title', 'Champion Arena')</h1>
                    <p class="text-[11px] text-slate-400 leading-none">@yield('header_subtitle', 'Sports & Court Booking')</p>
                </div>
            </div>

            <!-- Header Right Action -->
            <div class="flex items-center space-x-2">
                @auth
                    <a href="{{ route('mobile.profile') }}" class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-emerald-400">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </a>
                @else
                    <a href="{{ route('mobile.login') }}" class="text-xs px-2.5 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold transition">
                        Login
                    </a>
                @endauth
            </div>
        </header>

        <!-- Flash Alert Messages -->
        @if (session('success'))
            <div class="mx-4 mt-3 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-4 mt-3 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span>⚠</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        @endif

        @if (session('info'))
            <div class="mx-4 mt-3 p-3 rounded-xl bg-sky-500/10 border border-sky-500/30 text-sky-400 text-xs flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span>ℹ</span>
                    <span>{{ session('info') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        @endif

        <!-- Main Content Slot -->
        <main class="flex-1 p-4">
            @yield('content')
        </main>

        <!-- Floating Bottom Navigation Tab Bar -->
        <nav class="fixed bottom-0 left-0 right-0 z-50">
            <div class="max-w-md mx-auto bg-slate-900/95 backdrop-blur-lg border-t border-slate-800 px-6 py-2.5 flex items-center justify-between shadow-2xl">
                <!-- Tab 1: Explore -->
                <a href="{{ route('mobile.home') }}" class="flex flex-col items-center group {{ request()->routeIs('mobile.home*') || request()->routeIs('mobile.court*') ? 'text-emerald-400' : 'text-slate-400 hover:text-slate-200' }} transition">
                    <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-[10px] font-semibold">Explore</span>
                </a>

                <!-- Tab 2: My Bookings -->
                <a href="{{ route('mobile.my-bookings') }}" class="flex flex-col items-center group {{ request()->routeIs('mobile.my-bookings*') ? 'text-emerald-400' : 'text-slate-400 hover:text-slate-200' }} transition">
                    <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    <span class="text-[10px] font-semibold">My Tickets</span>
                </a>

                <!-- Tab 3: Profile -->
                <a href="{{ route('mobile.profile') }}" class="flex flex-col items-center group {{ request()->routeIs('mobile.profile*') ? 'text-emerald-400' : 'text-slate-400 hover:text-slate-200' }} transition">
                    <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-[10px] font-semibold">Profile</span>
                </a>
            </div>
        </nav>

    </div>

    @stack('scripts')
</body>
</html>
