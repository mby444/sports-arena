@extends('mobile.layouts.app')

@section('title', 'Explore Courts')
@section('header_title', 'Champion Arena')
@section('header_subtitle', 'Jakarta Timur • Open 08:00 - 22:00')

@section('content')
<div class="space-y-5">

    <!-- Venue Hero Showcase -->
    @foreach ($venues as $venue)
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-slate-800 to-slate-900 border border-slate-800 shadow-xl">
            <div class="h-44 w-full relative overflow-hidden">
                <img src="{{ $venue->image_url ?? 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800&auto=format&fit=crop&q=80' }}" alt="{{ $venue->name }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
                <div class="absolute top-3 left-3 bg-emerald-500/90 backdrop-blur-md text-slate-950 font-extrabold text-[11px] px-2.5 py-1 rounded-full shadow-md flex items-center space-x-1">
                    <span>★ 4.9 (120+ Reviews)</span>
                </div>
                <div class="absolute top-3 right-3 bg-slate-900/80 backdrop-blur-md text-slate-200 text-[11px] px-2.5 py-1 rounded-full border border-slate-700">
                    {{ $venue->courts_count }} Active Courts
                </div>
            </div>

            <div class="p-4 -mt-2 relative">
                <h2 class="text-lg font-extrabold text-white">{{ $venue->name }}</h2>
                <p class="text-xs text-slate-400 mt-1 flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                    {{ $venue->address }}
                </p>
                <p class="text-xs text-slate-300 mt-2 line-clamp-2 leading-relaxed">
                    {{ $venue->description }}
                </p>
            </div>
        </div>
    @endforeach

    <!-- Sport Category Filter Chips -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Select Sport</h3>
            <span class="text-[11px] text-emerald-400 font-semibold">{{ $courts->count() }} Available</span>
        </div>

        <div class="flex items-center space-x-2 no-scrollbar overflow-x-auto pb-1">
            <a href="{{ route('mobile.home') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 {{ empty($currentSport) ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                All Courts
            </a>
            <a href="{{ route('mobile.home', ['sport' => 'badminton']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center space-x-1.5 {{ $currentSport === 'badminton' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                <span>🏸</span>
                <span>Badminton</span>
            </a>
            <a href="{{ route('mobile.home', ['sport' => 'futsal']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center space-x-1.5 {{ $currentSport === 'futsal' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                <span>⚽</span>
                <span>Futsal</span>
            </a>
        </div>
    </div>

    <!-- Court Cards List -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Available Courts</h3>

        @forelse ($courts as $court)
            <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700/60 hover:border-slate-600 transition flex flex-col justify-between space-y-3 shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold uppercase {{ $court->sport_type === 'badminton' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                                {{ $court->sport_type === 'badminton' ? '🏸 Badminton' : '⚽ Futsal' }}
                            </span>
                            <span class="text-[11px] text-slate-400">BWF / FIFA Spec</span>
                        </div>
                        <h4 class="text-base font-bold text-white mt-1">{{ $court->name }}</h4>
                        <p class="text-xs text-slate-400">Hours: {{ substr($court->open_time, 0, 5) }} – {{ substr($court->close_time, 0, 5) }}</p>
                    </div>

                    <div class="text-right">
                        <span class="text-xs text-slate-400">Rate</span>
                        <p class="text-sm font-extrabold text-emerald-400">Rp {{ number_format($court->hourly_rate, 0, ',', '.') }}<span class="text-[10px] text-slate-400 font-normal">/hr</span></p>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-700/50 flex items-center justify-between">
                    <div class="flex items-center space-x-3 text-[11px] text-slate-400">
                        <span class="flex items-center">✓ 1-Hr Slots</span>
                        <span class="flex items-center">⚡ Instant Lock</span>
                    </div>

                    <a href="{{ route('mobile.court', $court) }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold text-xs shadow-md shadow-emerald-500/20 transition flex items-center space-x-1">
                        <span>Book Now</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-10 bg-slate-800/40 rounded-2xl border border-slate-800">
                <p class="text-slate-400 text-sm">No courts found for this category.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
