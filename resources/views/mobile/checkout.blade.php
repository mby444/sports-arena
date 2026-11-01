@extends('mobile.layouts.app')

@section('title', 'Checkout • ' . $booking->booking_code)
@section('back_url', route('mobile.court', $booking->court_id))
@section('header_title', 'Checkout')
@section('header_subtitle', $booking->booking_code)

@section('content')
<div class="space-y-5">

    <!-- 15-Minute Slot Lock Countdown Banner -->
    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-lg animate-pulse">
                ⏱
            </div>
            <div>
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block">Slot Held For You</span>
                <span class="text-[11px] text-slate-300">Complete payment before timer expires:</span>
            </div>
        </div>
        <div class="text-right">
            <span id="lockCountdown" class="text-xl font-black text-amber-400 font-mono tracking-tight">14:59</span>
        </div>
    </div>

    <!-- Booking Order Breakdown Card -->
    <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700/80 shadow-xl space-y-4">
        <div class="flex items-start justify-between pb-3 border-b border-slate-700">
            <div>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $booking->court->sport_type === 'badminton' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                    {{ $booking->court->sport_type === 'badminton' ? '🏸 Badminton' : '⚽ Futsal' }}
                </span>
                <h3 class="text-base font-extrabold text-white mt-1">{{ $booking->court->name }}</h3>
                <p class="text-xs text-slate-400">{{ $booking->court->venue->name }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 uppercase font-semibold">Date</span>
                <p class="text-xs font-bold text-slate-200 mt-0.5">{{ $booking->booking_date->format('d M Y') }}</p>
            </div>
        </div>

        <!-- Selected Slots -->
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Reserved Time Slots</span>
            <div class="space-y-1.5">
                @foreach ($booking->slots as $slot)
                    <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-lg bg-slate-900/50 border border-slate-700/50">
                        <span class="font-bold text-slate-200">{{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}</span>
                        <span class="text-emerald-400 font-semibold">Rp {{ number_format($slot->price, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Customer Info -->
        <div class="pt-3 border-t border-slate-700 flex items-center justify-between text-xs">
            <span class="text-slate-400">Customer</span>
            <span class="font-semibold text-slate-200">{{ $booking->user->name }} ({{ $booking->user->phone ?? $booking->user->email }})</span>
        </div>

        <!-- Total Price -->
        <div class="pt-3 border-t border-slate-700 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-semibold">Total Amount</span>
                <p class="text-xs text-slate-500">Includes all court taxes & fees</p>
            </div>
            <span class="text-xl font-black text-emerald-400">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Payment Section -->
    @if ($isMock)
        <!-- Mock Midtrans Payment Simulation -->
        <div class="p-5 rounded-2xl bg-gradient-to-b from-slate-800 to-slate-800/60 border border-emerald-500/30 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-lg">💳</span>
                    <h4 class="text-sm font-bold text-white">Midtrans Sandbox Payment</h4>
                </div>
                <span class="text-[10px] bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded-full font-bold uppercase">Mock Ready</span>
            </div>

            <p class="text-xs text-slate-400">Choose your simulated payment method to test instant confirmation:</p>

            <form action="{{ route('mobile.checkout.mock', $booking) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-3 gap-2">
                    <label class="p-3 rounded-xl border border-slate-700 bg-slate-900/60 hover:border-emerald-500 cursor-pointer text-center select-none has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-500/10 transition">
                        <input type="radio" name="payment_method" value="QRIS" class="hidden" checked>
                        <span class="block text-base mb-1">📱</span>
                        <span class="text-xs font-bold text-slate-200">QRIS</span>
                    </label>

                    <label class="p-3 rounded-xl border border-slate-700 bg-slate-900/60 hover:border-emerald-500 cursor-pointer text-center select-none has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-500/10 transition">
                        <input type="radio" name="payment_method" value="BCA_VA" class="hidden">
                        <span class="block text-base mb-1">🏦</span>
                        <span class="text-xs font-bold text-slate-200">BCA VA</span>
                    </label>

                    <label class="p-3 rounded-xl border border-slate-700 bg-slate-900/60 hover:border-emerald-500 cursor-pointer text-center select-none has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-500/10 transition">
                        <input type="radio" name="payment_method" value="GOPAY" class="hidden">
                        <span class="block text-base mb-1">🟢</span>
                        <span class="text-xs font-bold text-slate-200">GoPay</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-sm shadow-xl shadow-emerald-500/25 transition flex items-center justify-center space-x-2">
                    <span>Pay Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>
    @else
        <!-- Live Midtrans Snap Integration -->
        <div class="p-5 rounded-2xl bg-slate-800 border border-slate-700 text-center space-y-4">
            <h4 class="text-sm font-bold text-white">Midtrans Snap Checkout</h4>
            <p class="text-xs text-slate-400">Click below to open Midtrans secure payment window.</p>
            <button id="payButton" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-black text-sm shadow-xl shadow-emerald-500/25 transition">
                Pay Rp {{ number_format($booking->total_price, 0, ',', '.') }}
            </button>
        </div>

        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script>
            document.getElementById('payButton').onclick = function(){
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result){
                        window.location.href = "{{ route('mobile.success', $booking) }}";
                    },
                    onPending: function(result){
                        window.location.reload();
                    },
                    onError: function(result){
                        alert("Payment error! Please retry.");
                    }
                });
            };
        </script>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // Live countdown timer for 15-minute slot lock
    const expiresAt = new Date("{{ $booking->lock_expires_at?->toIso8601String() }}").getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = expiresAt - now;

        if (distance <= 0) {
            document.getElementById('lockCountdown').textContent = "00:00";
            alert("Your 15-minute reservation lock has expired. Please select your court slots again.");
            window.location.href = "{{ route('mobile.court', $booking->court_id) }}";
            return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const formatted = (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
        document.getElementById('lockCountdown').textContent = formatted;
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
</script>
@endpush
