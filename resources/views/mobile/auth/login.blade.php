@extends('mobile.layouts.app')

@section('title', 'Sign In')
@section('back_url', route('mobile.home'))
@section('header_title', 'Sign In')
@section('header_subtitle', 'Access your court reservations')

@section('content')
<div class="space-y-6 py-4">

    <!-- Header Greeting -->
    <div class="text-center">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 mx-auto flex items-center justify-center font-black text-2xl text-slate-950 shadow-xl shadow-emerald-500/20 mb-3">
            🏸
        </div>
        <h2 class="text-xl font-black text-white">Welcome Back!</h2>
        <p class="text-xs text-slate-400 mt-1">Sign in to book courts and view your active tickets.</p>
    </div>

    <!-- Quick Demo Accounts -->
    <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/80 space-y-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">⚡ Quick Demo Autofill:</span>
        <div class="grid grid-cols-2 gap-2">
            <button type="button" onclick="fillDemo('customer@arena.com', 'password')" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-650 text-emerald-400 text-xs font-bold transition text-left border border-slate-600">
                👤 Customer
                <span class="block text-[10px] text-slate-300 font-normal">customer@arena.com</span>
            </button>
            <button type="button" onclick="fillDemo('admin@arena.com', 'password')" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-650 text-amber-400 text-xs font-bold transition text-left border border-slate-600">
                🛡️ Arena Admin
                <span class="block text-[10px] text-slate-300 font-normal">admin@arena.com</span>
            </button>
        </div>
    </div>

    <!-- Login Form -->
    <form action="{{ route('mobile.login.submit') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-bold text-slate-300 mb-1.5">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:border-emerald-500 transition placeholder:text-slate-500"
                   placeholder="yourname@example.com">
            @error('email')
                <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-bold text-slate-300 mb-1.5">Password</label>
            <input type="password" id="password" name="password" required
                   class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs focus:outline-none focus:border-emerald-500 transition placeholder:text-slate-500"
                   placeholder="••••••••">
            @error('password')
                <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center space-x-2 text-slate-400 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-0">
                <span>Remember me</span>
            </label>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-xs shadow-xl shadow-emerald-500/20 transition">
            Sign In
        </button>
    </form>

    <div class="text-center pt-2">
        <p class="text-xs text-slate-400">
            Don't have an account?
            <a href="{{ route('mobile.register') }}" class="text-emerald-400 font-bold hover:underline">Create One</a>
        </p>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function fillDemo(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endpush
