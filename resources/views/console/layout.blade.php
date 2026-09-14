<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Console — on-score.io')</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        ink: { DEFAULT: '#0a2540', soft: '#425466' },
                        iris: { 50: '#f2f1ff', 100: '#e6e4ff', 300: '#b6b1ff', 500: '#635bff', 600: '#4f46e5', 700: '#4338ca' },
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(16,24,40,.04), 0 1px 3px rgba(16,24,40,.06)',
                        lift: '0 12px 28px -12px rgba(10,37,64,.18)',
                    },
                },
            },
        }
    </script>
    <style>
        :root { color-scheme: light; }
        body { background-color: #f6f8fb; color: #0a2540; }
        .nav-link { display:flex; align-items:center; gap:.625rem; padding:.5rem .7rem; border-radius:.7rem; font-size:.8125rem; font-weight:600; color:#54657a; transition:all .15s ease; }
        .nav-link:hover { background:#eef2f7; color:#0a2540; }
        .nav-link.active { background:#0a2540; color:#fff; box-shadow:0 6px 14px -8px rgba(10,37,64,.7); }
        .nav-link.active svg { color:#8ad9c0; }
        .nav-group { font-size:.625rem; font-weight:800; letter-spacing:.09em; text-transform:uppercase; color:#94a3b8; padding:0 .7rem; margin:1.25rem 0 .45rem; }
        .surface { background:#fff; border:1px solid #e7ecf3; border-radius:1rem; box-shadow:0 1px 2px rgba(16,24,40,.04); }
        .kpi-label { font-size:.6875rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:#8497ab; }
        .th { font-size:.625rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; color:#8497ab; padding:.65rem .9rem; text-align:left; white-space:nowrap; }
        .td { padding:.7rem .9rem; font-size:.8125rem; vertical-align:middle; }
        .row-hover:hover { background:#f8fafc; }
        .input { width:100%; background:#fff; border:1px solid #d8e0ea; border-radius:.7rem; padding:.6rem .8rem; font-size:.8125rem; font-weight:500; color:#0a2540; transition:border-color .15s, box-shadow .15s; }
        .input:focus { outline:none; border-color:#635bff; box-shadow:0 0 0 3px rgba(99,91,255,.12); }
        .label { display:block; font-size:.6875rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; color:#64748b; margin-bottom:.35rem; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; font-size:.8125rem; font-weight:700; padding:.6rem 1rem; border-radius:.7rem; transition:all .15s ease; white-space:nowrap; }
        .btn-primary { background:#0a2540; color:#fff; box-shadow:0 8px 18px -10px rgba(10,37,64,.8); }
        .btn-primary:hover { background:#123a63; }
        .btn-iris { background:#635bff; color:#fff; box-shadow:0 8px 18px -10px rgba(99,91,255,.9); }
        .btn-iris:hover { background:#4f46e5; }
        .btn-ghost { background:#fff; border:1px solid #d8e0ea; color:#425466; }
        .btn-ghost:hover { border-color:#9fb0c4; color:#0a2540; }
        .btn-danger { background:#fff; border:1px solid #fecdd3; color:#be123c; }
        .btn-danger:hover { background:#fff1f2; }
        .chip { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .55rem; border-radius:999px; font-size:.6875rem; font-weight:700; border:1px solid transparent; }
        .scroll-x { overflow-x:auto; }
        .scroll-x::-webkit-scrollbar { height:6px; }
        .scroll-x::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:99px; }
        [x-cloak] { display:none !important; }
    </style>
    @stack('head')
</head>
<body class="min-h-full font-sans antialiased selection:bg-iris-500 selection:text-white">

@php
    $authUser = auth()->user();
    $impersonating = \App\Support\MerchantContext::isImpersonating();
@endphp

@if($impersonating)
    <div class="bg-amber-400 text-amber-950 text-xs font-bold px-4 py-2 flex flex-wrap items-center justify-center gap-3 sticky top-0 z-50">
        <span class="inline-flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-900 animate-pulse"></span>
            You are viewing the merchant portal as <span class="underline underline-offset-2">{{ \App\Support\MerchantContext::account()?->name }}</span>
        </span>
        <form action="{{ route('admin.stop-impersonating') }}" method="POST">
            @csrf
            <button class="bg-amber-950 text-amber-50 px-2.5 py-1 rounded-lg text-[11px] font-bold hover:bg-black transition">Exit client view</button>
        </form>
    </div>
@endif

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed lg:sticky inset-y-0 left-0 z-40 w-[248px] shrink-0 bg-white border-r border-slate-200/80 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200 {{ $impersonating ? 'lg:top-[36px] lg:h-[calc(100vh-36px)]' : 'lg:top-0 lg:h-screen' }}">
        <div class="h-16 flex items-center px-4 border-b border-slate-200/70 shrink-0">
            <a href="{{ $authUser?->isAdmin() && !$impersonating ? route('admin.dashboard') : route('merchant.dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 p-1 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    @include('console.partials.logo-mark', ['uid' => 'side'])
                </div>
                <div class="leading-none">
                    <div class="text-[15px] font-black tracking-tight text-ink">On-Score<span class="text-iris-500">.io</span></div>
                    <div class="text-[9px] uppercase font-extrabold tracking-wider text-slate-400 mt-0.5">
                        {{ $authUser?->isAdmin() && !$impersonating ? 'Control Room' : 'Merchant Portal' }}
                    </div>
                </div>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-3">
            @if($authUser?->isAdmin() && !$impersonating)
                @include('console.partials.nav-admin')
            @else
                @include('console.partials.nav-merchant')
            @endif
        </nav>

        <div class="border-t border-slate-200/70 p-3 shrink-0">
            <div class="flex items-center gap-2.5 px-1.5 py-1.5">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-iris-500 to-[#00d4ff] text-white flex items-center justify-center text-[11px] font-black shrink-0">
                    {{ $authUser?->initials() }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-ink truncate">{{ $authUser?->name }}</div>
                    <div class="text-[10px] text-slate-400 truncate">{{ $authUser?->roleLabel() }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button title="Sign out" class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div id="sidebarBackdrop" class="fixed inset-0 bg-ink/40 z-30 hidden lg:hidden"></div>

    <!-- Main -->
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="h-16 bg-white/85 backdrop-blur border-b border-slate-200/80 sticky {{ $impersonating ? 'top-[36px]' : 'top-0' }} z-20 flex items-center gap-3 px-4 sm:px-6">
            <button id="sidebarToggle" class="lg:hidden w-9 h-9 rounded-lg border border-slate-200 text-slate-500 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="min-w-0 flex-1">
                <h1 class="text-[15px] sm:text-base font-black text-ink tracking-tight truncate">@yield('page-title', 'Dashboard')</h1>
                <p class="text-[11px] text-slate-500 truncate hidden sm:block">@yield('page-subtitle')</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @yield('actions')
            </div>
        </header>

        <main class="flex-1 px-4 sm:px-6 py-6 w-full max-w-[1400px]">
            @include('console.partials.flash')
            @yield('content')
        </main>

        <footer class="border-t border-slate-200/80 bg-white px-6 py-4 text-[11px] text-slate-400 flex flex-wrap gap-x-6 gap-y-1 justify-between">
            <span>&copy; {{ date('Y') }} on-score.io — On-Chain Player Intelligence</span>
            <span class="font-mono">Model WPS-v1.0 · TRON · ETH · BSC · SOL · BTC</span>
        </footer>
    </div>
</div>

<script>
    (function () {
        const btn = document.getElementById('sidebarToggle');
        const bar = document.getElementById('sidebar');
        const back = document.getElementById('sidebarBackdrop');
        const close = () => { bar.classList.add('-translate-x-full'); back.classList.add('hidden'); };
        btn?.addEventListener('click', () => { bar.classList.toggle('-translate-x-full'); back.classList.toggle('hidden'); });
        back?.addEventListener('click', close);
    })();

    function copyText(text, el) {
        const done = () => {
            if (!el) return;
            const prev = el.innerHTML;
            el.innerHTML = 'Copied';
            setTimeout(() => { el.innerHTML = prev; }, 1400);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(() => fallbackCopy(text, done));
        } else {
            fallbackCopy(text, done);
        }
    }

    function fallbackCopy(text, done) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(ta);
    }
</script>
@stack('scripts')
</body>
</html>
