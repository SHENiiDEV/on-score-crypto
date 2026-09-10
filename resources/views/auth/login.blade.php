@extends('layout')

@section('title', 'Sign In — on-score.io')

@section('content')
<div class="max-w-md mx-auto my-8">
    <div class="bg-white border border-slate-200 rounded-3xl p-8 card-shadow">
        <div class="text-center space-y-2 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200/90 p-1.5 shadow-md shadow-indigo-500/10 flex items-center justify-center mx-auto">
                <svg viewBox="0 0 100 100" class="w-full h-full" fill="none">
                    <defs>
                        <linearGradient id="logOrbitGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#00d4ff" />
                            <stop offset="35%" stop-color="#635bff" />
                            <stop offset="70%" stop-color="#7a73ff" />
                            <stop offset="100%" stop-color="#ff6080" />
                        </linearGradient>
                        <linearGradient id="logPulseGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#00d4ff" />
                            <stop offset="50%" stop-color="#635bff" />
                            <stop offset="100%" stop-color="#ff6080" />
                        </linearGradient>
                    </defs>
                    <circle cx="50" cy="50" r="32" stroke="url(#logOrbitGrad)" stroke-width="7.5" stroke-linecap="round" />
                    <ellipse cx="50" cy="50" rx="44" ry="18" stroke="url(#logOrbitGrad)" stroke-width="6" transform="rotate(-30 50 50)" stroke-linecap="round" />
                    <path d="M 24 50 L 38 50 L 44 65 L 56 32 L 63 56 L 68 50 L 76 50" stroke="url(#logPulseGrad)" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Sign In</h2>
            <p class="text-xs text-slate-500">Enter your credentials to access the admin & intelligence portal.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Email Address</label>
                <input type="email" name="email" id="loginEmail" required value="{{ old('email') }}" placeholder="name@company.com"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium text-sm focus:border-indigo-500 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Password</label>
                <input type="password" name="password" id="loginPassword" required placeholder="••••••••"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-medium text-sm focus:border-indigo-500 focus:bg-white focus:outline-none">
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-slate-600">Remember me</span>
                </label>
                <span class="text-slate-400 font-mono">On-Score Auth Guard</span>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-sm py-3.5 rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                <span>Sign In to Account</span>
                <span>&rarr;</span>
            </button>
        </form>
    </div>
</div>
@endsection
