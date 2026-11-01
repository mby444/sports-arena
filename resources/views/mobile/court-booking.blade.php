@extends('mobile.layouts.app')

@section('title', 'Select Slots • ' . $court->name)
@section('back_url', route('mobile.home'))
@section('header_title', $court->name)
@section('header_subtitle', 'Choose date and hourly slots')

@section('content')
<div class="space-y-5 pb-24">

    <!-- Court Info Bar -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-800 to-slate-800/60 border border-slate-700/80 flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $court->sport_type === 'badminton' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                    {{ $court->sport_type === 'badminton' ? '🏸 Badminton' : '⚽ Futsal' }}
                </span>
                <span class="text-xs text-slate-400">{{ $court->venue->name }}</span>
            </div>
            <h3 class="text-base font-extrabold text-white mt-1">{{ $court->name }}</h3>
        </div>
        <div class="text-right">
            <span class="text-[11px] text-slate-400">Price / Hour</span>
            <p class="text-base font-black text-emerald-400">Rp {{ number_format($court->hourly_rate, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- 14-Day Date Carousel Picker -->
    <div>
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Select Date</h4>
            <span class="text-[11px] text-slate-400">{{ Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}</span>
        </div>

        <div class="flex items-center space-x-2.5 no-scrollbar overflow-x-auto pb-2">
            @foreach ($dates as $d)
                <a href="{{ route('mobile.court', ['court' => $court, 'date' => $d['date']]) }}"
                   class="flex flex-col items-center justify-center min-w-[62px] py-2.5 rounded-2xl border transition text-center shrink-0 {{ $d['is_selected'] ? 'bg-emerald-500 border-emerald-400 text-slate-950 font-bold shadow-lg shadow-emerald-500/30' : 'bg-slate-800/80 border-slate-700/70 text-slate-300 hover:border-slate-500' }}">
                    <span class="text-[10px] uppercase font-bold {{ $d['is_selected'] ? 'text-slate-900' : 'text-slate-400' }}">
                        {{ $d['is_today'] ? 'Today' : $d['day_name'] }}
                    </span>
                    <span class="text-lg font-black leading-tight mt-0.5">{{ $d['day_num'] }}</span>
                    <span class="text-[9px] uppercase tracking-wider mt-0.5 {{ $d['is_selected'] ? 'text-slate-800 font-semibold' : 'text-slate-500' }}">{{ $d['month'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Slot Legend -->
    <div class="flex items-center justify-center space-x-4 py-2 px-3 rounded-xl bg-slate-800/40 border border-slate-800 text-[11px]">
        <div class="flex items-center space-x-1.5">
            <div class="w-3 h-3 rounded bg-slate-800 border border-emerald-500/80"></div>
            <span class="text-slate-300">Available</span>
        </div>
        <div class="flex items-center space-x-1.5">
            <div class="w-3 h-3 rounded bg-indigo-600 border border-indigo-400"></div>
            <span class="text-slate-300">Selected</span>
        </div>
        <div class="flex items-center space-x-1.5">
            <div class="w-3 h-3 rounded bg-slate-800/40 border border-slate-700 opacity-60"></div>
            <span class="text-slate-500">Booked/Locked</span>
        </div>
    </div>

    <!-- Booking Form -->
    <form id="bookingForm" action="{{ route('mobile.court.reserve', $court) }}" method="POST">
        @csrf
        <input type="hidden" name="booking_date" value="{{ $selectedDate }}">

        <!-- Slot Selection Grid -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Choose Hourly Slots</h4>
                <span class="text-[11px] text-slate-400">Tap to select multiple</span>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                @foreach ($slots as $slot)
                    @php
                        $isAvailable = $slot['status'] === 'available';
                        $statusLabel = match($slot['status']) {
                            'booked' => 'Booked',
                            'locked' => 'Held (Lock)',
                            default => 'Available'
                        };
                    @endphp

                    @if ($isAvailable)
                        <label class="slot-card relative flex items-center justify-between p-3 rounded-xl border border-emerald-500/40 bg-slate-800/60 hover:bg-slate-800 cursor-pointer transition select-none group has-[:checked]:bg-indigo-600 has-[:checked]:border-indigo-400 has-[:checked]:shadow-lg has-[:checked]:shadow-indigo-600/30">
                            <input type="checkbox" name="slots[]" value="{{ $slot['start_time'] }}" class="hidden slot-checkbox" onchange="updateSummary()">
                            <div>
                                <span class="text-sm font-black text-white group-has-[:checked]:text-white">
                                    {{ $slot['start_time'] }} – {{ $slot['end_time'] }}
                                </span>
                                <span class="block text-[10px] text-emerald-400 group-has-[:checked]:text-indigo-200 font-semibold mt-0.5">
                                    Rp {{ number_format($slot['price'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-5 h-5 rounded-full border border-slate-600 flex items-center justify-center group-has-[:checked]:bg-white group-has-[:checked]:border-white transition">
                                <svg class="w-3 h-3 text-indigo-600 hidden group-has-[:checked]:block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            </div>
                        </label>
                    @else
                        <div class="relative flex items-center justify-between p-3 rounded-xl border border-slate-800/80 bg-slate-900/60 opacity-40 cursor-not-allowed select-none">
                            <div>
                                <span class="text-sm font-bold text-slate-500 line-through">
                                    {{ $slot['start_time'] }} – {{ $slot['end_time'] }}
                                </span>
                                <span class="block text-[10px] text-rose-400 font-medium mt-0.5">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <div class="w-4 h-4 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-[10px] text-slate-500">
                                ✕
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Sticky Bottom Summary Bar -->
        <div class="fixed bottom-16 left-0 right-0 z-40">
            <div class="max-w-md mx-auto p-4 bg-slate-900/95 backdrop-blur-lg border-t border-slate-800 shadow-2xl flex items-center justify-between">
                <div>
                    <span id="selectedCount" class="text-xs text-slate-400 font-medium">0 slots selected</span>
                    <p id="totalPrice" class="text-lg font-black text-emerald-400">Rp 0</p>
                </div>

                <button type="submit" id="submitBtn" disabled class="px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center space-x-1.5">
                    <span>Reserve & Pay</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    const hourlyRate = {{ (float) $court->hourly_rate }};

    function updateSummary() {
        const checkboxes = document.querySelectorAll('.slot-checkbox:checked');
        const count = checkboxes.length;
        const total = count * hourlyRate;

        const countEl = document.getElementById('selectedCount');
        const priceEl = document.getElementById('totalPrice');
        const btn = document.getElementById('submitBtn');

        if (count === 0) {
            countEl.textContent = '0 slots selected';
            priceEl.textContent = 'Rp 0';
            btn.disabled = true;
        } else {
            countEl.textContent = `${count} hour${count > 1 ? 's' : ''} selected`;
            priceEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            btn.disabled = false;
        }
    }
</script>
@endpush
