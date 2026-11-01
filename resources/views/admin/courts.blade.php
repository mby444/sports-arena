@extends('admin.layouts.admin')

@section('title', 'Court Management')
@section('page_title', 'Courts & Hourly Pricing')
@section('page_subtitle', 'Manage arena courts, sport categories, operating hours, and rates')

@section('content')
<div class="space-y-8">

    <!-- Add New Court Card -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <h3 class="text-base font-extrabold text-white">Add New Court</h3>

        <form action="{{ route('admin.courts.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            @csrf

            <!-- Venue -->
            <div>
                <label for="venue_id" class="block text-xs font-bold text-slate-300 mb-1">Venue</label>
                <select id="venue_id" name="venue_id" required class="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500">
                    @foreach ($venues as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Court Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-slate-300 mb-1">Court Name</label>
                <input type="text" id="name" name="name" placeholder="e.g. Badminton Court 4" required
                       class="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500">
            </div>

            <!-- Sport Type -->
            <div>
                <label for="sport_type" class="block text-xs font-bold text-slate-300 mb-1">Sport Type</label>
                <select id="sport_type" name="sport_type" required class="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500">
                    <option value="badminton">🏸 Badminton</option>
                    <option value="futsal">⚽ Futsal</option>
                </select>
            </div>

            <!-- Hourly Rate -->
            <div>
                <label for="hourly_rate" class="block text-xs font-bold text-slate-300 mb-1">Hourly Rate (Rp)</label>
                <input type="number" id="hourly_rate" name="hourly_rate" placeholder="60000" min="1000" step="1000" required
                       class="w-full px-3 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500">
            </div>

            <!-- Hours -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="open_time" class="block text-xs font-bold text-slate-300 mb-1">Open</label>
                    <input type="time" id="open_time" name="open_time" value="08:00" required
                           class="w-full px-2 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label for="close_time" class="block text-xs font-bold text-slate-300 mb-1">Close</label>
                    <input type="time" id="close_time" name="close_time" value="22:00" required
                           class="w-full px-2 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Submit -->
            <div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs transition shadow-lg shadow-emerald-500/20">
                    + Add Court
                </button>
            </div>
        </form>
    </div>

    <!-- Courts List Table -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-extrabold text-white">Configured Courts ({{ $courts->count() }})</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4">Court Name</th>
                        <th class="py-3 px-4">Sport Type</th>
                        <th class="py-3 px-4">Venue</th>
                        <th class="py-3 px-4">Operating Hours</th>
                        <th class="py-3 px-4">Rate / Hour</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($courts as $court)
                        <tr class="hover:bg-slate-850 transition">
                            <td class="py-3 px-4 font-bold text-white">{{ $court->name }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $court->sport_type === 'badminton' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                                    {{ $court->sport_type === 'badminton' ? '🏸 Badminton' : '⚽ Futsal' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-400">{{ $court->venue->name ?? 'Arena' }}</td>
                            <td class="py-3 px-4 text-slate-300 font-mono">{{ substr($court->open_time, 0, 5) }} – {{ substr($court->close_time, 0, 5) }}</td>
                            <td class="py-3 px-4 font-black text-emerald-400">Rp {{ number_format($court->hourly_rate, 0, ',', '.') }}</td>
                            <td class="py-3 px-4">
                                @if ($court->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 uppercase">
                                        Maintenance
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.courts.toggle', $court) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold border transition {{ $court->is_active ? 'border-amber-500/30 text-amber-400 hover:bg-amber-500/10' : 'border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10' }}">
                                        {{ $court->is_active ? 'Put on Maintenance' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
