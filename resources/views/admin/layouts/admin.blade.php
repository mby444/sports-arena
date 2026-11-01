<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') • Champion Sports Arena</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex antialiased">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between shrink-0">
        <div>
            <!-- Arena Logo Header -->
            <div class="p-6 border-b border-slate-800 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black shadow-lg shadow-emerald-500/20">
                    ⚡
                </div>
                <div>
                    <h2 class="text-sm font-black text-white leading-tight">Champion Arena</h2>
                    <span class="text-[10px] text-amber-400 uppercase font-bold tracking-wider">Venue Admin</span>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <span class="text-base">📊</span>
                    <span>Dashboard & Matrix</span>
                </a>

                <a href="{{ route('admin.courts') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.courts*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <span class="text-base">🏟️</span>
                    <span>Courts & Pricing</span>
                </a>

                <a href="{{ route('admin.bookings') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.bookings*') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <span class="text-base">📋</span>
                    <span>Reservations Log</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800 space-y-2">
            <a href="{{ route('mobile.home') }}" target="_blank" class="flex items-center justify-between px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-750 text-slate-300 text-xs font-semibold transition">
                <span class="flex items-center space-x-2">
                    <span>📱</span>
                    <span>Open Mobile View</span>
                </span>
                <span class="text-emerald-400 text-[10px]">↗</span>
            </a>

            <div class="p-3 rounded-xl bg-slate-850 flex items-center justify-between text-xs">
                <div>
                    <span class="font-bold text-white block">{{ Auth::user()->name }}</span>
                    <span class="text-[10px] text-slate-400">{{ Auth::user()->email }}</span>
                </div>
                <form action="{{ route('mobile.logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="text-slate-400 hover:text-rose-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top App Bar -->
        <header class="h-16 bg-slate-900 border-b border-slate-800 px-8 flex items-center justify-between shrink-0">
            <div>
                <h1 class="text-base font-bold text-white">@yield('page_title', 'Admin Portal')</h1>
                <p class="text-xs text-slate-400">@yield('page_subtitle', 'Manage operations, schedule matrix, and bookings')</p>
            </div>

            <div class="flex items-center space-x-3">
                <span class="text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded-full font-bold">
                    ● System Live
                </span>
            </div>
        </header>

        <!-- Alerts -->
        <div class="px-8 pt-4">
            @if (session('success'))
                <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center justify-between">
                    <span class="font-semibold">✓ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
                </div>
            @endif

            @if (session('info'))
                <div class="p-3.5 rounded-xl bg-sky-500/10 border border-sky-500/30 text-sky-400 text-xs flex items-center justify-between">
                    <span class="font-semibold">ℹ {{ session('info') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center justify-between">
                    <span class="font-semibold">⚠ {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
                </div>
            @endif
        </div>

        <main class="p-8">
            @yield('content')
        </main>
    </div>

</body>
</html>
