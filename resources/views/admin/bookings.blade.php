@extends('admin.layouts.admin')

@section('title', 'Reservations Log')
@section('page_title', 'Master Reservations Log')
@section('page_subtitle', 'Check in arriving players, review payment statuses, and manage bookings')

@section('content')
<div class="space-y-6">

    <!-- Filters Header -->
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Filter Tabs -->
        <div class="flex items-center space-x-1.5 overflow-x-auto text-xs font-bold">
            <a href="{{ route('admin.bookings', ['status' => 'all', 'search' => $search, 'date' => $selectedDate]) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $currentStatus === 'all' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                All Bookings
            </a>
            <a href="{{ route('admin.bookings', ['status' => 'paid', 'search' => $search, 'date' => $selectedDate]) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $currentStatus === 'paid' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                Paid
            </a>
            <a href="{{ route('admin.bookings', ['status' => 'pending', 'search' => $search, 'date' => $selectedDate]) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $currentStatus === 'pending' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                Pending Lock
            </a>
            <a href="{{ route('admin.bookings', ['status' => 'completed', 'search' => $search, 'date' => $selectedDate]) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $currentStatus === 'completed' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                Completed
            </a>
            <a href="{{ route('admin.bookings', ['status' => 'cancelled', 'search' => $search, 'date' => $selectedDate]) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $currentStatus === 'cancelled' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                Cancelled
            </a>
        </div>

        <!-- Search & Date Filter Form -->
        <form action="{{ route('admin.bookings') }}" method="GET" class="flex items-center space-x-2">
            <input type="hidden" name="status" value="{{ $currentStatus }}">

            <input type="date" name="date" value="{{ $selectedDate }}"
                   class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500">

            <input type="text" name="search" value="{{ $search }}" placeholder="Code or Customer..."
                   class="px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500 placeholder:text-slate-500 w-48">

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition">
                Search
            </button>
        </form>
    </div>

    <!-- Reservations Table Card -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4">Booking Code</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Court</th>
                        <th class="py-3 px-4">Date & Slots</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($bookings as $b)
                        <tr class="hover:bg-slate-850 transition">
                            <td class="py-3.5 px-4 font-mono font-black text-white">
                                {{ $b->booking_code }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-200 block">{{ $b->user->name }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $b->user->phone ?? $b->user->email }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $b->court->name }}</span>
                                <span class="text-[10px] uppercase font-bold text-emerald-400">{{ $b->court->sport_type }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-300 font-semibold block">{{ $b->booking_date->format('d M Y') }}</span>
                                <span class="text-[11px] text-slate-400">
                                    @foreach ($b->slots as $s)
                                        {{ substr($s->start_time, 0, 5) }}@if (!$loop->last), @endif
                                    @endforeach
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-black text-emerald-400 block">Rp {{ number_format($b->total_price, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">{{ $b->payment_method ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($b->isPaid())
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">
                                        PAID
                                    </span>
                                @elseif ($b->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 uppercase">
                                        CHECKED IN
                                    </span>
                                @elseif ($b->isLockActive())
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-400 border border-amber-500/30 uppercase">
                                        PENDING LOCK
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-800 text-slate-400 uppercase">
                                        {{ strtoupper($b->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                @if ($b->isPaid())
                                    <form action="{{ route('admin.bookings.checkin', $b) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs transition">
                                            ✓ Check In
                                        </button>
                                    </form>
                                @endif

                                @if (!in_array($b->status, ['completed', 'cancelled']))
                                    <form action="{{ route('admin.bookings.cancel', $b) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this booking and release slots?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold text-xs transition">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-500">
                                No reservations match the current filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-4 border-t border-slate-800">
            {{ $bookings->links() }}
        </div>
    </div>

</div>
@endsection
