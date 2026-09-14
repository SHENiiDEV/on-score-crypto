<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>on-score.io — B2B Intelligence & Player Scoring API Reference</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css" />
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom Light Theme styling for Swagger UI */
        .swagger-ui .topbar { display: none !important; }
        .swagger-ui .info { margin: 20px 0 !important; }
        .swagger-ui .info .title { font-size: 28px; font-weight: 800; color: #0f172a; }
        .swagger-ui .scheme-container { background: #ffffff; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1); border-radius: 16px; padding: 20px; margin-bottom: 24px; border: 1px solid #e2e8f0; }
        .swagger-ui .opblock { border-radius: 16px !important; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important; margin-bottom: 16px !important; border: 1px solid #e2e8f0 !important; }
        .swagger-ui .opblock .opblock-summary { padding: 12px 16px !important; }
        .swagger-ui .opblock.opblock-post { background: rgba(79, 70, 229, 0.03) !important; border-color: #c7d2fe !important; }
        .swagger-ui .opblock.opblock-post .opblock-summary-method { background: #635bff !important; border-radius: 8px; font-weight: 800; }
        .swagger-ui .opblock.opblock-get { background: rgba(16, 185, 129, 0.03) !important; border-color: #a7f3d0 !important; }
        .swagger-ui .opblock.opblock-get .opblock-summary-method { background: #10b981 !important; border-radius: 8px; font-weight: 800; }
        .swagger-ui .btn.authorize { background: #635bff; color: white; border-radius: 12px; border: none; font-weight: 700; padding: 8px 20px; }
        .swagger-ui .btn.authorize svg { fill: white; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 flex flex-col">

    <!-- Top Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 p-1 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg viewBox="0 0 100 100" class="w-full h-full" fill="none">
                            <defs>
                                <linearGradient id="docOrbitGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#00d4ff" />
                                    <stop offset="35%" stop-color="#635bff" />
                                    <stop offset="70%" stop-color="#7a73ff" />
                                    <stop offset="100%" stop-color="#ff6080" />
                                </linearGradient>
                                <linearGradient id="docPulseGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#00d4ff" />
                                    <stop offset="50%" stop-color="#635bff" />
                                    <stop offset="100%" stop-color="#ff6080" />
                                </linearGradient>
                            </defs>
                            <circle cx="50" cy="50" r="32" stroke="url(#docOrbitGrad)" stroke-width="7.5" stroke-linecap="round" />
                            <ellipse cx="50" cy="50" rx="44" ry="18" stroke="url(#docOrbitGrad)" stroke-width="6" transform="rotate(-30 50 50)" stroke-linecap="round" />
                            <path d="M 24 50 L 38 50 L 44 65 L 56 32 L 63 56 L 68 50 L 76 50" stroke="url(#docPulseGrad)" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <span class="text-xl font-black tracking-tight text-slate-900">On-Score<span class="text-[#635bff]">.io</span></span>
                </a>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                    API Docs v1.0
                </span>
            </div>

            <div class="flex items-center space-x-3">
                <a href="/OnScore_API_Postman_Collection.json" download class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 rounded-xl text-xs font-bold transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Download Postman Collection</span>
                </a>
                <a href="/admin" class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-extrabold transition shadow-sm">
                    Admin Portal &rarr;
                </a>
            </div>
        </div>
    </header>

    <!-- Quick Snippets Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white py-6 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-indigo-400 mb-1">1. Base Endpoint</div>
                    <div class="font-mono text-xs bg-slate-800/80 px-3 py-2 rounded-xl border border-slate-700 select-all">
                        POST https://on-score.io/api/v1/analyses
                    </div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-1">2. Required Headers</div>
                    <div class="font-mono text-[11px] bg-slate-800/80 px-3 py-2 rounded-xl border border-slate-700">
                        X-API-Key: ons_live_...<br>
                        X-API-Secret: ons_sec_...
                    </div>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-1">3. Supported Blockchains</div>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="px-2 py-0.5 bg-slate-800 rounded-md text-[10px] font-mono font-bold text-slate-200">TRON (TRC20)</span>
                        <span class="px-2 py-0.5 bg-slate-800 rounded-md text-[10px] font-mono font-bold text-slate-200">Bitcoin (BTC)</span>
                        <span class="px-2 py-0.5 bg-slate-800 rounded-md text-[10px] font-mono font-bold text-slate-200">Ethereum (ERC20)</span>
                        <span class="px-2 py-0.5 bg-slate-800 rounded-md text-[10px] font-mono font-bold text-slate-200">BNB Chain (BEP20)</span>
                        <span class="px-2 py-0.5 bg-slate-800 rounded-md text-[10px] font-mono font-bold text-slate-200">Solana (SPL)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Behavioral Intelligence Feature Callout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-[#635bff] border border-indigo-200 flex items-center justify-center font-bold text-base">🎯</span>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">New: Behavioral Pattern Engine &amp; Casino Footprint Payload</h3>
                        <p class="text-xs text-slate-500">Every analysis response now returns structured behavioral heuristics and gambling telemetry.</p>
                    </div>
                </div>
                <span class="chip bg-emerald-50 text-emerald-700 border-emerald-200 font-mono text-[10px] font-bold">API Payload v1.2</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                    <span class="font-bold text-slate-900 block font-mono">round_deposits</span>
                    <p class="text-[11px] text-slate-500 mt-1">Percentage of round bet amounts ($50, $100, $500) and Shannon bet sizing entropy.</p>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                    <span class="font-bold text-slate-900 block font-mono">martingale_chasing</span>
                    <p class="text-[11px] text-slate-500 mt-1">Detection of escalating loss-chasing doubling sequences (&lt;3h) with tilt risk level.</p>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                    <span class="font-bold text-slate-900 block font-mono">session_activity</span>
                    <p class="text-[11px] text-slate-500 mt-1">24-hour UTC distribution array (0..23h), night activity index (22:00-06:00), and weekend share.</p>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                    <span class="font-bold text-slate-900 block font-mono">unlabeled_casino_heuristics</span>
                    <p class="text-[11px] text-slate-500 mt-1">Heuristic identification of ephemeral casino deposit sweepers &amp; forwarding proxies.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Swagger UI Interactive Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div id="swagger-ui"></div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500 font-medium">
        on-score.io &copy; {{ date('Y') }}. High-Agency On-Chain Cryptocurrency Intelligence & Player Scoring. All rights reserved.
    </footer>

    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: '/openapi.yaml',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                docExpansion: "list",
            });
        };
    </script>
</body>
</html>
