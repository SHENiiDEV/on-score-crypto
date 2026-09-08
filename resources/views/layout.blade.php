<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'on-score.io — On-Chain Gambling Intelligence & Scoring Platform')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            accent: '#4f46e5',
                            vip: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
        }
        .card-shadow {
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05);
        }
        .card-hover:hover {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.07), 0 4px 6px -4px rgb(0 0 0 / 0.05);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans selection:bg-brand-500 selection:text-white antialiased">
    <!-- Top Bar Navigation -->
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 via-teal-500 to-indigo-600 flex items-center justify-center font-extrabold text-white text-lg shadow-md shadow-emerald-500/20">
                        O
                    </div>
                    <div>
                        <span class="text-xl font-black tracking-tight text-slate-900">On-Score<span class="text-emerald-600">.io</span></span>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-700 block -mt-1">Gambling Intelligence</span>
                    </div>
                </a>

                <nav class="hidden md:flex items-center space-x-1 pl-4 border-l border-slate-200">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->routeIs('dashboard*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }} transition">
                        Dashboard & Scoring
                    </a>
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' }} transition flex items-center space-x-1">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <span>Admin Panel (B2B & Keys)</span>
                            </a>
                            <a href="{{ route('admin.entities') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->routeIs('admin.entities') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' }} transition">
                                CEX & Casino Database
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                @if(isset($account))
                <!-- Credit Balance Badge -->
                <div class="flex items-center bg-emerald-50 border border-emerald-200/80 rounded-xl p-1.5 px-3 space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs text-emerald-800 font-semibold">Credits:</span>
                    <span class="text-sm font-black text-emerald-900 font-mono">{{ number_format($account->credit_balance) }}</span>
                    <form action="{{ route('dashboard.topup') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="amount" value="500">
                        <button type="submit" class="text-[11px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-0.5 rounded-lg transition shadow-sm">
                            +500
                        </button>
                    </form>
                </div>
                @endif

                <!-- Auth / Profile Controls -->
                <div class="flex items-center space-x-3 border-l border-slate-200 pl-4">
                    @auth
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-bold text-slate-800">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-slate-500 font-mono uppercase">{{ Auth::user()->role }}</div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-slate-600 hover:text-rose-600 bg-slate-100 hover:bg-rose-50 border border-slate-200 px-3 py-1.5 rounded-lg transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-3.5 py-2 rounded-xl transition shadow-sm flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            <span>Admin Login</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center space-x-3 card-shadow">
                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-center space-x-3 card-shadow">
                <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold text-xs shrink-0">!</div>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500 mt-auto">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
            <div>
                &copy; {{ date('Y') }} <span class="text-slate-800 font-bold">on-score.io</span> — On-Chain Gambling Intelligence & Scoring.
            </div>
            <div class="flex space-x-6 text-slate-600 font-medium">
                <span>Model: <code class="text-emerald-600 font-bold">WPS-v1.0</code></span>
                <span>CEX Filtering: <span class="text-emerald-700 font-bold">Active (Bybit, Binance, OKX)</span></span>
                <span>Networks: TRON, ETH, BSC, SOL, BTC</span>
            </div>
        </div>
    </footer>
</body>
</html>
