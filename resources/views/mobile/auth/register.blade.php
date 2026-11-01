@extends('mobile.layouts.app')

@section('title', 'Create Account')
@section('back_url', route('mobile.login'))
@section('header_title', 'Create Account')
@section('header_subtitle', 'Join Champion Sports Arena')

@section('content')
<div class="space-y-6 py-4">

    <!-- Header Greeting -->
    <div class="text-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 mx-auto flex items-center justify-center font-black text-2xl text-slate-950 shadow-xl shadow-emerald-500/20 mb-3">
            ⚽
        </div>
        <h2 class="text-xl font-black text-white">Create an Account</h2>
        <p class="text-xs text-slate-400 mt-1">Book your badminton & futsal court slots in seconds.</p>
    </div>

    <!-- Register Form -->
    <form action="{{ route('mobile.register.submit') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-bold text-slate-300 mb-1.5">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:border-emerald-500 transition placeholder:text-slate-500"
                   placeholder="e.g. Budi Santoso">
            @error('name')
                <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 mb-1.5">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:border-emerald-500 transition placeholder:text-slate-500"
                   placeholder="yourname@example.com">
            @error('email')
                <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-xs font-bold text-slate-300 mb-1.5">WhatsApp / Phone Number</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                   class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:border-emerald-500 transition placeholder:text-slate-500"
                   placeholder="081234567890">
            @error('phone')
                <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-bold text-slate-300 mb-1.5">Password (Min. 6 Characters)</label>
            <input type="password" id="password" name="password" required
                   class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:border-emerald-500 transition placeholder:text-slate-500"
                   placeholder="••••••••">
            @error('password')
                <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-xs shadow-xl shadow-emerald-500/20 transition">
            Create Account
        </button>
    </form>

    <div class="text-center pt-2">
        <p class="text-xs text-slate-400">
            Already have an account?
            <a href="{{ route('mobile.login') }}" class="text-emerald-400 font-bold hover:underline">Sign In</a>
        </p>
    </div>

</div>
@endsection
