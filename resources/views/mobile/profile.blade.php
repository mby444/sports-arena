@extends('mobile.layouts.app')

@section('title', 'Profile')
@section('header_title', 'My Account')
@section('header_subtitle', 'Profile & Settings')

@section('content')
<div class="space-y-5">

    <!-- Profile Card -->
    <div class="p-6 rounded-3xl bg-gradient-to-b from-slate-800 to-slate-800/70 border border-slate-700/80 shadow-xl flex items-center space-x-4">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center font-black text-2xl text-slate-950 shadow-lg shadow-emerald-500/20">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <div class="flex items-center space-x-2">
                <h3 class="text-base font-extrabold text-white">{{ $user->name }}</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $user->isAdmin() ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                    {{ $user->role }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</p>
            <p class="text-xs text-slate-500">{{ $user->phone ?? 'No phone recorded' }}</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-3">
        <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60">
            <span class="text-slate-400 text-xs block">Total Bookings</span>
            <span class="text-2xl font-black text-white mt-1 block">{{ $totalBookings }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60">
            <span class="text-slate-400 text-xs block">Confirmed Paid</span>
            <span class="text-2xl font-black text-emerald-400 mt-1 block">{{ $paidBookings }}</span>
        </div>
    </div>

    <!-- Quick Links & Actions -->
    <div class="rounded-2xl bg-slate-800/80 border border-slate-700/70 overflow-hidden divide-y divide-slate-700/60">
        <a href="{{ route('mobile.my-bookings') }}" class="p-4 flex items-center justify-between hover:bg-slate-750 transition text-xs font-semibold text-slate-200">
            <div class="flex items-center space-x-3">
                <span class="text-base">🎟️</span>
                <span>My Reservation History</span>
            </div>
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        @if ($user->isAdmin())
            <a href="/admin/dashboard" class="p-4 flex items-center justify-between hover:bg-slate-750 transition text-xs font-semibold text-amber-400">
                <div class="flex items-center space-x-3">
                    <span class="text-base">⚙️</span>
                    <span>Venue Admin Dashboard</span>
                </div>
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endif
    </div>

    <!-- Logout Button -->
    <form action="{{ route('mobile.logout') }}" method="POST">
        @csrf
        <button type="submit" class="w-full py-3.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-bold transition flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span>Log Out</span>
        </button>
    </form>

</div>
@endsection
