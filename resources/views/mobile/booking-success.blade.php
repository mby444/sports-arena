@extends('mobile.layouts.app')

@section('title', 'Booking Confirmed • ' . $booking->booking_code)
@section('header_title', 'E-Ticket Pass')
@section('header_subtitle', 'Confirmed & Paid')

@section('content')
<div class="space-y-5 text-center py-2">

    <!-- Celebration Badge -->
    <div class="flex flex-col items-center">
        <div class="w-16 h-16 rounded-3xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-3xl shadow-xl shadow-emerald-500/20 ring-4 ring-emerald-500/30 animate-bounce">
            ✓
        </div>
        <h2 class="text-xl font-black text-white mt-3">Booking Confirmed!</h2>
        <p class="text-xs text-slate-400 mt-1">Show this digital pass at the arena check-in desk.</p>
    </div>

    <!-- E-Ticket Card -->
    <div class="p-6 rounded-3xl bg-gradient-to-b from-slate-800 to-slate-900 border border-emerald-500/30 shadow-2xl relative overflow-hidden text-left space-y-5">
        <!-- Top Ticket Header -->
        <div class="flex items-start justify-between pb-4 border-b border-dashed border-slate-700">
            <div>
                <span class="text-[10px] bg-emerald-500 text-slate-950 px-2.5 py-0.5 rounded-full font-black uppercase">
                    PAID • {{ $booking->payment_method ?? 'ONLINE' }}
                </span>
                <h3 class="text-lg font-black text-white mt-2">{{ $booking->court->name }}</h3>
                <p class="text-xs text-slate-400">{{ $booking->court->venue->name }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 uppercase font-semibold">Booking Code</span>
                <p class="text-sm font-mono font-black text-emerald-400 tracking-wider">{{ $booking->booking_code }}</p>
            </div>
        </div>

        <!-- Session Details Grid -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px]">Date</span>
                <span class="font-extrabold text-white text-sm">{{ $booking->booking_date->format('D, d M Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">Sport</span>
                <span class="font-extrabold text-white text-sm capitalize">{{ $booking->court->sport_type }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">Reserved Hours</span>
                <span class="font-bold text-emerald-400">
                    @foreach ($booking->slots as $slot)
                        {{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}@if (!$loop->last), @endif
                    @endforeach
                </span>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">Total Paid</span>
                <span class="font-bold text-white">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Simulated Scannable QR Code -->
        <div class="pt-4 border-t border-dashed border-slate-700 flex flex-col items-center">
            <div class="p-3 bg-white rounded-2xl shadow-lg">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($booking->booking_code) }}" alt="Ticket QR" class="w-32 h-32">
            </div>
            <span class="text-[11px] text-slate-400 font-mono mt-2 tracking-wider">{{ $booking->booking_code }}</span>
        </div>
    </div>

    <!-- Actions -->
    <div class="space-y-2 pt-2">
        <a href="{{ route('mobile.my-bookings') }}" class="w-full py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition flex items-center justify-center space-x-1.5">
            <span>View All My Bookings</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        <a href="{{ route('mobile.home') }}" class="block text-xs text-slate-400 hover:text-white py-2 font-medium">
            ← Back to Explore
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Trigger Native Notification if permitted
    if ("Notification" in window) {
        if (Notification.permission === "granted") {
            new Notification("Court Booking Confirmed! ⚡", {
                body: "Your booking for {{ $booking->court->name }} on {{ $booking->booking_date->format('d M') }} is paid and ready.",
                icon: "https://api.qrserver.com/v1/create-qr-code/?size=64x64&data={{ urlencode($booking->booking_code) }}"
            });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    new Notification("Court Booking Confirmed! ⚡", {
                        body: "Your booking for {{ $booking->court->name }} is ready.",
                    });
                }
            });
        }
    }
</script>
@endpush
