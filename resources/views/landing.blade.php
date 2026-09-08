<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>on-score.io — B2B On-Chain Player Intelligence & Crypto Scoring Gateway</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        night: {
                            950: '#060913',
                            900: '#0b1120',
                            850: '#0f172a',
                            800: '#1e293b',
                            750: '#283548',
                        },
                        brand: {
                            emerald: '#10b981',
                            indigo: '#6366f1',
                            amber: '#f59e0b',
                            cyan: '#06b6d4',
                            purple: '#8b5cf6',
                            rose: '#f43f5e',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-8px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #060913;
            color: #0f172a;
        }
        
        .hero-mesh {
            background-color: #060913;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(6, 182, 212, 0.12) 0px, transparent 50%),
                radial-gradient(at 80% 80%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
        }

        .grid-pattern {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        .dark-glass {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.10);
        }

        .dark-glass-card {
            background: rgba(11, 17, 32, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .gradient-border-card {
            position: relative;
            border-radius: 1.5rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .gradient-border-card:hover {
            border-color: #c7d2fe;
            box-shadow: 0 20px 40px -10px rgba(99, 102, 241, 0.10);
            transform: translateY(-3px);
        }

        .glow-dot {
            box-shadow: 0 0 12px currentColor;
        }

        /* Syntax colors */
        .syn-key { color: #93c5fd; }
        .syn-str { color: #34d399; }
        .syn-num { color: #fbbf24; }
        .syn-bool { color: #f472b6; }
        .syn-null { color: #94a3b8; }
        .syn-comm { color: #64748b; font-style: italic; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Floating Dark Header (PayAdmit Style) -->
    <header class="fixed top-0 inset-x-0 z-50 bg-night-950/80 backdrop-blur-xl border-b border-white/10 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-500 via-emerald-500 to-cyan-400 flex items-center justify-center font-black text-white text-xl shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition transform">
                        O
                    </div>
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-2xl font-black tracking-tight text-white">On-Score</span>
                            <span class="text-emerald-400 text-2xl font-black">.io</span>
                        </div>
                        <span class="text-[9px] uppercase font-extrabold tracking-widest text-indigo-300 block -mt-1">Player Intelligence</span>
                    </div>
                </a>
            </div>

            <nav class="hidden lg:flex items-center space-x-8 text-xs font-bold text-slate-300">
                <a href="#how-it-works" class="hover:text-emerald-400 transition">How It Works</a>
                <a href="#live-demo" class="hover:text-emerald-400 transition">Interactive Demo</a>
                <a href="#cex-isolation" class="hover:text-emerald-400 transition">CEX Isolation</a>
                <a href="#features" class="hover:text-emerald-400 transition">Features</a>
                <a href="#faq" class="hover:text-emerald-400 transition">FAQ</a>
            </nav>

            <div class="flex items-center space-x-3">
                <a href="/login" class="text-xs font-extrabold text-night-950 bg-gradient-to-r from-emerald-400 to-cyan-400 hover:from-emerald-300 hover:to-cyan-300 px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-500/20 transition transform hover:-translate-y-0.5 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-night-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>Admin Portal</span>
                </a>
            </div>
        </div>
    </header>

    <!-- HERO SECTION (PayAdmit Dark Atmospheric Cosmic Theme) -->
    <section class="relative pt-32 pb-20 hero-mesh grid-pattern overflow-hidden text-white">
        <!-- Glow Orbs -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[650px] h-[350px] bg-indigo-600/15 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute top-1/3 right-10 w-[450px] h-[300px] bg-emerald-500/15 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Column: High-Agency Copy & Actions -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    <!-- Pill Badge -->
                    <div class="inline-flex items-center space-x-2.5 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-emerald-400 text-xs font-extrabold backdrop-blur-md shadow-lg shadow-emerald-500/5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span class="tracking-wide">THE B2B ON-CHAIN SCORING GATEWAY FOR CRYPTO IGAMING</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-white leading-[1.10]">
                        INSTANT ON-CHAIN <br>
                        <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 bg-clip-text text-transparent">VIP Player Detection</span> <br>
                        & RISK SCORING
                    </h1>

                    <!-- Lead Paragraph -->
                    <p class="text-base sm:text-lg text-slate-300 font-normal max-w-xl leading-relaxed">
                        Score crypto deposit wallets in under <strong>1.2 seconds</strong> across <strong>TRON</strong>, <strong>Ethereum</strong>, <strong>BNB Chain</strong>, <strong>Solana</strong>, and <strong>Bitcoin</strong>. Instantly identify high-rollers with $200k+ balance & active gambling history while isolating exchange hot wallets (Bybit, Binance).
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-3">
                        <a href="/docs" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-indigo-500 via-indigo-600 to-indigo-700 hover:from-indigo-600 hover:to-indigo-800 text-white text-sm font-black rounded-2xl shadow-xl shadow-indigo-500/30 transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                            <span>Explore API Documentation</span>
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                        <a href="#live-demo" class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/15 border border-white/20 text-white text-sm font-bold rounded-2xl backdrop-blur-md transition flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Test Live Simulator</span>
                        </a>
                    </div>

                    <!-- Micro Highlights -->
                    <div class="pt-4 flex flex-wrap items-center gap-6 text-xs text-slate-400 font-medium">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">✓</div>
                            <span class="text-slate-300">0–100 WPS-v1.0 Score</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">✓</div>
                            <span class="text-slate-300">CEX Pool Anti-Hallucination</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">✓</div>
                            <span class="text-slate-300">Per-Client Custom Rules & Tags</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Live Dossier Card (PayAdmit Style with live preset switching) -->
                <div class="lg:col-span-5 relative" id="hero-card-container">
                    <div class="absolute -inset-1.5 bg-gradient-to-tr from-emerald-500 via-indigo-500 to-cyan-400 rounded-3xl blur-2xl opacity-30 animate-pulse-slow"></div>
                    
                    <div class="relative dark-glass-card rounded-3xl p-6 sm:p-7 space-y-5 border border-white/15 shadow-2xl">
                        
                        <!-- Card Top Bar: Live Status & Preset Indicator -->
                        <div class="flex items-center justify-between border-b border-white/10 pb-4">
                            <div class="flex items-center space-x-3">
                                <div id="cardBadgeIcon" class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-300 flex items-center justify-center font-black text-night-950 text-sm font-mono shadow-md">
                                    VIP
                                </div>
                                <div>
                                    <div class="text-xs font-black text-white flex items-center space-x-2">
                                        <span id="cardPlayerTitle">Live Player Intelligence</span>
                                    </div>
                                    <div id="cardPlayerAddress" class="text-[10px] font-mono text-slate-400">0xde0B29...7BAe (Ethereum)</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span id="cardStatusBadge" class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[10px] font-black uppercase tracking-wider">
                                    SUPER_SHARK
                                </span>
                            </div>
                        </div>

                        <!-- Score Gauge Display -->
                        <div class="flex items-center justify-between bg-night-900/90 rounded-2xl p-4 border border-white/5">
                            <div>
                                <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">Scoring Engine Output</div>
                                <div id="cardScoreTier" class="text-lg font-black text-amber-400 mt-0.5">VIP Tier 5 (Whale)</div>
                                <div id="cardConfidence" class="text-[10px] text-slate-400">Confidence: 98% • Latency: 0.94s</div>
                            </div>
                            <div id="cardScoreBox" class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-300 flex flex-col items-center justify-center text-night-950 font-black shadow-lg">
                                <span id="cardScoreNumber" class="text-2xl leading-none">94</span>
                                <span class="text-[8px] uppercase tracking-wider opacity-80">/ 100</span>
                            </div>
                        </div>

                        <!-- 2-Column Metrics Grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-night-900/80 border border-white/5 rounded-2xl p-3.5 space-y-1">
                                <span class="text-[10px] font-bold uppercase text-slate-400">Liquid Balance</span>
                                <div id="cardBalanceUsd" class="text-lg font-black text-white font-mono">$24,864,346</div>
                                <div id="cardBalanceTokens" class="text-[10px] text-emerald-400 font-bold truncate">6,774.49 ETH + USDT</div>
                            </div>
                            <div class="bg-night-900/80 border border-white/5 rounded-2xl p-3.5 space-y-1">
                                <span class="text-[10px] font-bold uppercase text-slate-400">Gambling Flow (365d)</span>
                                <div id="cardGamblingFlow" class="text-lg font-black text-indigo-300 font-mono">$1,820,000</div>
                                <div id="cardGamblingBrands" class="text-[10px] text-indigo-400 font-bold truncate">Stake.com, Rollbit</div>
                            </div>
                        </div>

                        <!-- Assigned Custom Tags -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase text-slate-400">Assigned Tags & Key Drivers</span>
                                <span class="text-[9px] text-slate-500 font-mono">Custom Rules Applied</span>
                            </div>
                            <div id="cardTagsContainer" class="flex flex-wrap gap-1.5">
                                <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[10px] font-black">#SUPER_SHARK</span>
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold">#WHALE_LIQUIDITY_GT_400K</span>
                                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 text-[10px] font-bold">#STAKE_VETERAN</span>
                            </div>
                        </div>

                        <!-- Instant Action Banner -->
                        <div id="cardBottomBanner" class="bg-gradient-to-r from-indigo-950/80 via-night-900 to-emerald-950/80 rounded-xl p-3 border border-indigo-500/30 flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                <span id="cardBottomText" class="font-bold text-white">VIP Host Auto-Assigned in CRM</span>
                            </div>
                            <span class="font-mono text-[11px] text-emerald-300 font-bold">&lt; 1.2s</span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 5-CHAIN LIVE NETWORK TICKER (PayAdmit Style) -->
    <div class="bg-night-900 border-y border-white/10 py-5 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2 text-xs font-black uppercase tracking-wider text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 glow-dot"></span>
                    <span>Supported Layer-1 & Token Ecosystems:</span>
                </div>
                <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-xs font-bold text-slate-300">
                    <div class="flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:border-red-500/40 transition">
                        <span class="text-red-400 font-mono">TRON</span>
                        <span class="text-[10px] text-slate-400">USDT (TRC-20), TRX</span>
                    </div>
                    <div class="flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:border-indigo-500/40 transition">
                        <span class="text-indigo-400 font-mono">ETH</span>
                        <span class="text-[10px] text-slate-400">ERC-20, ETH</span>
                    </div>
                    <div class="flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:border-amber-500/40 transition">
                        <span class="text-amber-400 font-mono">BNB</span>
                        <span class="text-[10px] text-slate-400">BEP-20, BNB</span>
                    </div>
                    <div class="flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:border-purple-500/40 transition">
                        <span class="text-purple-400 font-mono">SOL</span>
                        <span class="text-[10px] text-slate-400">SPL USDT, SOL</span>
                    </div>
                    <div class="flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:border-orange-500/40 transition">
                        <span class="text-orange-400 font-mono">BTC</span>
                        <span class="text-[10px] text-slate-400">UTXO Native</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HORIZONTAL METRICS BAR (PayAdmit Style) -->
    <div class="bg-night-950 border-b border-white/10 py-8 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="space-y-1 border-r border-white/10 last:border-none">
                    <div class="text-3xl sm:text-4xl font-black font-mono bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">$3.8B+</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">On-Chain Volume Processed</div>
                </div>
                <div class="space-y-1 border-r border-white/10 last:border-none">
                    <div class="text-3xl sm:text-4xl font-black font-mono text-white">99.8%</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">CEX Attribution Accuracy</div>
                </div>
                <div class="space-y-1 border-r border-white/10 last:border-none">
                    <div class="text-3xl sm:text-4xl font-black font-mono text-indigo-400">5 Chains</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">TRON, ETH, BSC, SOL, BTC</div>
                </div>
                <div class="space-y-1">
                    <div class="text-3xl sm:text-4xl font-black font-mono text-amber-400">&lt; 1.2 sec</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Average Scoring Latency</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN BODY SECTIONS (Clean Light Theme Transition) -->
    <div class="bg-slate-50 text-slate-900 flex-1">

        <!-- INTERACTIVE LIVE SIMULATOR DEMO SECTION -->
        <section id="live-demo" class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-10">
                <span class="text-xs font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-3.5 py-1 rounded-full border border-indigo-200">Interactive Simulator</span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Try Real-Time Wallet Dossier Inspection</h2>
                <p class="text-sm sm:text-base text-slate-600">
                    Click any player archetype below to see how On-Score calculates balances, checks gambling footprints, and applies custom casino tags in real time.
                </p>

                <!-- Archetype Selector Tabs -->
                <div class="flex flex-wrap items-center justify-center gap-2.5 pt-4">
                    <button onclick="setSimulationPreset('whale_eth')" id="simBtn-whale_eth" class="px-4 py-2.5 rounded-xl font-extrabold text-xs transition border bg-slate-900 text-white border-slate-900 shadow-md flex items-center space-x-2">
                        <span>🐋</span>
                        <span>High-Roller Shark (ETH)</span>
                    </button>
                    <button onclick="setSimulationPreset('tron_gambler')" id="simBtn-tron_gambler" class="px-4 py-2.5 rounded-xl font-extrabold text-xs transition border bg-white text-slate-700 border-slate-300 hover:bg-slate-100 flex items-center space-x-2">
                        <span>⚡</span>
                        <span>Active Gambler (TRON)</span>
                    </button>
                    <button onclick="setSimulationPreset('bybit_cex')" id="simBtn-bybit_cex" class="px-4 py-2.5 rounded-xl font-extrabold text-xs transition border bg-white text-slate-700 border-slate-300 hover:bg-slate-100 flex items-center space-x-2">
                        <span>🛡️</span>
                        <span>Exchange Hot Pool (Bybit)</span>
                    </button>
                    <button onclick="setSimulationPreset('casual_sol')" id="simBtn-casual_sol" class="px-4 py-2.5 rounded-xl font-extrabold text-xs transition border bg-white text-slate-700 border-slate-300 hover:bg-slate-100 flex items-center space-x-2">
                        <span>💤</span>
                        <span>Casual Wallet (Solana)</span>
                    </button>
                </div>
            </div>

            <!-- Simulator Interactive Container -->
            <div class="max-w-5xl mx-auto gradient-border-card p-6 sm:p-10 shadow-xl">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Left Breakdown Info -->
                    <div class="lg:col-span-6 space-y-4 text-left">
                        <div class="flex items-center space-x-2">
                            <span id="simArchetypeBadge" class="px-3 py-1 rounded-lg bg-amber-100 text-amber-900 text-xs font-extrabold border border-amber-300">
                                🐋 High-Roller Shark Profile
                            </span>
                            <span id="simNetworkBadge" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-mono font-bold">
                                Ethereum Mainnet
                            </span>
                        </div>
                        <h3 id="simHeadline" class="text-2xl font-black text-slate-900">
                            High-Net-Worth VIP Detected ($24.8M)
                        </h3>
                        <p id="simDescription" class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            This wallet holds massive liquid ETH and stablecoin balances, with a proven history of 7-figure wagers on Stake and Rollbit. On-Score flags this player as <strong class="text-indigo-600 font-bold">SUPER_SHARK</strong> and assigns top VIP priority.
                        </p>

                        <div class="space-y-2 pt-2 text-xs">
                            <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 space-y-1">
                                <div class="font-extrabold text-slate-800 flex justify-between">
                                    <span>Casino Action Recommendation:</span>
                                    <span id="simActionRec" class="text-emerald-700 font-black">Assign Dedicated VIP Manager</span>
                                </div>
                                <div id="simActionDetail" class="text-slate-600">
                                    Trigger custom high-roller retention flow, raise deposit limits, and enable 1-on-1 VIP concierge support.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Live Simulated JSON Snapshot -->
                    <div class="lg:col-span-6 bg-night-950 rounded-2xl p-5 sm:p-6 text-white font-mono text-xs space-y-3 shadow-2xl border border-slate-800">
                        <div class="flex justify-between items-center border-b border-slate-800 pb-2 text-slate-400">
                            <span class="flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span class="font-bold text-white">Live Engine Response</span>
                            </span>
                            <span id="simEngineTag" class="text-emerald-400 font-bold text-[10px]">WPS-v1.0 (PROD)</span>
                        </div>
                        
                        <pre id="simJsonSnippet" class="text-slate-200 overflow-x-auto max-h-[280px] leading-relaxed text-[11px]">{
  "status": "completed",
  "score": {
    "value": 94,
    "segment": "super_vip",
    "custom_tags": ["SUPER_SHARK", "HIGH_ROLLER_VIP"]
  },
  "wallet": {
    "visible_balance_usd": 24864346.00,
    "is_custodial_cex": false
  },
  "gambling": {
    "status": "detected",
    "total_flow_365d_usd": 1820000.00,
    "entities_list": ["Stake.com", "Rollbit"]
  }
}</pre>
                    </div>

                </div>
            </div>
        </section>

        <!-- 3-STEP PIPELINE: HOW IT WORKS (PayAdmit Style) -->
        <section id="how-it-works" class="py-20 bg-white border-y border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3.5 py-1 rounded-full border border-emerald-200">3-Step Pipeline</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">How On-Score Analyzes Players in Real Time</h2>
                    <p class="text-sm sm:text-base text-slate-600">
                        Zero friction for players. Zero guesswork for VIP & Risk teams.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                    
                    <!-- Step 1 -->
                    <div class="gradient-border-card p-8 space-y-4 text-left relative">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-black text-xl">
                            01
                        </div>
                        <h4 class="text-xl font-black text-slate-900">Player Initiates Crypto Deposit</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            When a user sends USDT, TRX, ETH, BNB, SOL, or BTC to your cashier, your backend immediately notifies On-Score with the wallet address.
                        </p>
                        <div class="pt-2 flex items-center space-x-2 text-[11px] font-mono text-indigo-600 font-bold">
                            <span>POST /api/v1/analyses</span>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="gradient-border-card p-8 space-y-4 text-left relative">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center font-black text-xl">
                            02
                        </div>
                        <h4 class="text-xl font-black text-slate-900">Sub-1.2s Heuristic Scoring</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Our engine scans verified nodes, calculates liquid token balances, verifies exchange sweepers, and indexes interactions with known casino contracts.
                        </p>
                        <div class="pt-2 flex items-center space-x-2 text-[11px] font-mono text-emerald-600 font-bold">
                            <span>4-Pillar Model WPS-v1.0</span>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="gradient-border-card p-8 space-y-4 text-left relative">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center font-black text-xl">
                            03
                        </div>
                        <h4 class="text-xl font-black text-slate-900">Instant VIP Routing & Custom Tags</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Your CRM or cashier receives the completed dossier with custom client tags (e.g. <code>#SUPER_SHARK</code>) to trigger immediate personalized VIP perks.
                        </p>
                        <div class="pt-2 flex items-center space-x-2 text-[11px] font-mono text-amber-600 font-bold">
                            <span>Webhook & CRM Dispatch</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- CEX ISOLATION & SCORING PILLARS (Interactive Tabs) -->
        <section id="cex-isolation" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="text-xs font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-3.5 py-1 rounded-full border border-indigo-200">Architecture Deep-Dive</span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Core Engine Capabilities</h2>
                <p class="text-sm sm:text-base text-slate-600">
                    Switch between modules to explore how raw blockchain data transforms into actionable intelligence.
                </p>

                <!-- Interactive Tab Switcher -->
                <div class="flex flex-wrap items-center justify-center gap-2 pt-6">
                    <button onclick="switchTab('scoring')" id="tabBtn-scoring" class="px-5 py-2.5 rounded-xl font-extrabold text-xs transition border bg-slate-900 text-white border-slate-900 shadow-md">
                        01. 0–100 Player Scoring
                    </button>
                    <button onclick="switchTab('cex')" id="tabBtn-cex" class="px-5 py-2.5 rounded-xl font-extrabold text-xs transition border bg-white text-slate-700 border-slate-300 hover:bg-slate-100">
                        02. CEX Pool Isolation
                    </button>
                    <button onclick="switchTab('gambling')" id="tabBtn-gambling" class="px-5 py-2.5 rounded-xl font-extrabold text-xs transition border bg-white text-slate-700 border-slate-300 hover:bg-slate-100">
                        03. Gambling Footprint
                    </button>
                    <button onclick="switchTab('rules')" id="tabBtn-rules" class="px-5 py-2.5 rounded-xl font-extrabold text-xs transition border bg-white text-slate-700 border-slate-300 hover:bg-slate-100">
                        04. Custom Client Rules
                    </button>
                </div>
            </div>

            <!-- TAB CONTENT CONTAINER -->
            <div class="max-w-5xl mx-auto">
                
                <!-- Tab 1: Scoring -->
                <div id="tabContent-scoring" class="gradient-border-card p-8 sm:p-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-6 space-y-4 text-left">
                            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-black">
                                <span>Model WPS-v1.0</span>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900">Deterministic 4-Pillar Score</h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                When a user deposits 100 USDT, On-Score computes an instant 0–100 score weighted across 4 critical on-chain vectors:
                            </p>
                            <ul class="space-y-2 text-xs text-slate-700 font-semibold">
                                <li class="flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-emerald-600"></span><span>Financial Capacity (35%): Verified liquid tokens & turnover.</span></li>
                                <li class="flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-indigo-600"></span><span>Gambling Activity (35%): Active casino smart contract interactions.</span></li>
                                <li class="flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-amber-600"></span><span>Activity Recency (15%): Bets/transfers in last 48h / 7d.</span></li>
                                <li class="flex items-center space-x-2"><span class="w-2 h-2 rounded-full bg-purple-600"></span><span>Transaction Profile (15%): Average deposit velocity & tx count.</span></li>
                            </ul>
                        </div>
                        <div class="lg:col-span-6 bg-slate-900 rounded-2xl p-6 text-white font-mono text-xs space-y-3 shadow-xl">
                            <div class="flex justify-between border-b border-slate-800 pb-2 text-slate-400">
                                <span>Scoring Breakdown</span>
                                <span class="text-emerald-400 font-bold">WPS-v1.0</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between"><span>Financial Capacity (35%):</span> <span class="text-emerald-400 font-bold">100 / 100</span></div>
                                <div class="flex justify-between"><span>Gambling Activity (35%):</span> <span class="text-indigo-400 font-bold">96 / 100</span></div>
                                <div class="flex justify-between"><span>Activity Recency (15%):</span> <span class="text-amber-400 font-bold">100 / 100</span></div>
                                <div class="flex justify-between"><span>Transaction Profile (15%):</span> <span class="text-purple-400 font-bold">88 / 100</span></div>
                                <div class="pt-2 border-t border-slate-800 flex justify-between font-bold text-sm">
                                    <span class="text-white">Aggregate Score:</span>
                                    <span class="text-amber-400">94 (SUPER_SHARK)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: CEX Isolation -->
                <div id="tabContent-cex" class="gradient-border-card p-8 sm:p-12 hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-6 space-y-4 text-left">
                            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-lg bg-rose-50 text-rose-800 text-xs font-black">
                                <span>Anti-Hallucination</span>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900">Zero Exchange Pool False Positives</h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Standard crypto tools see Bybit or Binance hot wallets ($85M balance) and mistakenly label any depositor as an ultra-whale.
                            </p>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                On-Score’s <strong>Known Entities Database</strong> recognizes exchange pools, zeros out collective balances (<code class="text-rose-600 font-bold">visible_assets: $0.00</code>), and prevents false VIP perks for non-custodial exchange users.
                            </p>
                        </div>
                        <div class="lg:col-span-6 bg-slate-900 rounded-2xl p-6 text-white font-mono text-xs space-y-3 shadow-xl border border-rose-900/40">
                            <div class="flex justify-between border-b border-slate-800 pb-2 text-rose-400 font-bold">
                                <span>CEX Attribution Layer</span>
                                <span>[CUSTODIAL_POOL]</span>
                            </div>
                            <div class="space-y-1.5 text-slate-300">
                                <div><span class="text-slate-500">Address:</span> TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71</div>
                                <div><span class="text-slate-500">Entity:</span> <strong class="text-white">Bybit Hot Wallet 1</strong></div>
                                <div><span class="text-slate-500">Is Custodial CEX:</span> <span class="text-rose-400 font-bold">true</span></div>
                                <div><span class="text-slate-500">Raw Address Balance:</span> $85,000,000 USD</div>
                                <div><span class="text-slate-500">Adjusted Visible Assets:</span> <span class="text-amber-400 font-bold">$0.00 USD</span></div>
                                <div><span class="text-slate-500">Assigned Score:</span> <span class="text-slate-400 font-bold">0 / 100 (Non-Individual)</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Gambling Footprint -->
                <div id="tabContent-gambling" class="gradient-border-card p-8 sm:p-12 hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-6 space-y-4 text-left">
                            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-lg bg-indigo-50 text-indigo-800 text-xs font-black">
                                <span>Cross-Brand Intelligence</span>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900">Historical Casino Turnover Discovery</h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                On-Score indexes deposit routers and hot wallets for <strong>Stake.com, Rollbit, BC.Game, Roobet, 1xBet</strong> and other top operators.
                            </p>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                You immediately know if a player deposits $50,000/month on competitor platforms, what games they gravitate towards, and their cashout habits.
                            </p>
                        </div>
                        <div class="lg:col-span-6 bg-slate-900 rounded-2xl p-6 text-white font-mono text-xs space-y-3 shadow-xl">
                            <div class="flex justify-between border-b border-slate-800 pb-2 text-indigo-400 font-bold">
                                <span>Gambling Intelligence Snapshot</span>
                                <span>[ACTIVE_CASINO_USER]</span>
                            </div>
                            <div class="space-y-1.5 text-slate-300">
                                <div><span class="text-slate-500">Status:</span> <span class="text-emerald-400 font-bold">detected</span></div>
                                <div><span class="text-slate-500">Known Brands:</span> <span class="text-white font-bold">["Stake.com", "Rollbit"]</span></div>
                                <div><span class="text-slate-500">Outgoing Deposits (365d):</span> <span class="text-white font-bold">$1,250,000.00</span></div>
                                <div><span class="text-slate-500">Incoming Cashouts (365d):</span> <span class="text-white font-bold">$570,000.00</span></div>
                                <div><span class="text-slate-500">Last Gambling Activity:</span> <span class="text-emerald-400 font-bold">4 hours ago</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Custom Rules -->
                <div id="tabContent-rules" class="gradient-border-card p-8 sm:p-12 hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-6 space-y-4 text-left">
                            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-lg bg-amber-50 text-amber-800 text-xs font-black">
                                <span>Per-Client Customization</span>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900">Custom Thresholds & VIP Tags</h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Every casino has its own VIP tiers. On-Score lets you configure custom thresholds and tags in the Admin Panel:
                            </p>
                            <div class="space-y-1.5 text-xs font-semibold text-slate-700">
                                <div>• If <code class="text-indigo-600 font-bold">visible_balance &gt;= $200,000</code> &rarr; tag <strong>SUPER_SHARK</strong>.</div>
                                <div>• If <code class="text-indigo-600 font-bold">gambling_flow &gt;= $50,000</code> &rarr; tag <strong>HIGH_ROLLER_GAMBLER</strong>.</div>
                                <div>• Custom overrides directly propagate to your CRM via webhooks and REST API.</div>
                            </div>
                        </div>
                        <div class="lg:col-span-6 bg-slate-900 rounded-2xl p-6 text-white font-mono text-xs space-y-3 shadow-xl">
                            <div class="flex justify-between border-b border-slate-800 pb-2 text-amber-400 font-bold">
                                <span>Admin Rule Config</span>
                                <span>[CLIENT_CUSTOMIZED]</span>
                            </div>
                            <pre class="text-slate-300 overflow-x-auto">{
  <span class="syn-key">"super_vip_threshold_usd"</span>: <span class="syn-num">200000</span>,
  <span class="syn-key">"custom_rules"</span>: [
    {
      <span class="syn-key">"metric"</span>: <span class="syn-str">"visible_balance_usd"</span>,
      <span class="syn-key">"operator"</span>: <span class="syn-str">"&gt;="</span>,
      <span class="syn-key">"value"</span>: <span class="syn-num">200000</span>,
      <span class="syn-key">"tag"</span>: <span class="syn-str">"SUPER_SHARK"</span>,
      <span class="syn-key">"tier"</span>: <span class="syn-str">"super_vip"</span>
    }
  ]
}</pre>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- LIVE JSON & SDK SHOWCASE SECTION (PayAdmit Style) -->
        <section id="json-preview" class="py-24 bg-night-950 text-white relative border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto space-y-3 mb-12">
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-3.5 py-1 rounded-full border border-emerald-500/30">Developer Integration</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Standardized JSON API Response</h2>
                    <p class="text-sm text-slate-400">
                        One simple REST call returns the entire on-chain dossier ready for automated CRM & VIP routing.
                    </p>

                    <!-- Code Language Selector -->
                    <div class="flex flex-wrap items-center justify-center gap-2 pt-4">
                        <button onclick="switchCodeTab('json')" id="codeBtn-json" class="px-4 py-2 rounded-xl text-xs font-extrabold transition bg-indigo-600 text-white shadow-md">
                            JSON Response (201 Created)
                        </button>
                        <button onclick="switchCodeTab('curl')" id="codeBtn-curl" class="px-4 py-2 rounded-xl text-xs font-extrabold transition bg-slate-800 text-slate-300 hover:bg-slate-700">
                            cURL
                        </button>
                        <button onclick="switchCodeTab('python')" id="codeBtn-python" class="px-4 py-2 rounded-xl text-xs font-extrabold transition bg-slate-800 text-slate-300 hover:bg-slate-700">
                            Python SDK
                        </button>
                        <button onclick="switchCodeTab('nodejs')" id="codeBtn-nodejs" class="px-4 py-2 rounded-xl text-xs font-extrabold transition bg-slate-800 text-slate-300 hover:bg-slate-700">
                            Node.js / TS
                        </button>
                        <button onclick="switchCodeTab('php')" id="codeBtn-php" class="px-4 py-2 rounded-xl text-xs font-extrabold transition bg-slate-800 text-slate-300 hover:bg-slate-700">
                            PHP / Laravel
                        </button>
                    </div>
                </div>

                <!-- Code Viewer Container -->
                <div class="max-w-5xl mx-auto dark-glass-card rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl relative">
                    
                    <!-- Copy Button -->
                    <button onclick="copyCurrentCodeSnippet()" class="absolute top-6 right-6 px-3.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs text-slate-300 font-mono font-bold flex items-center space-x-1.5 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span id="copyBtnText">Copy Code</span>
                    </button>

                    <!-- Tab: JSON -->
                    <div id="codeView-json" class="font-mono text-xs text-slate-200 overflow-x-auto max-h-[500px]">
                        <pre id="codeSnippet-json">{
  <span class="syn-key">"analysis_id"</span>: <span class="syn-str">"01m1xn8954a6bye6x91mhtjmkk"</span>,
  <span class="syn-key">"status"</span>: <span class="syn-str">"completed"</span>,
  <span class="syn-key">"created_at"</span>: <span class="syn-str">"2026-09-07T12:00:00+00:00"</span>,
  <span class="syn-key">"wallet"</span>: {
    <span class="syn-key">"network"</span>: <span class="syn-str">"tron"</span>,
    <span class="syn-key">"address"</span>: <span class="syn-str">"TH7XRsbntBXxdLGx47b1xy2NgAhKfGUvYx"</span>,
    <span class="syn-key">"is_custodial_cex"</span>: <span class="syn-bool">false</span>,
    <span class="syn-key">"wallet_age_days"</span>: <span class="syn-num">340</span>,
    <span class="syn-key">"transactions_count"</span>: <span class="syn-num">118</span>
  },
  <span class="syn-key">"score"</span>: {
    <span class="syn-key">"value"</span>: <span class="syn-num">94</span>,
    <span class="syn-key">"segment"</span>: <span class="syn-str">"super_vip"</span>,
    <span class="syn-key">"confidence"</span>: <span class="syn-num">0.98</span>,
    <span class="syn-key">"key_drivers"</span>: [
      <span class="syn-str">"visible_assets_gt_200k"</span>,
      <span class="syn-str">"high_gambling_volume_gt_100k"</span>,
      <span class="syn-str">"gambling_activity_last_48h"</span>,
      <span class="syn-str">"SUPER_SHARK"</span>
    ],
    <span class="syn-key">"custom_tags"</span>: [<span class="syn-str">"SUPER_SHARK"</span>]
  },
  <span class="syn-key">"financial_profile"</span>: {
    <span class="syn-key">"visible_balance_usd"</span>: <span class="syn-num">284500.00</span>,
    <span class="syn-key">"lifetime_turnover_usd"</span>: <span class="syn-num">3480000.00</span>,
    <span class="syn-key">"tokens"</span>: {
      <span class="syn-key">"USDT"</span>: { <span class="syn-key">"amount"</span>: <span class="syn-num">280000.00</span>, <span class="syn-key">"usd_value"</span>: <span class="syn-num">280000.00</span> },
      <span class="syn-key">"TRX"</span>: { <span class="syn-key">"amount"</span>: <span class="syn-num">18000.00</span>, <span class="syn-key">"usd_value"</span>: <span class="syn-num">4500.00</span> }
    }
  },
  <span class="syn-key">"gambling"</span>: {
    <span class="syn-key">"status"</span>: <span class="syn-str">"detected"</span>,
    <span class="syn-key">"entities_count"</span>: <span class="syn-num">2</span>,
    <span class="syn-key">"entities_list"</span>: [<span class="syn-str">"Stake.com"</span>, <span class="syn-str">"Rollbit"</span>],
    <span class="syn-key">"total_flow_365d_usd"</span>: <span class="syn-num">1820000.00</span>,
    <span class="syn-key">"last_gambling_activity"</span>: <span class="syn-str">"2026-09-07T08:30:00+00:00"</span>
  },
  <span class="syn-key">"credits_charged"</span>: <span class="syn-num">3</span>
}</pre>
                    </div>

                    <!-- Tab: cURL -->
                    <div id="codeView-curl" class="font-mono text-xs text-slate-200 overflow-x-auto max-h-[500px] hidden">
                        <pre id="codeSnippet-curl">curl -X POST http://127.0.0.1:8080/api/v1/analyses \
  -H "X-API-Key: ons_live_your_api_key_here" \
  -H "X-API-Secret: ons_sec_your_secret_hash_here" \
  -H "Content-Type: application/json" \
  -d '{
    "network": "tron",
    "address": "TH7XRsbntBXxdLGx47b1xy2NgAhKfGUvYx",
    "external_player_id": "casino_user_9921",
    "context": {
      "deposit_amount": 500,
      "deposit_asset": "USDT"
    }
  }'</pre>
                    </div>

                    <!-- Tab: Python -->
                    <div id="codeView-python" class="font-mono text-xs text-slate-200 overflow-x-auto max-h-[500px] hidden">
                        <pre id="codeSnippet-python">import requests

url = "http://127.0.0.1:8080/api/v1/analyses"
headers = {
    "X-API-Key": "ons_live_your_api_key_here",
    "X-API-Secret": "ons_sec_your_secret_hash_here",
    "Content-Type": "application/json"
}
payload = {
    "network": "tron",
    "address": "TH7XRsbntBXxdLGx47b1xy2NgAhKfGUvYx",
    "external_player_id": "casino_user_9921"
}

response = requests.post(url, json=payload, headers=headers)
profile = response.json()

# Trigger instant VIP host assignment if scored as super_vip
if profile["score"]["segment"] == "super_vip":
    print(f"VIP Shark Identified! Balance: ${profile['financial_profile']['visible_balance_usd']:,}")
    print(f"Custom Tags: {profile['score']['custom_tags']}")</pre>
                    </div>

                    <!-- Tab: Node.js -->
                    <div id="codeView-nodejs" class="font-mono text-xs text-slate-200 overflow-x-auto max-h-[500px] hidden">
                        <pre id="codeSnippet-nodejs">import axios from 'axios';

async function evaluatePlayerDeposit(walletAddress, network = 'tron') {
  const { data } = await axios.post('http://127.0.0.1:8080/api/v1/analyses', {
    network,
    address: walletAddress,
    external_player_id: 'casino_user_9921'
  }, {
    headers: {
      'X-API-Key': process.env.ONSCORE_API_KEY,
      'X-API-Secret': process.env.ONSCORE_API_SECRET
    }
  });

  if (data.score.segment === 'super_vip') {
    // Notify VIP manager on Telegram or CRM
    console.log(`[VIP ALERT] Player scored ${data.score.value}/100. Tags:`, data.score.custom_tags);
  }

  return data;
}</pre>
                    </div>

                    <!-- Tab: PHP -->
                    <div id="codeView-php" class="font-mono text-xs text-slate-200 overflow-x-auto max-h-[500px] hidden">
                        <pre id="codeSnippet-php">&lt;?php

use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'X-API-Key' => config('services.onscore.key'),
    'X-API-Secret' => config('services.onscore.secret'),
])->post('http://127.0.0.1:8080/api/v1/analyses', [
    'network' => 'tron',
    'address' => 'TH7XRsbntBXxdLGx47b1xy2NgAhKfGUvYx',
    'external_player_id' => 'casino_user_9921',
]);

$dossier = $response->json();

if ($dossier['score']['segment'] === 'super_vip') {
    // Dispatch VIP bonus & high-roller treatment
    event(new HighRollerPlayerDetected($dossier));
}</pre>
                    </div>

                </div>
            </div>
        </section>

        <!-- 3x3 FEATURE MATRIX GRID (PayAdmit Style) -->
        <section id="features" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3.5 py-1 rounded-full border border-emerald-200">Platform Capabilities</span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Engineered for High-Stakes Operations</h2>
                <p class="text-sm sm:text-base text-slate-600">
                    Everything crypto casinos and sportsbooks need to maximize player LTV and protect treasury margins.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- 1 -->
                <div class="gradient-border-card p-6 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black">
                        ⚡
                    </div>
                    <h4 class="font-extrabold text-slate-900 text-base">Sub-1.2s Real-Time Latency</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Evaluates deposit transactions instantaneously before the player even finishes loading the casino lobby.
                    </p>
                </div>

                <!-- 2 -->
                <div class="gradient-border-card p-6 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center font-black">
                        🛡️
                    </div>
                    <h4 class="font-extrabold text-slate-900 text-base">CEX Sweeper Pool Isolation</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Recognizes Bybit, Binance, OKX hot wallets and sweeps to ensure pooled exchange balances are never attributed to individual players.
                    </p>
                </div>

                <!-- 3 -->
                <div class="gradient-border-card p-6 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-black">
                        🎰
                    </div>
                    <h4 class="font-extrabold text-slate-900 text-base">Cross-Casino Footprints</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Identifies historical deposits and withdrawals across Stake.com, Rollbit, BC.Game, Roobet, and 1xBet.
                    </p>
                </div>

                <!-- 4 -->
                <div class="gradient-border-card p-6 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center font-black">
                        ⚙️
                    </div>
                    <h4 class="font-extrabold text-slate-900 text-base">Custom Client Scoring Rules</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Customize asset thresholds ($200k+), custom tag names (SUPER_SHARK), and VIP tiers per casino operator in the Admin portal.
                    </p>
                </div>

                <!-- 5 -->
                <div class="gradient-border-card p-6 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-800 flex items-center justify-center font-black">
                        🌐
                    </div>
                    <h4 class="font-extrabold text-slate-900 text-base">Full 5-Chain Support</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Native support for TRON (TRC20), Ethereum (ERC20), BNB Chain (BEP20), Solana (SPL), and Bitcoin (UTXO).
                    </p>
                </div>

                <!-- 6 -->
                <div class="gradient-border-card p-6 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center font-black">
                        🔒
                    </div>
                    <h4 class="font-extrabold text-slate-900 text-base">Immutable Ledger & Auth</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        B2B API authentication with Key & Secret hashing, atomic credit reservation, and transparent billing audit ledger.
                    </p>
                </div>

            </div>
        </section>

        <!-- FAQ ACCORDION SECTION (PayAdmit Style) -->
        <section id="faq" class="py-20 bg-white border-t border-slate-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="text-center space-y-2">
                    <span class="text-xs font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-3.5 py-1 rounded-full border border-indigo-200">FAQ</span>
                    <h2 class="text-3xl font-black text-slate-900">Frequently Asked Questions</h2>
                </div>

                <div class="space-y-4">
                    <!-- Q1 -->
                    <div class="border border-slate-200 rounded-2xl p-5 hover:border-slate-300 transition bg-slate-50">
                        <div class="font-extrabold text-slate-900 text-sm">How does On-Score detect VIP players on their very first deposit?</div>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            When a player deposits crypto, their wallet address is publicly recorded on the blockchain. On-Score queries indexed nodes to analyze historical turnover, current liquid balances (USDT, ETH, BTC, SOL), and past interactions with known gaming smart contracts.
                        </p>
                    </div>

                    <!-- Q2 -->
                    <div class="border border-slate-200 rounded-2xl p-5 hover:border-slate-300 transition bg-slate-50">
                        <div class="font-extrabold text-slate-900 text-sm">What prevents an exchange hot wallet from being marked as a VIP?</div>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            Our proprietary Entity Attribution service maintains a real-time database of exchange custodial hot wallets (Bybit, Binance, OKX). If an address matches an exchange pool, the collective balance is zeroed out (<code class="text-xs bg-slate-200 px-1 py-0.5 rounded font-bold">visible_assets: $0.00</code>) and flagged accordingly.
                        </p>
                    </div>

                    <!-- Q3 -->
                    <div class="border border-slate-200 rounded-2xl p-5 hover:border-slate-300 transition bg-slate-50">
                        <div class="font-extrabold text-slate-900 text-sm">How can we configure custom scoring rules for our casino?</div>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            Inside the On-Score Admin Portal, each B2B client account has a dedicated "Custom Rules" configuration manager. You can adjust balance thresholds (e.g. $200k) and assign custom tag strings (e.g. <code class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-1 py-0.5 rounded font-bold">SUPER_SHARK</code>).
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- HIGH-CONVERTING BOTTOM CTA SECTION (PayAdmit Dark Atmospheric Theme) -->
    <section class="py-24 bg-night-950 text-white text-center relative border-t border-white/10 hero-mesh">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 relative z-10">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-3.5 py-1 rounded-full border border-emerald-500/30">Get Started Today</span>
            <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
                Start Scoring Crypto Players at Scale
            </h2>
            <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto font-normal">
                Integrate in under 15 minutes. Explore the interactive OpenAPI documentation or sign in to the Admin Portal to generate live API credentials.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <a href="/docs" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-emerald-400 to-cyan-400 hover:from-emerald-300 hover:to-cyan-300 text-night-950 text-sm font-black rounded-2xl shadow-xl shadow-emerald-500/20 transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    <span>Open API Documentation (/docs)</span>
                </a>
                <a href="/login" class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/15 border border-white/20 text-white text-sm font-bold rounded-2xl transition">
                    Admin Portal Sign In
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-night-950 border-t border-white/10 py-10 text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-indigo-500 to-emerald-400 flex items-center justify-center font-black text-white text-xs">
                    O
                </div>
                <div>
                    &copy; 2026 <strong class="text-white font-extrabold">on-score.io</strong>. High-Agency On-Chain Intelligence Platform.
                </div>
            </div>
            <div class="flex space-x-6 text-slate-300 font-semibold">
                <a href="/docs" class="hover:text-emerald-400">API Documentation</a>
                <a href="/OnScore_API_Postman_Collection.json" download class="hover:text-orange-400">Postman Collection</a>
                <a href="/login" class="hover:text-emerald-400">Admin Portal</a>
                <span class="text-slate-500">Model: WPS-v1.0</span>
            </div>
        </div>
    </footer>

    <!-- Interactive JavaScript -->
    <script>
    // Presets for the Interactive Simulator
    const presets = {
        whale_eth: {
            address: "0xde0B29...7BAe (Ethereum)",
            network: "Ethereum Mainnet",
            badge: "VIP",
            badgeClass: "bg-gradient-to-tr from-amber-500 to-amber-300 text-night-950",
            statusText: "SUPER_SHARK",
            statusBadgeClass: "bg-amber-500/20 text-amber-300 border-amber-500/40",
            score: 94,
            scoreBoxClass: "bg-gradient-to-tr from-amber-500 to-amber-300 text-night-950",
            scoreTier: "VIP Tier 5 (SUPER_SHARK)",
            confidence: "Confidence: 98% • Latency: 0.94s",
            balanceUsd: "$24,864,346",
            balanceTokens: "6,774.49 ETH + USDT",
            gamblingFlow: "$1,820,000",
            gamblingBrands: "Stake.com, Rollbit",
            tags: ["#SUPER_SHARK", "#WHALE_LIQUIDITY_GT_400K", "#STAKE_VETERAN"],
            bottomText: "VIP Host Auto-Assigned in CRM",
            simBadge: "🐋 High-Roller Shark Profile",
            headline: "High-Net-Worth VIP Detected ($24.8M)",
            description: "This wallet holds massive liquid ETH and stablecoin balances, with a proven history of 7-figure wagers on Stake and Rollbit. On-Score flags this player as SUPER_SHARK and assigns top VIP priority.",
            actionRec: "Assign Dedicated VIP Host",
            actionDetail: "Trigger custom high-roller retention flow, raise deposit limits, and enable 1-on-1 VIP concierge support.",
            jsonSnippet: `{\n  "status": "completed",\n  "score": {\n    "value": 94,\n    "segment": "super_vip",\n    "custom_tags": ["SUPER_SHARK", "HIGH_ROLLER_VIP"]\n  },\n  "wallet": {\n    "visible_balance_usd": 24864346.00,\n    "is_custodial_cex": false\n  },\n  "gambling": {\n    "status": "detected",\n    "total_flow_365d_usd": 1820000.00,\n    "entities_list": ["Stake.com", "Rollbit"]\n  }\n}`
        },
        tron_gambler: {
            address: "TH7XRsb...GUvYx (TRON TRC-20)",
            network: "TRON Mainnet",
            badge: "VIP",
            badgeClass: "bg-gradient-to-tr from-emerald-400 to-teal-500 text-night-950",
            statusText: "HIGH_ROLLER",
            statusBadgeClass: "bg-emerald-500/20 text-emerald-300 border-emerald-500/40",
            score: 88,
            scoreBoxClass: "bg-gradient-to-tr from-emerald-400 to-teal-500 text-night-950",
            scoreTier: "VIP Tier 4 (High Roller)",
            confidence: "Confidence: 96% • Latency: 0.82s",
            balanceUsd: "$284,500",
            balanceTokens: "280,000 USDT + TRX",
            gamblingFlow: "$640,000",
            gamblingBrands: "Stake.com, BC.Game",
            tags: ["#HIGH_ROLLER", "#FREQUENT_DEPOSITOR", "#STAKE_USER"],
            bottomText: "Automated 20% Deposit Match Bonus Queued",
            simBadge: "⚡ Active TRON Gambler Profile",
            headline: "Active High-Velocity Bettor ($284k)",
            description: "Frequent TRON USDT depositor with rapid turnover and regular betting activity across multiple casinos. High retention potential.",
            actionRec: "Instant Retention Reload Offer",
            actionDetail: "Dispatch automated personalized reload offer and fast-track withdrawal queue.",
            jsonSnippet: `{\n  "status": "completed",\n  "score": {\n    "value": 88,\n    "segment": "vip",\n    "custom_tags": ["HIGH_ROLLER", "FREQUENT_DEPOSITOR"]\n  },\n  "wallet": {\n    "visible_balance_usd": 284500.00,\n    "is_custodial_cex": false\n  },\n  "gambling": {\n    "status": "detected",\n    "total_flow_365d_usd": 640000.00,\n    "entities_list": ["Stake.com", "BC.Game"]\n  }\n}`
        },
        bybit_cex: {
            address: "TLyqzV...TmTX71 (Bybit Hot Pool)",
            network: "TRON (Custodial)",
            badge: "CEX",
            badgeClass: "bg-gradient-to-tr from-rose-500 to-red-400 text-white",
            statusText: "CEX_HOT_WALLET",
            statusBadgeClass: "bg-rose-500/20 text-rose-300 border-rose-500/40",
            score: 0,
            scoreBoxClass: "bg-gradient-to-tr from-slate-700 to-slate-800 text-slate-300",
            scoreTier: "Non-Individual (Exchange Pool)",
            confidence: "Attribution: 100% Bybit Hot Wallet",
            balanceUsd: "$0.00 (Adjusted)",
            balanceTokens: "Raw $85M Swept to $0",
            gamblingFlow: "$0.00",
            gamblingBrands: "None (Pooled Address)",
            tags: ["#CEX_HOT_WALLET", "#ANTI_HALLUCINATION", "#NON_INDIVIDUAL"],
            bottomText: "False-VIP Hallucination Suppressed",
            simBadge: "🛡️ Exchange Hot Pool (Anti-Hallucination)",
            headline: "Bybit Exchange Pool Isolated ($0.00 Visible)",
            description: "On-Score recognizes this as a shared custodial hot wallet. It prevents the casino from erroneously assigning VIP perks or credit based on pooled customer funds.",
            actionRec: "Standard Retail Cashier Routing",
            actionDetail: "Treat as standard retail player; do not issue exclusive whale perks based on exchange treasury size.",
            jsonSnippet: `{\n  "status": "completed",\n  "score": {\n    "value": 0,\n    "segment": "unrated",\n    "custom_tags": ["CEX_HOT_WALLET"]\n  },\n  "wallet": {\n    "visible_balance_usd": 0.00,\n    "is_custodial_cex": true,\n    "entity_name": "Bybit Hot Wallet 1"\n  }\n}`
        },
        casual_sol: {
            address: "4k3Dyj...91nB4 (Solana SPL)",
            network: "Solana Mainnet",
            badge: "STD",
            badgeClass: "bg-gradient-to-tr from-slate-600 to-slate-700 text-white",
            statusText: "CASUAL_PLAYER",
            statusBadgeClass: "bg-slate-500/20 text-slate-300 border-slate-500/40",
            score: 18,
            scoreBoxClass: "bg-gradient-to-tr from-slate-700 to-slate-800 text-slate-300",
            scoreTier: "Retail Tier 1 (Casual)",
            confidence: "Confidence: 94% • Latency: 0.65s",
            balanceUsd: "$145.20",
            balanceTokens: "0.85 SOL + $25 USDT",
            gamblingFlow: "$0.00",
            gamblingBrands: "No History",
            tags: ["#CASUAL_PLAYER", "#LOW_ACTIVITY", "#NEW_WALLET"],
            bottomText: "Standard Automated Onboarding Triggered",
            simBadge: "💤 Casual Solana Wallet Profile",
            headline: "Standard Retail Depositor ($145)",
            description: "Small balance and no prior gambling footprint. Suitable for standard automated retail welcome campaigns without manual host intervention.",
            actionRec: "Welcome Free Spins Campaign",
            actionDetail: "Trigger standard gamified welcome bonus flow and regular churn-prevention sequence.",
            jsonSnippet: `{\n  "status": "completed",\n  "score": {\n    "value": 18,\n    "segment": "casual",\n    "custom_tags": ["CASUAL_PLAYER"]\n  },\n  "wallet": {\n    "visible_balance_usd": 145.20,\n    "is_custodial_cex": false\n  },\n  "gambling": {\n    "status": "none",\n    "total_flow_365d_usd": 0.00\n  }\n}`
        }
    };

    function setSimulationPreset(key) {
        const p = presets[key];
        if (!p) return;

        // Button states
        ['whale_eth', 'tron_gambler', 'bybit_cex', 'casual_sol'].forEach(k => {
            const btn = document.getElementById('simBtn-' + k);
            if (btn) {
                if (k === key) {
                    btn.className = 'px-4 py-2.5 rounded-xl font-extrabold text-xs transition border bg-slate-900 text-white border-slate-900 shadow-md flex items-center space-x-2';
                } else {
                    btn.className = 'px-4 py-2.5 rounded-xl font-extrabold text-xs transition border bg-white text-slate-700 border-slate-300 hover:bg-slate-100 flex items-center space-x-2';
                }
            }
        });

        // Update Hero Mockup Card
        const cardBadgeIcon = document.getElementById('cardBadgeIcon');
        if (cardBadgeIcon) {
            cardBadgeIcon.innerText = p.badge;
            cardBadgeIcon.className = `w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm font-mono shadow-md ${p.badgeClass}`;
        }
        document.getElementById('cardPlayerAddress').innerText = p.address;
        
        const cardStatusBadge = document.getElementById('cardStatusBadge');
        cardStatusBadge.innerText = p.statusText;
        cardStatusBadge.className = `px-2.5 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wider ${p.statusBadgeClass}`;

        document.getElementById('cardScoreTier').innerText = p.scoreTier;
        document.getElementById('cardConfidence').innerText = p.confidence;
        document.getElementById('cardScoreNumber').innerText = p.score;
        
        const cardScoreBox = document.getElementById('cardScoreBox');
        cardScoreBox.className = `w-16 h-16 rounded-2xl flex flex-col items-center justify-center font-black shadow-lg ${p.scoreBoxClass}`;

        document.getElementById('cardBalanceUsd').innerText = p.balanceUsd;
        document.getElementById('cardBalanceTokens').innerText = p.balanceTokens;
        document.getElementById('cardGamblingFlow').innerText = p.gamblingFlow;
        document.getElementById('cardGamblingBrands').innerText = p.gamblingBrands;
        document.getElementById('cardBottomText').innerText = p.bottomText;

        // Update tags
        const tagsContainer = document.getElementById('cardTagsContainer');
        tagsContainer.innerHTML = p.tags.map(t => `<span class="px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 text-[10px] font-bold">${t}</span>`).join('');

        // Update Simulator section
        document.getElementById('simArchetypeBadge').innerText = p.simBadge;
        document.getElementById('simNetworkBadge').innerText = p.network;
        document.getElementById('simHeadline').innerText = p.headline;
        document.getElementById('simDescription').innerText = p.description;
        document.getElementById('simActionRec').innerText = p.actionRec;
        document.getElementById('simActionDetail').innerText = p.actionDetail;
        document.getElementById('simJsonSnippet').innerText = p.jsonSnippet;
    }

    function switchTab(tabKey) {
        ['scoring', 'cex', 'gambling', 'rules'].forEach(k => {
            const btn = document.getElementById('tabBtn-' + k);
            const content = document.getElementById('tabContent-' + k);
            if (k === tabKey) {
                btn.className = 'px-5 py-2.5 rounded-xl font-extrabold text-xs transition border bg-slate-900 text-white border-slate-900 shadow-md';
                content.classList.remove('hidden');
            } else {
                btn.className = 'px-5 py-2.5 rounded-xl font-extrabold text-xs transition border bg-white text-slate-700 border-slate-300 hover:bg-slate-100';
                content.classList.add('hidden');
            }
        });
    }

    let currentActiveCodeTab = 'json';
    function switchCodeTab(lang) {
        currentActiveCodeTab = lang;
        ['json', 'curl', 'python', 'nodejs', 'php'].forEach(l => {
            const btn = document.getElementById('codeBtn-' + l);
            const view = document.getElementById('codeView-' + l);
            if (l === lang) {
                btn.className = 'px-4 py-2 rounded-xl text-xs font-extrabold transition bg-indigo-600 text-white shadow-md';
                view.classList.remove('hidden');
            } else {
                btn.className = 'px-4 py-2 rounded-xl text-xs font-extrabold transition bg-slate-800 text-slate-300 hover:bg-slate-700';
                view.classList.add('hidden');
            }
        });
    }

    function copyCurrentCodeSnippet() {
        const codeElement = document.getElementById('codeSnippet-' + currentActiveCodeTab);
        if (codeElement) {
            navigator.clipboard.writeText(codeElement.innerText);
            const btnText = document.getElementById('copyBtnText');
            btnText.innerText = 'Copied!';
            setTimeout(() => { btnText.innerText = 'Copy Code'; }, 2000);
        }
    }
    </script>
</body>
</html>