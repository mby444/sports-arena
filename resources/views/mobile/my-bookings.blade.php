@extends('mobile.layouts.app')

@section('title', 'My Bookings')
@section('header_title', 'My Tickets')
@section('header_subtitle', 'Active & Past Bookings')

@section('content')
<div class="space-y-4">

    <!-- Tab Filter Header -->
    <div class="grid grid-cols-3 gap-1 p-1 bg-slate-800/80 rounded-xl border border-slate-700/60 text-xs font-bold text-center">
        <a href="{{ route('mobile.my-bookings', ['tab' => 'upcoming']) }}" class="py-2 rounded-lg transition {{ $tab === 'upcoming' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
            Upcoming ({{ $upcoming->count() }})
        </a>
        <a href="{{ route('mobile.my-bookings', ['tab' => 'completed']) }}" class="py-2 rounded-lg transition {{ $tab === 'completed' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
            Done ({{ $completed->count() }})
        </a>
        <a href="{{ route('mobile.my-bookings', ['tab' => 'cancelled']) }}" class="py-2 rounded-lg transition {{ $tab === 'cancelled' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
            Expired ({{ $cancelled->count() }})
        </a>
    </div>

    @php
        $currentList = match($tab) {
            'completed' => $completed,
            'cancelled' => $cancelled,
            default => $upcoming,
        };
    @endphp

    <!-- Booking Cards List -->
    <div class="space-y-3">
        @forelse ($currentList as $booking)
            <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700/70 shadow-lg space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $booking->court->sport_type === 'badminton' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                                {{ $booking->court->sport_type === 'badminton' ? '🏸 Badminton' : '⚽ Futsal' }}
                            </span>
                            <span class="text-[11px] font-mono text-slate-400">{{ $booking->booking_code }}</span>
                        </div>
                        <h4 class="text-sm font-extrabold text-white mt-1">{{ $booking->court->name }}</h4>
                        <p class="text-xs text-slate-400">{{ $booking->court->venue->name }}</p>
                    </div>

                    <div>
                        @if ($booking->isPaid())
                            <span class="text-[10px] px-2.5 py-1 rounded-full font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">
                                PAID
                            </span>
                        @elseif ($booking->isLockActive())
                            <span class="text-[10px] px-2.5 py-1 rounded-full font-black bg-amber-500/20 text-amber-400 border border-amber-500/30 uppercase">
                                PENDING
                            </span>
                        @else
                            <span class="text-[10px] px-2.5 py-1 rounded-full font-black bg-slate-700 text-slate-400 uppercase">
                                {{ strtoupper($booking->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Session Details -->
                <div class="py-2 px-3 rounded-xl bg-slate-900/60 border border-slate-700/40 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Date</span>
                        <span class="font-bold text-white">{{ $booking->booking_date->format('d M Y') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block text-[10px]">Hours ({{ $booking->slots->count() }} hrs)</span>
                        <span class="font-bold text-emerald-400">
                            @foreach ($booking->slots as $slot)
                                {{ substr($slot->start_time, 0, 5) }}@if (!$loop->last), @endif
                            @endforeach
                        </span>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs font-black text-white">
                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                    </span>

                    @if ($booking->isPaid())
                        <a href="{{ route('mobile.success', $booking) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-emerald-500/30 transition flex items-center space-x-1">
                            <span>View Ticket</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @elseif ($booking->isLockActive())
                        <a href="{{ route('mobile.checkout', $booking) }}" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition flex items-center space-x-1">
                            <span>Pay Now</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="text-xs text-slate-500">Expired</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-slate-800/40 rounded-2xl border border-slate-800">
                <span class="text-3xl block mb-2">🎟️</span>
                <p class="text-slate-300 text-sm font-semibold">No bookings in this tab.</p>
                <p class="text-slate-500 text-xs mt-1">Ready for a match? Book your court now!</p>
                <a href="{{ route('mobile.home') }}" class="inline-block mt-4 px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 font-bold text-xs">
                    Explore Courts
                </a>
            </div>
        @endforelse
    </div>

</div>
@endsection
