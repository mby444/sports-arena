@extends('admin.layouts.admin')

@section('title', 'Admin Dashboard')
@section('page_title', 'Overview & Master Court Matrix')
@section('page_subtitle', 'Live court occupancy and operational performance')

@section('content')
<div class="space-y-8">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <!-- Metric 1: Today's Bookings -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-2">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold">
                <span>Today's Confirmed Bookings</span>
                <span class="text-emerald-400">🎟️</span>
            </div>
            <div class="text-3xl font-black text-white">
                {{ $todayBookingsCount }}
            </div>
            <span class="text-[11px] text-emerald-400">Paid & active for today</span>
        </div>

        <!-- Metric 2: Today's Revenue -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-2">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold">
                <span>Today's Revenue</span>
                <span class="text-emerald-400">💰</span>
            </div>
            <div class="text-3xl font-black text-emerald-400">
                Rp {{ number_format($todayRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-slate-400">From paid reservations</span>
        </div>

        <!-- Metric 3: Active Courts -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-2">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold">
                <span>Active Courts</span>
                <span class="text-sky-400">🏟️</span>
            </div>
            <div class="text-3xl font-black text-white">
                {{ $activeCourtsCount }}
            </div>
            <span class="text-[11px] text-slate-400">Operational facilities</span>
        </div>

        <!-- Metric 4: Occupancy Rate -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-2">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold">
                <span>Day Occupancy Rate</span>
                <span class="text-amber-400">⚡</span>
            </div>
            <div class="text-3xl font-black text-white">
                {{ $occupancyRate }}%
            </div>
            <span class="text-[11px] text-amber-400">{{ $pendingLocksCount }} active 15-min lock(s)</span>
        </div>
    </div>

    <!-- Master Daily Schedule Matrix Card -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div>
                <h3 class="text-base font-extrabold text-white">Master Court Schedule Matrix</h3>
                <p class="text-xs text-slate-400">Hourly court occupancy for {{ Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}</p>
            </div>

            <!-- Date Switcher -->
            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center space-x-2">
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                       class="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500">
                <a href="{{ route('admin.dashboard', ['date' => now()->format('Y-m-d')]) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300">
                    Today
                </a>
            </form>
        </div>

        <!-- Schedule Legend -->
        <div class="flex items-center space-x-6 text-xs">
            <div class="flex items-center space-x-2">
                <span class="w-3.5 h-3.5 rounded bg-emerald-500/20 border border-emerald-500/50"></span>
                <span class="text-slate-300">Available</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3.5 h-3.5 rounded bg-indigo-600 border border-indigo-400"></span>
                <span class="text-slate-300">Booked (Paid)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3.5 h-3.5 rounded bg-amber-500/30 border border-amber-500"></span>
                <span class="text-slate-300">15-Min Lock (Checkout)</span>
            </div>
        </div>

        <!-- Matrix Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4 w-48 bg-slate-900 sticky left-0">Court</th>
                        @for ($h = 8; $h < 22; $h++)
                            <th class="py-3 px-2 text-center whitespace-nowrap">
                                {{ sprintf('%02d:00', $h) }}
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-xs">
                    @foreach ($scheduleMatrix as $row)
                        @php $court = $row['court']; $slots = collect($row['slots'])->keyBy('start_time'); @endphp
                        <tr class="hover:bg-slate-850/50 transition">
                            <td class="py-3 px-4 font-bold text-white bg-slate-900 sticky left-0 border-r border-slate-800">
                                <div>{{ $court->name }}</div>
                                <span class="text-[10px] text-slate-400 font-normal uppercase">{{ $court->sport_type }}</span>
                            </td>

                            @for ($h = 8; $h < 22; $h++)
                                @php
                                    $timeKey = sprintf('%02d:00', $h);
                                    $slot = $slots->get($timeKey);
                                    $status = $slot['status'] ?? 'available';
                                @endphp
                                <td class="py-2 px-1 text-center">
                                    @if ($status === 'booked')
                                        <div title="Booked & Paid" class="w-full py-2.5 px-1 rounded-lg bg-indigo-600/90 text-white font-extrabold text-[10px] shadow-sm select-none">
                                            PAID
                                        </div>
                                    @elseif ($status === 'locked')
                                        <div title="Holding 15-min lock" class="w-full py-2.5 px-1 rounded-lg bg-amber-500/30 text-amber-300 border border-amber-500/50 font-bold text-[10px] select-none animate-pulse">
                                            LOCK
                                        </div>
                                    @else
                                        <div title="Available: Rp {{ number_format($court->hourly_rate, 0, ',', '.') }}" class="w-full py-2.5 px-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold text-[10px] hover:bg-emerald-500/20 transition select-none">
                                            OPEN
                                        </div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
