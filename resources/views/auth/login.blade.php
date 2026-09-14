<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — on-score.io</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: light; }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; background:#f6f8fb; color:#0a2540; }
        .input { width:100%; background:#fff; border:1px solid #d8e0ea; border-radius:.75rem; padding:.75rem .9rem; font-size:.875rem; font-weight:500; color:#0a2540; }
        .input:focus { outline:none; border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
        .label { display:block; font-size:.6875rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color:#64748b; margin-bottom:.4rem; }
        .aurora { background:
            radial-gradient(60% 60% at 15% 20%, rgba(0,212,255,.35) 0%, transparent 60%),
            radial-gradient(55% 55% at 85% 25%, rgba(99,91,255,.45) 0%, transparent 62%),
            radial-gradient(60% 60% at 50% 90%, rgba(255,96,128,.28) 0%, transparent 60%),
            #0a2540; }
    </style>
</head>
<body class="min-h-full">
<div class="min-h-screen grid lg:grid-cols-2">

    <!-- Brand panel -->
    <div class="aurora hidden lg:flex flex-col justify-between p-12 text-white relative overflow-hidden">
        <div class="relative">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 w-fit">
                <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 p-1.5 backdrop-blur flex items-center justify-center">
                    @include('console.partials.logo-mark', ['uid' => 'login'])
                </div>
                <div>
                    <div class="text-lg font-black tracking-tight leading-none">On-Score<span class="text-[#00d4ff]">.io</span></div>
                    <div class="text-[9px] uppercase font-extrabold tracking-wider text-white/50 mt-1">Intelligence Platform</div>
                </div>
            </a>
        </div>

        <div class="relative max-w-md">
            <h2 class="text-3xl font-black leading-tight tracking-tight">
                On-chain player intelligence, in one console.
            </h2>
            <p class="text-sm text-white/60 mt-4 leading-relaxed">
                Merchants manage their own API keys, watch a single shared credit balance,
                audit usage per key and tune scoring rules — all from one portal.
            </p>

            <div class="mt-8 grid grid-cols-3 gap-3">
                @foreach([['Shared', 'credit balance'], ['Self-service', 'API keys'], ['Custom', 'scoring rules']] as [$a, $b])
                    <div class="rounded-xl bg-white/5 border border-white/10 px-3 py-3 backdrop-blur">
                        <div class="text-[13px] font-black">{{ $a }}</div>
                        <div class="text-[10px] text-white/50 font-semibold uppercase tracking-wide mt-0.5">{{ $b }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="relative text-[11px] text-white/40 font-mono">
            TRON · ETH · BSC · SOL · BTC — Model WPS-v1.0
        </div>
    </div>

    <!-- Form panel -->
    <div class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-sm">
            <div class="lg:hidden mb-8 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 p-1 shadow-sm flex items-center justify-center">
                    @include('console.partials.logo-mark', ['uid' => 'loginm'])
                </div>
                <div class="text-lg font-black tracking-tight">On-Score<span class="text-[#635bff]">.io</span></div>
            </div>

            <h1 class="text-2xl font-black tracking-tight">Sign in</h1>
            <p class="text-[13px] text-slate-500 mt-1.5">Merchant portal &amp; On-Score admin console.</p>

            @if(session('success'))
                <div class="mt-5 rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2.5 text-[12px] font-semibold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mt-5 rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-[12px] font-semibold text-rose-800">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mt-5 rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-[12px] font-semibold text-rose-800">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="label">Email address</label>
                    <input type="email" name="email" required value="{{ old('email') }}" autocomplete="username" placeholder="name@company.com" class="input">
                </div>
                <div>
                    <label class="label">Password</label>
                    <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••••" class="input">
                </div>
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-[#635bff] focus:ring-[#635bff]">
                    <span class="text-[12px] font-medium text-slate-600">Keep me signed in</span>
                </label>
                <button type="submit" class="w-full bg-[#0a2540] hover:bg-[#123a63] text-white font-extrabold text-sm py-3.5 rounded-xl shadow-lg shadow-[#0a2540]/20 transition">
                    Sign in
                </button>
            </form>

            <p class="mt-6 text-[12px] text-slate-400 leading-relaxed">
                Access is provisioned by On-Score. If you don't have credentials yet, contact your account manager.
            </p>
            <a href="{{ route('landing') }}" class="mt-4 inline-block text-[12px] font-bold text-slate-500 hover:text-[#0a2540]">&larr; Back to on-score.io</a>
        </div>
    </div>
</div>
</body>
</html>
