<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>on-score.io — On-Chain Player Intelligence & Crypto Scoring Infrastructure</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    
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
                        stripe: {
                            navy: '#0a2540',
                            slate: '#425466',
                            muted: '#697386',
                            light: '#f6f9fc',
                            border: '#e6ebf1',
                            indigo: '#635bff',
                            'indigo-hover': '#5851ea',
                            cyan: '#00d4ff',
                            pink: '#ff6080',
                            amber: '#ffa84a',
                            purple: '#7a73ff',
                            emerald: '#00d66f',
                            darkbg: '#081726',
                        }
                    },
                    keyframes: {
                        'float-slow': {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        'float-reverse': {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(8px)' },
                        },
                        'pulse-glow': {
                            '0%, 100%': { opacity: '0.6', transform: 'scale(1)' },
                            '50%': { opacity: '0.9', transform: 'scale(1.05)' },
                        },
                        'shimmer': {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(200%)' }
                        }
                    },
                    animation: {
                        'float': 'float-slow 6s ease-in-out infinite',
                        'float-rev': 'float-reverse 7s ease-in-out infinite',
                        'glow': 'pulse-glow 5s ease-in-out infinite',
                        'shimmer': 'shimmer 2.5s infinite',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #ffffff;
            color: #425466;
            overflow-x: hidden;
        }

        /* Stripe Signature Diagonal Mesh Gradient Background */
        .stripe-hero-canvas {
            position: relative;
            background-color: #ffffff;
            overflow: hidden;
        }

        .stripe-gradient-mesh {
            position: absolute;
            top: -240px;
            right: -180px;
            width: 1200px;
            height: 950px;
            background: 
                radial-gradient(circle at 75% 30%, rgba(255, 96, 128, 0.45) 0%, transparent 45%),
                radial-gradient(circle at 45% 45%, rgba(122, 115, 255, 0.55) 0%, transparent 50%),
                radial-gradient(circle at 85% 70%, rgba(255, 168, 74, 0.45) 0%, transparent 40%),
                radial-gradient(circle at 30% 70%, rgba(0, 212, 255, 0.45) 0%, transparent 45%),
                linear-gradient(135deg, rgba(99, 91, 255, 0.4) 0%, rgba(255, 96, 128, 0.25) 100%);
            filter: blur(75px);
            opacity: 0.9;
            transform: rotate(-12deg) skew(-8deg);
            pointer-events: none;
            z-index: 1;
        }

        /* Diffused Stripe Glass Card */
        .stripe-card {
            background: #ffffff;
            border: 1px solid #e6ebf1;
            border-radius: 1.5rem;
            box-shadow: 0 13px 27px -5px rgba(50, 50, 93, 0.08), 0 8px 16px -8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .stripe-card:hover {
            box-shadow: 0 30px 60px -12px rgba(50, 50, 93, 0.15), 0 18px 36px -18px rgba(0, 0, 0, 0.12);
            transform: translateY(-3px);
        }

        .stripe-glass-hero {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 30px 60px -12px rgba(50, 50, 93, 0.18), 0 18px 36px -18px rgba(0, 0, 0, 0.14);
        }

        .stripe-dark-card {
            background: #081726;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .stripe-pill-button {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .stripe-pill-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(99, 91, 255, 0.35);
        }
        .stripe-pill-button:active {
            transform: translateY(1px);
        }

        /* Syntax styling */
        .syn-key { color: #7dd3fc; font-weight: 600; }
        .syn-str { color: #a7f3d0; }
        .syn-num { color: #fde047; font-weight: 700; }
        .syn-bool { color: #f472b6; font-weight: 700; }
        .syn-tag { color: #c084fc; font-weight: 700; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="antialiased selection:bg-stripe-indigo selection:text-white">

    <!-- ========================================== -->
    <!-- 1. NAVIGATION (Stripe Style)               -->
    <!-- ========================================== -->
    <header class="relative z-30 w-full transition-all bg-white/80 backdrop-blur-md border-b border-stripe-border/60 sticky top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo: 2 Parts (Left Icon + Right Text) -->
            <div class="flex items-center space-x-8">
                <a href="/" class="flex items-center space-x-3.5 group">
                    <!-- Left Icon Emblem -->
                    <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200/90 p-1.5 shadow-md shadow-indigo-500/10 group-hover:scale-105 transition-transform flex items-center justify-center shrink-0">
                        <svg viewBox="0 0 100 100" class="w-full h-full" fill="none">
                            <defs>
                                <linearGradient id="navOrbitGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#00d4ff" />
                                    <stop offset="35%" stop-color="#635bff" />
                                    <stop offset="70%" stop-color="#7a73ff" />
                                    <stop offset="100%" stop-color="#ff6080" />
                                </linearGradient>
                                <linearGradient id="navPulseGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#00d4ff" />
                                    <stop offset="50%" stop-color="#635bff" />
                                    <stop offset="100%" stop-color="#ff6080" />
                                </linearGradient>
                            </defs>
                            <circle cx="50" cy="50" r="32" stroke="url(#navOrbitGrad)" stroke-width="7.5" stroke-linecap="round" />
                            <ellipse cx="50" cy="50" rx="44" ry="18" stroke="url(#navOrbitGrad)" stroke-width="6" transform="rotate(-30 50 50)" stroke-linecap="round" />
                            <path d="M 24 50 L 38 50 L 44 65 L 56 32 L 63 56 L 68 50 L 76 50" stroke="url(#navPulseGrad)" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <!-- Right Text -->
                    <div class="flex flex-col">
                        <span class="text-2xl font-black tracking-tight text-stripe-navy font-sans leading-none">
                            on-score<span class="text-stripe-indigo">.io</span>
                        </span>
                        <span class="text-[9px] uppercase font-extrabold tracking-widest text-stripe-slate mt-1">
                            Player Intelligence
                        </span>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden lg:flex items-center space-x-7 text-sm font-bold text-stripe-navy">
                    <a href="#features" class="text-stripe-slate hover:text-stripe-navy transition">Features</a>
                    <a href="#simulator" class="text-stripe-slate hover:text-stripe-navy transition flex items-center space-x-1.5">
                        <span>Live Simulator</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-stripe-indigo/10 text-stripe-indigo">Interactive</span>
                    </a>
                    <a href="#developers" class="text-stripe-slate hover:text-stripe-navy transition">API & SDKs</a>
                    <a href="/docs" class="text-stripe-slate hover:text-stripe-navy transition">API Docs</a>
                </nav>
            </div>

            <!-- Right CTA Action Buttons -->
            <div class="flex items-center space-x-4">
                <a href="/login" class="text-sm font-bold text-stripe-navy hover:text-stripe-indigo transition hidden sm:inline-flex items-center space-x-1">
                    <span>Sign in</span>
                    <span class="text-xs">&rarr;</span>
                </a>
                <a href="/login" class="stripe-pill-button px-5 py-2.5 rounded-full text-xs font-extrabold text-white bg-stripe-navy hover:bg-stripe-indigo transition shadow-md flex items-center space-x-2">
                    <span>Admin Portal</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ========================================== -->
    <!-- 2. HERO SECTION (Stripe Iconic Aurora Canvas) -->
    <!-- ========================================== -->
    <section class="stripe-hero-canvas relative pt-12 pb-24 lg:pt-20 lg:pb-36 border-b border-stripe-border/70">
        <!-- Aurora Gradient Mesh -->
        <div class="stripe-gradient-mesh"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left: Headline & Pitch -->
                <div class="lg:col-span-6 space-y-8 text-left">
                    <!-- Feature Pill -->
                    <div class="inline-flex items-center space-x-2.5 bg-white/90 backdrop-blur-md border border-slate-200/90 rounded-full px-4 py-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-stripe-emerald animate-pulse"></span>
                        <span class="text-xs font-bold text-stripe-navy">Enterprise Player Intelligence v2.4</span>
                        <span class="text-slate-300">|</span>
                        <a href="#simulator" class="text-xs font-extrabold text-stripe-indigo hover:underline flex items-center space-x-1">
                            <span>Try Live Demo</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <!-- Main Bold H1 -->
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-stripe-navy tracking-tight leading-[1.06]">
                        Financial & player <br>
                        <span class="bg-gradient-to-r from-stripe-indigo via-stripe-purple to-stripe-pink bg-clip-text text-transparent">
                            intelligence for crypto gaming
                        </span>
                    </h1>

                    <!-- Paragraph -->
                    <p class="text-lg sm:text-xl text-stripe-slate font-medium leading-relaxed max-w-xl">
                        Identify VIP high-rollers instantly on deposit, detect liquid balances across 5 blockchains, and automate AML risk gates with a sub-180ms scoring API.
                    </p>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 pt-2">
                        <a href="#simulator" class="stripe-pill-button px-7 py-4 rounded-full text-sm font-extrabold text-white bg-stripe-indigo hover:bg-stripe-indigo-hover text-center shadow-lg shadow-stripe-indigo/30 transition flex items-center justify-center space-x-2">
                            <span>Test Live Wallet Simulator</span>
                            <span>&rarr;</span>
                        </a>
                        <a href="/docs" class="px-6 py-4 rounded-full text-sm font-bold text-stripe-navy bg-white hover:bg-slate-50 border border-stripe-border text-center transition shadow-sm flex items-center justify-center space-x-2">
                            <span>Read API Docs</span>
                        </a>
                    </div>

                    <!-- Stripe-Style Micro Metrics Bar -->
                    <div class="pt-8 border-t border-slate-200/80 grid grid-cols-3 gap-6">
                        <div>
                            <div class="text-2xl font-black text-stripe-navy font-mono">5 Chains</div>
                            <div class="text-xs font-semibold text-stripe-muted">TRON, ETH, BSC, SOL, BTC</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-stripe-indigo font-mono">&lt;180ms</div>
                            <div class="text-xs font-semibold text-stripe-muted">Scoring Latency</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-stripe-emerald font-mono">150k+</div>
                            <div class="text-xs font-semibold text-stripe-muted">Attributed Clusters</div>
                        </div>
                    </div>
                </div>

                <!-- Right: Stripe-Style 3D Floating Product Mockup -->
                <div class="lg:col-span-6 relative">
                    <!-- Glow behind card -->
                    <div class="absolute -inset-4 bg-gradient-to-r from-stripe-indigo/20 via-stripe-pink/20 to-stripe-amber/20 rounded-[2.5rem] blur-2xl opacity-70 animate-glow"></div>

                    <!-- Main Glass Dossier Card -->
                    <div class="stripe-glass-hero rounded-3xl p-6 sm:p-7 relative z-10 space-y-6">
                        
                        <!-- Card Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full bg-stripe-emerald animate-pulse"></div>
                                <span class="text-xs font-mono font-bold text-stripe-navy uppercase tracking-wider">
                                    LIVE TRANSACTION DOSSIER
                                </span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                100% On-Chain Match
                            </span>
                        </div>

                        <!-- Top Wallet Snapshot -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/90 rounded-2xl p-4 border border-slate-200/80">
                            <div>
                                <div class="text-[11px] font-bold uppercase text-stripe-muted tracking-wider">Target Wallet (TRON)</div>
                                <div class="font-mono text-xs font-extrabold text-stripe-navy select-all mt-0.5">
                                    TH7XRsbntBXxdLGx47b1xy2NgAhKfGUvYx
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2.5 py-1 bg-stripe-indigo/10 text-stripe-indigo rounded-lg text-[10px] font-mono font-extrabold">TRC-20</span>
                                <span class="px-2.5 py-1 bg-purple-100 text-purple-800 rounded-lg text-[10px] font-extrabold">SUPER SHARK</span>
                            </div>
                        </div>

                        <!-- Score & High Roller Metric Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                            <!-- Gauge -->
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-bold uppercase text-stripe-muted">Player Score</span>
                                <div class="flex items-baseline space-x-1 my-1">
                                    <span class="text-3xl font-black text-stripe-navy font-mono">98</span>
                                    <span class="text-xs font-bold text-slate-400">/100</span>
                                </div>
                                <span class="text-[10px] font-extrabold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md self-start">
                                    Top 0.1% VIP
                                </span>
                            </div>

                            <!-- Balance -->
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-bold uppercase text-stripe-muted">Liquid Balance</span>
                                <div class="text-2xl font-black text-stripe-emerald font-mono my-1">$452,180</div>
                                <span class="text-[10px] font-semibold text-slate-500">Visible on-chain</span>
                            </div>

                            <!-- 365d Gambling Flow -->
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between col-span-2 sm:col-span-1">
                                <span class="text-[10px] font-bold uppercase text-stripe-muted">Gambling Turnover</span>
                                <div class="text-2xl font-black text-stripe-indigo font-mono my-1">$1.48M</div>
                                <span class="text-[10px] font-semibold text-stripe-indigo">14 Casinos active</span>
                            </div>
                        </div>

                        <!-- Identified Clusters Chips -->
                        <div class="space-y-2">
                            <div class="text-[11px] font-bold uppercase text-stripe-muted tracking-wider flex items-center justify-between">
                                <span>Verified Entity Interactions</span>
                                <span class="text-[10px] text-stripe-indigo font-mono font-bold">Confidence: 99.8%</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-stripe-navy shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Stake.com ($420k Vol)</span>
                                </span>
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-stripe-navy shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    <span>Rollbit ($180k Vol)</span>
                                </span>
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-stripe-navy shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Binance VIP Desk</span>
                                </span>
                            </div>
                        </div>

                        <!-- Automated Decision Strip -->
                        <div class="p-3.5 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200 flex items-center justify-between">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-xl bg-stripe-emerald text-white flex items-center justify-center font-bold text-xs">✓</div>
                                <div>
                                    <div class="text-xs font-extrabold text-emerald-950">Decision: Auto-Approve & Fast Track</div>
                                    <div class="text-[10px] text-emerald-800">Trigger VIP Welcome Pack & Alert Telegram Host</div>
                                </div>
                            </div>
                            <span class="text-[10px] font-mono font-bold text-emerald-700 bg-white/80 px-2 py-1 rounded-lg">114ms</span>
                        </div>
                    </div>

                    <!-- Floating Badge 1 (Top-Right): AML Risk Low -->
                    <div class="hidden sm:flex absolute -top-6 -right-6 bg-white border border-slate-200/90 rounded-2xl p-3.5 shadow-xl items-center space-x-3 z-20 animate-float">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                            🛡️
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase text-slate-400">AML Risk Score</div>
                            <div class="text-xs font-extrabold text-stripe-navy">Clean 0 / 100 • 0% Mixer</div>
                        </div>
                    </div>

                    <!-- Floating Badge 2 (Bottom-Left): Webhook Dispatch -->
                    <div class="hidden sm:flex absolute -bottom-6 -left-6 bg-stripe-navy text-white border border-slate-700 rounded-2xl p-3.5 shadow-2xl items-center space-x-3 z-20 animate-float-rev">
                        <div class="w-9 h-9 rounded-xl bg-stripe-indigo text-white flex items-center justify-center font-bold text-xs">
                            ⚡
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase text-slate-400 font-mono">Webhook Dispatched</div>
                            <div class="text-xs font-mono font-bold text-stripe-cyan">event: "player.scored.vip"</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 3. BRAND & CLUSTER ATTRIBUTION TICKER     -->
    <!-- ========================================== -->
    <section class="py-10 bg-stripe-light border-b border-stripe-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <p class="text-xs font-extrabold uppercase tracking-widest text-slate-400">
                PROVEN CLUSTER ATTRIBUTION & TRANSACTION PARSING ACROSS ECOSYSTEMS
            </p>
            <div class="flex flex-wrap items-center justify-center gap-8 sm:gap-14 opacity-70 hover:opacity-100 transition-opacity">
                <!-- Blockchain Badges -->
                <div class="flex items-center space-x-2 text-sm font-extrabold text-stripe-navy font-mono">
                    <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                    <span>TRON (TRC20)</span>
                </div>
                <div class="flex items-center space-x-2 text-sm font-extrabold text-stripe-navy font-mono">
                    <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                    <span>Ethereum (ERC20)</span>
                </div>
                <div class="flex items-center space-x-2 text-sm font-extrabold text-stripe-navy font-mono">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <span>BNB Chain (BEP20)</span>
                </div>
                <div class="flex items-center space-x-2 text-sm font-extrabold text-stripe-navy font-mono">
                    <span class="w-3 h-3 rounded-full bg-teal-500"></span>
                    <span>Solana (SPL)</span>
                </div>
                <div class="flex items-center space-x-2 text-sm font-extrabold text-stripe-navy font-mono">
                    <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                    <span>Bitcoin (BTC)</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 4. PRODUCT BENTO GRID (Stripe Style)       -->
    <!-- ========================================== -->
    <section id="features" class="py-24 lg:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            
            <!-- Section Header -->
            <div class="max-w-3xl space-y-4">
                <span class="text-xs font-extrabold uppercase tracking-widest text-stripe-indigo">
                    MODULAR INTELLIGENCE ENGINE
                </span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-stripe-navy tracking-tight leading-tight">
                    Everything you need to evaluate, retain, and protect crypto players.
                </h2>
                <p class="text-base sm:text-lg text-stripe-slate">
                    Designed for high-load iGaming platforms, payment gateways, and Web3 sportsbooks handling thousands of real-time crypto deposits.
                </p>
            </div>

            <!-- Bento Matrix Grid -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                
                <!-- Tile 1: High Roller & VIP Discovery (Wide: 7 cols) -->
                <div class="md:col-span-7 stripe-card p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden group">
                    <div class="space-y-4 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center font-black text-xl">
                            🐋
                        </div>
                        <h3 class="text-2xl font-black text-stripe-navy">
                            Instant VIP Shark & Whale Detection
                        </h3>
                        <p class="text-sm text-stripe-slate leading-relaxed max-w-lg">
                            Evaluate total liquid wealth across multiple blockchains on the player's very first deposit. Uncover off-site liquidity in other wallets and trigger immediate VIP onboarding.
                        </p>
                    </div>

                    <!-- Visual Mockup inside tile -->
                    <div class="mt-8 bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-3">
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500 font-bold">Liquid Balance Multi-Chain:</span>
                            <span class="text-purple-700 font-extrabold">$480,000 USD (ETH + TRON)</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden flex">
                            <div class="bg-indigo-600 h-3" style="width: 55%"></div>
                            <div class="bg-rose-500 h-3" style="width: 35%"></div>
                            <div class="bg-amber-500 h-3" style="width: 10%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                            <span>ERC20 ($264k)</span>
                            <span>TRC20 ($168k)</span>
                            <span>BEP20 ($48k)</span>
                        </div>
                    </div>
                </div>

                <!-- Tile 2: 150k+ Clustered Entities (5 cols) -->
                <div class="md:col-span-5 stripe-card p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-200 text-stripe-indigo flex items-center justify-center font-black text-xl">
                            🎰
                        </div>
                        <h3 class="text-2xl font-black text-stripe-navy">
                            150k+ Casino & CEX Clusters
                        </h3>
                        <p class="text-sm text-stripe-slate leading-relaxed">
                            Continuous on-chain graph analysis identifying interactions with Stake.com, Rollbit, BC.Game, Roobet, 1xBet, Binance, and OKX.
                        </p>
                    </div>

                    <!-- Flow Chip Mockup -->
                    <div class="mt-6 space-y-2 font-mono text-xs">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                            <span class="text-slate-700 font-bold">Stake.com Deposit Router</span>
                            <span class="text-emerald-600 font-extrabold">Attributed</span>
                        </div>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                            <span class="text-slate-700 font-bold">Rollbit Casino Vault</span>
                            <span class="text-emerald-600 font-extrabold">Attributed</span>
                        </div>
                    </div>
                </div>

                <!-- Tile 3: Automated Risk & AML Policy Engine (5 cols) -->
                <div class="md:col-span-5 stripe-card p-8 sm:p-10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center font-black text-xl">
                            🛡️
                        </div>
                        <h3 class="text-2xl font-black text-stripe-navy">
                            AML Risk & Policy Rules
                        </h3>
                        <p class="text-sm text-stripe-slate leading-relaxed">
                            Detect sanctioned entities, Tornado Cash, darknet market hops, and multi-accounting bonus hunters in milliseconds before granting withdrawals.
                        </p>
                    </div>

                    <div class="mt-6 bg-slate-900 text-white rounded-2xl p-4 font-mono text-xs space-y-1.5">
                        <div class="text-slate-400">// Rule Execution Engine</div>
                        <div>IF <span class="text-yellow-300">mixer_exposure &gt; 0</span> &rarr; <span class="text-rose-400">FLAG_HIGH_RISK</span></div>
                        <div>IF <span class="text-yellow-300">turnover &gt; $100k</span> &rarr; <span class="text-emerald-400">SET_SUPER_VIP</span></div>
                    </div>
                </div>

                <!-- Tile 4: Unified Multi-Chain DTO (7 cols) -->
                <div class="md:col-span-7 stripe-card p-8 sm:p-10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 border border-teal-200 text-teal-600 flex items-center justify-center font-black text-xl">
                            ⚡
                        </div>
                        <h3 class="text-2xl font-black text-stripe-navy">
                            Unified Scoring JSON Schema
                        </h3>
                        <p class="text-sm text-stripe-slate leading-relaxed max-w-lg">
                            One normalized API response format regardless of whether the player deposits in TRON TRC-20, Ethereum ERC-20, Solana SPL, or Bitcoin Taproot.
                        </p>
                    </div>

                    <div class="mt-6 bg-slate-50 border border-slate-200 rounded-2xl p-4 font-mono text-xs text-slate-700 flex flex-wrap gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 shadow-sm font-bold text-stripe-navy">score.value</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 shadow-sm font-bold text-stripe-navy">score.segment</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 shadow-sm font-bold text-stripe-navy">financial_profile.visible_balance_usd</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 shadow-sm font-bold text-stripe-navy">gambling_intelligence.entities_count</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 5. INTERACTIVE LIVE WALLET SIMULATOR       -->
    <!-- ========================================== -->
    <section id="simulator" class="py-24 bg-stripe-light border-y border-stripe-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="text-xs font-extrabold uppercase tracking-widest text-stripe-indigo">
                    TEST IT IN REAL TIME
                </span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-stripe-navy tracking-tight">
                    Live Crypto Scoring Simulator
                </h2>
                <p class="text-base text-stripe-slate">
                    Pick one of the live test profiles below or paste any address to see real-time player scoring and dossier generation.
                </p>
            </div>

            <!-- Simulator UI Component -->
            <div class="bg-white rounded-3xl border border-stripe-border shadow-xl p-6 sm:p-10 space-y-8 max-w-5xl mx-auto">
                
                <!-- Preset Pickers -->
                <div class="space-y-3">
                    <label class="block text-xs font-extrabold uppercase text-stripe-muted tracking-wider">
                        1. Select a Preset Wallet Profile:
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button type="button" onclick="selectPreset('vip')" id="btnPreset-vip" class="p-3.5 rounded-2xl border-2 border-stripe-indigo bg-stripe-indigo/5 text-left transition hover:scale-[1.02]">
                            <div class="text-xs font-bold text-stripe-indigo font-mono">ETH (ERC20)</div>
                            <div class="text-sm font-black text-stripe-navy mt-1">🐋 VIP Whale</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">$480k+ Liquid</div>
                        </button>

                        <button type="button" onclick="selectPreset('casino')" id="btnPreset-casino" class="p-3.5 rounded-2xl border border-slate-200 bg-white text-left transition hover:border-stripe-indigo hover:scale-[1.02]">
                            <div class="text-xs font-bold text-rose-600 font-mono">TRON (TRC20)</div>
                            <div class="text-sm font-black text-stripe-navy mt-1">🎰 Stake Player</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">$180k Turnover</div>
                        </button>

                        <button type="button" onclick="selectPreset('hunter')" id="btnPreset-hunter" class="p-3.5 rounded-2xl border border-slate-200 bg-white text-left transition hover:border-stripe-indigo hover:scale-[1.02]">
                            <div class="text-xs font-bold text-amber-600 font-mono">BSC (BEP20)</div>
                            <div class="text-sm font-black text-stripe-navy mt-1">🎯 Bonus Hunter</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Rapid Micro Sweeps</div>
                        </button>

                        <button type="button" onclick="selectPreset('aml')" id="btnPreset-aml" class="p-3.5 rounded-2xl border border-slate-200 bg-white text-left transition hover:border-stripe-indigo hover:scale-[1.02]">
                            <div class="text-xs font-bold text-slate-700 font-mono">ETH (Mixer)</div>
                            <div class="text-sm font-black text-stripe-navy mt-1">🚨 Sanctioned / AML</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Tornado Direct Hop</div>
                        </button>
                    </div>
                </div>

                <!-- Input Form Bar -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold uppercase text-stripe-muted mb-1">Network</label>
                        <select id="simNetwork" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold text-stripe-navy focus:border-stripe-indigo focus:bg-white focus:outline-none">
                            <option value="ethereum">Ethereum (ETH)</option>
                            <option value="tron">TRON (TRC20)</option>
                            <option value="bsc">BNB Chain (BSC)</option>
                            <option value="solana">Solana (SOL)</option>
                            <option value="bitcoin">Bitcoin (BTC)</option>
                        </select>
                    </div>

                    <div class="md:col-span-6">
                        <label class="block text-xs font-bold uppercase text-stripe-muted mb-1">Wallet Address</label>
                        <input type="text" id="simAddress" value="0x28c6c06298d514db089934071355e5743bf21d60" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-xs sm:text-sm font-mono font-bold text-stripe-navy focus:border-stripe-indigo focus:bg-white focus:outline-none">
                    </div>

                    <div class="md:col-span-3 flex items-end">
                        <button type="button" onclick="runSimulation()" id="simRunBtn" class="stripe-pill-button w-full bg-stripe-navy hover:bg-stripe-indigo text-white font-extrabold text-sm py-3.5 px-4 rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                            <span>Score Wallet</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- Simulation Output Panel -->
                <div id="simResultBox" class="border border-slate-200 rounded-2xl p-6 bg-slate-50 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-200">
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 rounded-full bg-stripe-emerald animate-pulse"></span>
                            <span class="text-xs font-mono font-bold text-stripe-navy uppercase">ANALYSIS RESULT #01m22vd...</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span id="simTagBadge" class="px-3 py-1 bg-purple-100 text-purple-800 border border-purple-300 rounded-full text-xs font-black">
                                SUPER_VIP_SHARK
                            </span>
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">
                                142ms
                            </span>
                        </div>
                    </div>

                    <!-- Metrics -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Risk / VIP Score</div>
                            <div id="simScoreVal" class="text-2xl sm:text-3xl font-black text-stripe-navy font-mono mt-1">98 / 100</div>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Visible Balance</div>
                            <div id="simBalanceVal" class="text-2xl sm:text-3xl font-black text-stripe-emerald font-mono mt-1">$482,500</div>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Gambling Turnover</div>
                            <div id="simFlowVal" class="text-2xl sm:text-3xl font-black text-stripe-indigo font-mono mt-1">$1.42M</div>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Decision Action</div>
                            <div id="simDecisionVal" class="text-xs font-black text-emerald-700 font-mono mt-2 bg-emerald-50 px-2 py-1 rounded inline-block">
                                AUTO_APPROVE
                            </div>
                        </div>
                    </div>

                    <!-- Raw JSON preview toggle -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-stripe-muted uppercase mb-2">
                            <span>Structured JSON Response</span>
                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('simJsonCode').innerText); alert('JSON copied to clipboard!');" class="text-stripe-indigo hover:underline font-bold">
                                Copy JSON
                            </button>
                        </div>
                        <pre id="simJsonCode" class="bg-slate-900 text-slate-200 rounded-xl p-4 font-mono text-xs overflow-x-auto max-h-64 shadow-inner">{
  "analysis_id": "01m22vdmk7j2c1jg9kgekbf3zd",
  "status": "completed",
  "network": "ethereum",
  "address": "0x28c6c06298d514db089934071355e5743bf21d60",
  "score": {
    "value": 98,
    "segment": "super_vip",
    "custom_tags": ["SUPER_SHARK", "HIGH_LIQUIDITY_WHALE"],
    "recommendation": "auto_approve"
  },
  "financial_profile": {
    "visible_balance_usd": 482500,
    "primary_assets": {"ETH": 142.5, "USDT": 150000}
  },
  "gambling_intelligence": {
    "is_gambler": true,
    "entities_count": 8,
    "total_flow_365d_usd": 1420000
  }
}</pre>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 6. DEVELOPER-FIRST SECTION (Dark Terminal)  -->
    <!-- ========================================== -->
    <section id="developers" class="py-24 bg-stripe-darkbg text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            
            <div class="max-w-3xl space-y-4">
                <span class="text-xs font-extrabold uppercase tracking-widest text-stripe-cyan">
                    ENGINEERED FOR DEVELOPERS
                </span>
                <h2 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Integrate in minutes with standard REST APIs and client SDKs.
                </h2>
                <p class="text-base sm:text-lg text-slate-400">
                    Authenticate via Bearer API keys or standard HMAC headers. Fully compatible with Postman, OpenAPI 3.0, and automated CI/CD webhooks.
                </p>
            </div>

            <!-- Interactive Code Terminal -->
            <div class="bg-[#0b1727] border border-slate-800 rounded-3xl shadow-2xl overflow-hidden">
                <!-- Tab Headers -->
                <div class="flex items-center justify-between px-6 py-4 bg-slate-900/90 border-b border-slate-800">
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="switchCodeTab('curl')" id="tab-curl" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-stripe-indigo text-white transition">
                            cURL
                        </button>
                        <button type="button" onclick="switchCodeTab('python')" id="tab-python" class="px-4 py-2 rounded-xl text-xs font-extrabold text-slate-400 hover:text-white transition">
                            Python
                        </button>
                        <button type="button" onclick="switchCodeTab('nodejs')" id="tab-nodejs" class="px-4 py-2 rounded-xl text-xs font-extrabold text-slate-400 hover:text-white transition">
                            Node.js
                        </button>
                        <button type="button" onclick="switchCodeTab('php')" id="tab-php" class="px-4 py-2 rounded-xl text-xs font-extrabold text-slate-400 hover:text-white transition">
                            PHP / Laravel
                        </button>
                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="/openapi.yaml" target="_blank" class="text-xs font-mono font-bold text-slate-400 hover:text-white transition hidden sm:inline">
                            openapi.yaml
                        </a>
                        <a href="/docs" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-bold text-white transition">
                            Interactive Swagger Docs &rarr;
                        </a>
                    </div>
                </div>

                <!-- Code View -->
                <div class="p-6 sm:p-8 font-mono text-xs overflow-x-auto text-slate-200">
                    <!-- cURL -->
                    <div id="codeSnippet-curl" class="space-y-1">
                        <span class="text-stripe-cyan font-bold">curl</span> -X POST https://on-score.io/api/v1/score/wallet \<br>
                        &nbsp;&nbsp;-H <span class="text-stripe-amber">"Authorization: Bearer ons_live_your_api_key_here"</span> \<br>
                        &nbsp;&nbsp;-H <span class="text-stripe-emerald">"Content-Type: application/json"</span> \<br>
                        &nbsp;&nbsp;-d '{<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-key">"chain"</span>: <span class="syn-str">"ETH"</span>,<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-key">"address"</span>: <span class="syn-str">"0x28c6c06298d514db089934071355e5743bf21d60"</span>,<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-key">"external_player_id"</span>: <span class="syn-str">"usr_casino_9021"</span><br>
                        &nbsp;&nbsp;}'
                    </div>

                    <!-- Python -->
                    <div id="codeSnippet-python" class="space-y-1 hidden">
                        <span class="text-stripe-cyan font-bold">import</span> requests<br><br>
                        url = <span class="syn-str">"https://on-score.io/api/v1/score/wallet"</span><br>
                        headers = {<br>
                        &nbsp;&nbsp;<span class="syn-str">"Authorization"</span>: <span class="syn-str">"Bearer ons_live_your_api_key_here"</span>,<br>
                        &nbsp;&nbsp;<span class="syn-str">"Content-Type"</span>: <span class="syn-str">"application/json"</span><br>
                        }<br>
                        payload = {<br>
                        &nbsp;&nbsp;<span class="syn-key">"chain"</span>: <span class="syn-str">"ETH"</span>,<br>
                        &nbsp;&nbsp;<span class="syn-key">"address"</span>: <span class="syn-str">"0x28c6c06298d514db089934071355e5743bf21d60"</span><br>
                        }<br><br>
                        response = requests.post(url, json=payload, headers=headers)<br>
                        dossier = response.json()<br>
                        print(f<span class="syn-str">"Player Scored: {dossier['score']['value']}/100. Segment: {dossier['score']['segment']}"</span>)
                    </div>

                    <!-- Node.js -->
                    <div id="codeSnippet-nodejs" class="space-y-1 hidden">
                        <span class="text-stripe-cyan font-bold">import</span> axios <span class="text-stripe-cyan font-bold">from</span> <span class="syn-str">'axios'</span>;<br><br>
                        <span class="text-stripe-purple font-bold">async function</span> scoreDeposit(address, chain = <span class="syn-str">'ETH'</span>) {<br>
                        &nbsp;&nbsp;<span class="text-stripe-purple font-bold">const</span> { data } = <span class="text-stripe-purple font-bold">await</span> axios.post(<span class="syn-str">'https://on-score.io/api/v1/score/wallet'</span>, {<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;chain,<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;address,<br>
                        &nbsp;&nbsp;}, {<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;headers: { <span class="syn-str">'Authorization'</span>: <span class="syn-str">`Bearer ${process.env.ONSCORE_API_KEY}`</span> }<br>
                        &nbsp;&nbsp;});<br><br>
                        &nbsp;&nbsp;<span class="text-stripe-purple font-bold">if</span> (data.score.segment === <span class="syn-str">'super_vip'</span>) {<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;console.log(<span class="syn-str">`[VIP ALERT] Whale detected: $${data.financial_profile.visible_balance_usd}`</span>);<br>
                        &nbsp;&nbsp;}<br>
                        &nbsp;&nbsp;<span class="text-stripe-purple font-bold">return</span> data;<br>
                        }
                    </div>

                    <!-- PHP -->
                    <div id="codeSnippet-php" class="space-y-1 hidden">
                        &lt;?php<br><br>
                        <span class="text-stripe-cyan font-bold">use</span> Illuminate\Support\Facades\Http;<br><br>
                        $response = Http::withToken(config(<span class="syn-str">'services.onscore.key'</span>))<br>
                        &nbsp;&nbsp;-&gt;post(<span class="syn-str">'https://on-score.io/api/v1/score/wallet'</span>, [<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-str">'chain'</span> =&gt; <span class="syn-str">'ETH'</span>,<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="syn-str">'address'</span> =&gt; <span class="syn-str">'0x28c6c06298d514db089934071355e5743bf21d60'</span>,<br>
                        &nbsp;&nbsp;]);<br><br>
                        $dossier = $response-&gt;json();
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 7. STRIPE GRADIENT CTA & FOOTER            -->
    <!-- ========================================== -->
    <section class="relative py-24 bg-white border-t border-stripe-border overflow-hidden">
        <!-- Accent Gradient Blob -->
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-tr from-stripe-indigo/30 to-stripe-pink/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-8">
            <h2 class="text-4xl sm:text-5xl font-black text-stripe-navy tracking-tight max-w-3xl mx-auto">
                Ready to transform your crypto player scoring?
            </h2>
            <p class="text-base sm:text-lg text-stripe-slate max-w-xl mx-auto">
                Start evaluating live wallet deposits, VIP whales, and AML clusters with instant enterprise credentials.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="/login" class="stripe-pill-button px-8 py-4 rounded-full text-sm font-extrabold text-white bg-stripe-indigo hover:bg-stripe-indigo-hover shadow-xl shadow-stripe-indigo/30 transition">
                    Access Portal & API Keys &rarr;
                </a>
                <a href="/docs" class="px-7 py-4 rounded-full text-sm font-bold text-stripe-navy bg-white hover:bg-slate-50 border border-stripe-border shadow-sm transition">
                    Explore API Reference
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-stripe-light border-t border-stripe-border py-12 text-xs text-stripe-slate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-3 group">
                    <div class="w-8 h-8 rounded-xl bg-white border border-slate-200/90 p-1 shadow-sm flex items-center justify-center shrink-0">
                        <svg viewBox="0 0 100 100" class="w-full h-full" fill="none">
                            <defs>
                                <linearGradient id="ftOrbitGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#00d4ff" />
                                    <stop offset="50%" stop-color="#635bff" />
                                    <stop offset="100%" stop-color="#ff6080" />
                                </linearGradient>
                            </defs>
                            <circle cx="50" cy="50" r="32" stroke="url(#ftOrbitGrad)" stroke-width="7.5" stroke-linecap="round" />
                            <ellipse cx="50" cy="50" rx="44" ry="18" stroke="url(#ftOrbitGrad)" stroke-width="6" transform="rotate(-30 50 50)" stroke-linecap="round" />
                            <path d="M 24 50 L 38 50 L 44 65 L 56 32 L 63 56 L 68 50 L 76 50" stroke="url(#ftOrbitGrad)" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <span class="text-base font-black text-stripe-navy font-sans tracking-tight">on-score<span class="text-stripe-indigo">.io</span></span>
                </div>
                <div class="flex items-center space-x-6 font-bold text-stripe-navy">
                    <a href="/docs" class="hover:text-stripe-indigo transition">API Reference</a>
                    <a href="/openapi.yaml" class="hover:text-stripe-indigo transition">OpenAPI 3.0</a>
                    <a href="/login" class="hover:text-stripe-indigo transition">Admin Portal</a>
                </div>
            </div>
            <div class="border-t border-slate-200/80 pt-6 flex flex-col sm:flex-row items-center justify-between text-slate-400 gap-2">
                <div>
                    &copy; {{ date('Y') }} on-score.io. High-Performance On-Chain Cryptocurrency Intelligence & Player Scoring Gateway.
                </div>
                <div class="flex space-x-4 font-mono text-[11px]">
                    <span>WPS-v2.4 Engine</span>
                    <span>•</span>
                    <span>Multi-Chain Aggregator</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Simulator & Code Switcher Scripts -->
    <script>
        const PRESETS = {
            vip: {
                network: 'ethereum',
                address: '0x28c6c06298d514db089934071355e5743bf21d60',
                tag: 'SUPER_VIP_SHARK',
                score: '98 / 100',
                balance: '$482,500',
                flow: '$1.42M',
                decision: 'AUTO_APPROVE',
                json: {
                    analysis_id: "01m22vdmk7j2c1jg9kgekbf3zd",
                    status: "completed",
                    network: "ethereum",
                    address: "0x28c6c06298d514db089934071355e5743bf21d60",
                    score: {
                        value: 98,
                        segment: "super_vip",
                        custom_tags: ["SUPER_SHARK", "HIGH_LIQUIDITY_WHALE"],
                        recommendation: "auto_approve"
                    },
                    financial_profile: {
                        visible_balance_usd: 482500,
                        primary_assets: { "ETH": 142.5, "USDT": 150000 }
                    },
                    gambling_intelligence: {
                        is_gambler: true,
                        entities_count: 8,
                        total_flow_365d_usd: 1420000
                    }
                }
            },
            casino: {
                network: 'tron',
                address: 'TH7XRsbntBXxdLGx47b1xy2NgAhKfGUvYx',
                tag: 'POTENTIAL_VIP',
                score: '84 / 100',
                balance: '$65,200',
                flow: '$240,000',
                decision: 'AUTO_APPROVE',
                json: {
                    analysis_id: "01m22vdmk7j2c1jg9kgekbf4ab",
                    status: "completed",
                    network: "tron",
                    address: "TH7XRsbntBXxdLGx47b1xy2NgAhKfGUvYx",
                    score: {
                        value: 84,
                        segment: "potential_vip",
                        custom_tags: ["STAKE_HIGH_ROLLER", "ACTIVE_GAMBLER"],
                        recommendation: "auto_approve"
                    },
                    financial_profile: {
                        visible_balance_usd: 65200,
                        primary_assets: { "USDT_TRC20": 65000, "TRX": 2500 }
                    },
                    gambling_intelligence: {
                        is_gambler: true,
                        entities_count: 4,
                        total_flow_365d_usd: 240000
                    }
                }
            },
            hunter: {
                network: 'bsc',
                address: '0x8894e0a0c962cb723c1976a4421c95949be2d4e3',
                tag: 'BONUS_HUNTER',
                score: '35 / 100',
                balance: '$1,250',
                flow: '$18,400',
                decision: 'STEP_UP_VERIFICATION',
                json: {
                    analysis_id: "01m22vdmk7j2c1jg9kgekbf5cd",
                    status: "completed",
                    network: "bsc",
                    address: "0x8894e0a0c962cb723c1976a4421c95949be2d4e3",
                    score: {
                        value: 35,
                        segment: "bonus_hunter",
                        custom_tags: ["RAPID_MICRO_SWEEPS", "CLUSTER_ASSOCIATED"],
                        recommendation: "step_up_verification"
                    },
                    financial_profile: {
                        visible_balance_usd: 1250,
                        primary_assets: { "BNB": 1.2, "USDT_BEP20": 650 }
                    },
                    gambling_intelligence: {
                        is_gambler: true,
                        entities_count: 11,
                        total_flow_365d_usd: 18400
                    }
                }
            },
            aml: {
                network: 'ethereum',
                address: '0x0d0707963952f2fba59dd06f2b425ace40b492fe',
                tag: 'HIGH_RISK_AML',
                score: '12 / 100',
                balance: '$840',
                flow: '$85,000',
                decision: 'BLOCK_AND_REVIEW',
                json: {
                    analysis_id: "01m22vdmk7j2c1jg9kgekbf6ef",
                    status: "completed",
                    network: "ethereum",
                    address: "0x0d0707963952f2fba59dd06f2b425ace40b492fe",
                    score: {
                        value: 12,
                        segment: "high_risk_aml",
                        custom_tags: ["TORNADO_DIRECT_HOP", "SANCTION_FLAG"],
                        recommendation: "block_and_review"
                    },
                    financial_profile: {
                        visible_balance_usd: 840,
                        primary_assets: { "ETH": 0.3 }
                    },
                    gambling_intelligence: {
                        is_gambler: false,
                        entities_count: 0,
                        total_flow_365d_usd: 0
                    }
                }
            }
        };

        function selectPreset(type) {
            const data = PRESETS[type];
            if (!data) return;

            // Highlight button
            ['vip', 'casino', 'hunter', 'aml'].forEach(t => {
                const btn = document.getElementById('btnPreset-' + t);
                if (t === type) {
                    btn.className = "p-3.5 rounded-2xl border-2 border-stripe-indigo bg-stripe-indigo/5 text-left transition hover:scale-[1.02]";
                } else {
                    btn.className = "p-3.5 rounded-2xl border border-slate-200 bg-white text-left transition hover:border-stripe-indigo hover:scale-[1.02]";
                }
            });

            document.getElementById('simNetwork').value = data.network;
            document.getElementById('simAddress').value = data.address;
            
            runSimulation(data);
        }

        function runSimulation(presetData = null) {
            const net = document.getElementById('simNetwork').value;
            const addr = document.getElementById('simAddress').value;
            const btn = document.getElementById('simRunBtn');

            btn.innerHTML = '<span>Scanning On-Chain...</span>';
            btn.classList.add('opacity-75');

            setTimeout(() => {
                btn.innerHTML = '<span>Score Wallet</span><span>&rarr;</span>';
                btn.classList.remove('opacity-75');

                const data = presetData || PRESETS.vip;
                document.getElementById('simTagBadge').innerText = data.tag;
                document.getElementById('simScoreVal').innerText = data.score;
                document.getElementById('simBalanceVal').innerText = data.balance;
                document.getElementById('simFlowVal').innerText = data.flow;
                document.getElementById('simDecisionVal').innerText = data.decision;
                document.getElementById('simJsonCode').innerText = JSON.stringify(data.json, null, 2);
            }, 300);
        }

        function switchCodeTab(tab) {
            ['curl', 'python', 'nodejs', 'php'].forEach(t => {
                const snippet = document.getElementById('codeSnippet-' + t);
                const tabBtn = document.getElementById('tab-' + t);
                if (t === tab) {
                    snippet.classList.remove('hidden');
                    tabBtn.className = "px-4 py-2 rounded-xl text-xs font-extrabold bg-stripe-indigo text-white transition";
                } else {
                    snippet.classList.add('hidden');
                    tabBtn.className = "px-4 py-2 rounded-xl text-xs font-extrabold text-slate-400 hover:text-white transition";
                }
            });
        }
    </script>
</body>
</html>