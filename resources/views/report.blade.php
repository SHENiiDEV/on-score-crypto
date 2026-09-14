@extends('console.layout')

@section('title', 'Player Intelligence Profile — ' . $analysis->address)

@section('content')
@php
    $score = $snapshot->score_breakdown ?? [];
    $scoreVal = $score['value'] ?? $analysis->score_value ?? 0;
    $segment = $score['segment'] ?? $analysis->segment ?? 'low_value';
    $subscores = $score['subscores'] ?? [];
    $keyDrivers = $score['key_drivers'] ?? [];
    $gambling = $snapshot->gambling_intelligence ?? [];
    $balance = $snapshot->balance_assets ?? [];
    $turnover = $snapshot->turnover ?? [];
    $counterparties = $snapshot->counterparties ?? [];
    $isCex = $snapshot->wallet_overview['is_custodial_cex'] ?? false;
    $warnings = $snapshot->warnings ?? [];
    $behavioral = $snapshot->behavioral_patterns ?? [];
    $round = $behavioral['round_deposits'] ?? [];
    $martingale = $behavioral['martingale_chasing'] ?? [];
    $session = $behavioral['session_activity'] ?? [];
    $velocity = $behavioral['velocity_cycles'] ?? [];
    $unlabeled = $behavioral['unlabeled_casino_heuristics'] ?? [];
    $bTags = $behavioral['behavioral_tags'] ?? [];
    $whaleScore = $behavioral['whale_potential_score'] ?? null;
    $hourly = $session['hourly_distribution'] ?? array_fill(0, 24, 0);
    $maxHourly = max(1, max($hourly));
@endphp

<div class="space-y-6 pb-12">
    
    <!-- Header Navigation & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 mb-1">
                @php 
                    $backUrl = (auth()->check() && !auth()->user()->isAdmin()) ? route('merchant.usage') : route('admin.analyses'); 
                    $backLabel = (auth()->check() && !auth()->user()->isAdmin()) ? 'Merchant Usage' : 'Analyses';
                @endphp
                <a href="{{ $backUrl }}" class="hover:text-iris-500 transition">&larr; {{ $backLabel }}</a>
                <span>/</span>
                <span class="text-iris-500 font-extrabold">Report {{ substr($analysis->id, 0, 10) }}...</span>
                <span class="chip bg-emerald-50 text-emerald-700 border-emerald-200 font-mono text-[10px]">
                    {{ strtoupper($analysis->status) }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-ink tracking-tight flex items-center space-x-2.5">
                <span>Player On-Chain Intelligence Profile</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-mono font-medium">
                Wallet: <strong class="text-ink select-all">{{ $analysis->address }}</strong> ({{ strtoupper($analysis->network) }})
                @if($analysis->external_player_id)
                    • Player ID: <strong class="text-ink font-sans">{{ $analysis->external_player_id }}</strong>
                @endif
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <span class="text-xs text-slate-500">Evaluated: <strong class="text-ink font-mono">{{ $analysis->created_at->format('Y-m-d H:i:s UTC') }}</strong></span>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost text-xs">
                <span>New Analysis</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Warnings / CEX Detection Alert Banner -->
    @if($isCex || count($warnings) > 0)
        <div class="p-5 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 space-y-2 card-shadow">
            <div class="flex items-center space-x-2 font-black text-sm text-amber-900">
                <span class="text-lg">🛡️</span>
                <span>Custodial CEX Pool Detected — Collective Balance Isolation Active</span>
            </div>
            <p class="text-xs text-amber-900 leading-relaxed font-medium">
                This wallet is identified as a <strong>custodial exchange hot wallet or deposit sweeper pool (Bybit/Binance/OKX)</strong>. On-Score deliberately isolates exchange pool balances to avoid erroneously attributing collective exchange funds to a single player.
            </p>
        </div>
    @endif

    <!-- Main Score & Intelligence Hero Card -->
    <div class="surface p-6 sm:p-8 relative overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Score Dial -->
            <div class="lg:col-span-4 flex flex-col items-center justify-center p-6 bg-slate-50 border border-slate-200/80 rounded-2xl">
                <div class="kpi-label mb-2">On-Score Player Index</div>
                <div class="relative flex items-center justify-center my-2">
                    <div class="text-6xl font-black font-mono tracking-tight text-ink">{{ $scoreVal }}</div>
                    <span class="text-xl font-bold text-slate-400 ml-1">/100</span>
                </div>
                <div class="mt-2">
                    @php
                        $segmentBadgeClass = match($segment) {
                            'super_vip' => 'bg-purple-100 text-purple-900 border-purple-300 ring-2 ring-purple-500/20',
                            'potential_vip' => 'bg-amber-100 text-amber-900 border-amber-300 ring-2 ring-amber-400/20',
                            'high_value' => 'bg-emerald-100 text-emerald-900 border-emerald-300 ring-2 ring-emerald-400/20',
                            'good_player' => 'bg-sky-100 text-sky-900 border-sky-300',
                            'regular' => 'bg-slate-100 text-slate-800 border-slate-300',
                            default => 'bg-rose-100 text-rose-900 border-rose-300',
                        };
                    @endphp
                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border {{ $segmentBadgeClass }}">
                        {{ strtoupper(str_replace('_', ' ', $segment)) }}
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 font-mono mt-3">
                    Confidence: <strong class="text-emerald-700">{{ ($score['confidence'] ?? 0.95) * 100 }}%</strong> • Model: {{ $score['model_version'] ?? 'WPS-v1.0' }}
                </div>
            </div>

            <!-- Key Drivers & Subscores -->
            <div class="lg:col-span-8 space-y-5">
                <div>
                    <div class="kpi-label mb-2">Key Drivers &amp; Explainability</div>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($keyDrivers as $driver)
                            <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 text-ink text-xs font-mono font-bold rounded-lg flex items-center space-x-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>{{ $driver }}</span>
                            </span>
                        @empty
                            <span class="text-xs text-slate-400 font-mono">No specific drivers triggered</span>
                        @endforelse
                    </div>
                </div>

                <!-- Subscores Breakdown Bars -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-bold">Financial Capacity (35%)</span>
                            <span class="text-ink font-black font-mono">{{ $subscores['financial_capacity'] ?? 0 }}/100</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-emerald-600 h-2 rounded-full transition-all" style="width: {{ $subscores['financial_capacity'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-bold">Gambling Activity (35%)</span>
                            <span class="text-ink font-black font-mono">{{ $subscores['gambling_activity'] ?? 0 }}/100</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-iris-500 h-2 rounded-full transition-all" style="width: {{ $subscores['gambling_activity'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-bold">Activity Recency (15%)</span>
                            <span class="text-ink font-black font-mono">{{ $subscores['activity_recency'] ?? 0 }}/100</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full transition-all" style="width: {{ $subscores['activity_recency'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-bold">Transaction Profile (15%)</span>
                            <span class="text-ink font-black font-mono">{{ $subscores['transaction_profile'] ?? 0 }}/100</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full transition-all" style="width: {{ $subscores['transaction_profile'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Behavioral Patterns & Casino Footprint Intelligence -->
    <div class="surface p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xl">🎯</span>
                    <h3 class="text-lg font-black text-ink tracking-tight">
                        Behavioral Analytics &amp; Casino Footprint
                    </h3>
                    <span class="chip bg-iris-50 text-iris-500 border-iris-300 uppercase">
                        Pattern AI Engine
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Detection of fixed bet clusters, Martingale sequences, burst gaming intensity, and unlabeled casino sweepers.
                </p>
            </div>

            <!-- Behavioral Tags -->
            <div class="flex flex-wrap gap-1.5">
                @forelse($bTags as $btag)
                    <span class="px-2.5 py-1 rounded-lg bg-iris-50 border border-iris-300 text-iris-500 font-extrabold text-[11px] font-mono flex items-center space-x-1 shadow-sm">
                        <span>⚡</span>
                        <span>{{ $btag }}</span>
                    </span>
                @empty
                    <span class="text-xs text-slate-400 font-mono">Standard behavioral pattern</span>
                @endforelse
            </div>
        </div>

        <!-- 4-Stat Strip for Behavioral Heuristics -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Round Denominations -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Round Deposits</span>
                    <span class="w-2 h-2 rounded-full {{ ($round['is_fixed_amount_depositor'] ?? false) ? 'bg-iris-500' : 'bg-slate-300' }}"></span>
                </div>
                <div class="text-2xl font-black text-ink font-mono mt-1">
                    {{ $round['round_percentage'] ?? 0 }}%
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    {{ $round['round_count'] ?? 0 }} of {{ $round['total_analyzed'] ?? 0 }} txs are round
                </div>
            </div>

            <!-- Martingale / Tilt -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Martingale Tilt Risk</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase {{ ($martingale['tilt_risk_level'] ?? 'LOW') === 'CRITICAL' ? 'bg-rose-100 text-rose-800' : (($martingale['tilt_risk_level'] ?? 'LOW') === 'HIGH' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                        {{ $martingale['tilt_risk_level'] ?? 'LOW' }}
                    </span>
                </div>
                <div class="text-2xl font-black font-mono mt-1 {{ ($martingale['detected'] ?? false) ? 'text-rose-600' : 'text-slate-800' }}">
                    {{ ($martingale['detected'] ?? false) ? ($martingale['max_multiplier'] . 'x Peak') : 'Clean' }}
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    {{ ($martingale['detected'] ?? false) ? ($martingale['sequence_count'] . ' escalating sequence(s)') : 'No loss-chasing chains' }}
                </div>
            </div>

            <!-- Night & Weekend Index -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Night Activity</span>
                    <span class="text-xs">🌙</span>
                </div>
                <div class="text-2xl font-black text-ink font-mono mt-1">
                    {{ $session['night_activity_percentage'] ?? 0 }}%
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    Weekend share: <strong class="text-slate-700">{{ $session['weekend_activity_percentage'] ?? 0 }}%</strong>
                </div>
            </div>

            <!-- Whale Potential -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Whale Potential</span>
                    <span class="text-xs">👑</span>
                </div>
                <div class="text-2xl font-black text-emerald-700 font-mono mt-1">
                    {{ $whaleScore ?? 0 }}/100
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    Churn Risk: <strong class="{{ ($behavioral['churn_risk'] ?? 'LOW') === 'HIGH' ? 'text-rose-600' : 'text-emerald-700' }}">{{ $behavioral['churn_risk'] ?? 'LOW' }}</strong>
                </div>
            </div>
        </div>

        <!-- 24-Hour Activity Heatmap / Bar Distribution -->
        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                <div>
                    <h4 class="text-xs font-black uppercase text-ink tracking-wider flex items-center space-x-2">
                        <span>24-Hour Activity Heatmap (UTC Distribution)</span>
                    </h4>
                    <p class="text-[11px] text-slate-500">
                        Peak gaming hour: <strong class="text-iris-500 font-mono">{{ sprintf('%02d:00 UTC', $session['peak_activity_hour'] ?? 0) }}</strong> • Night hours (22:00–06:00) highlighted in indigo.
                    </p>
                </div>
                <div class="text-xs font-mono font-bold text-slate-500">
                    Sessions: <strong class="text-ink">{{ $session['total_sessions_count'] ?? 0 }}</strong> (Max {{ $session['max_session_tx_count'] ?? 0 }} txs/session)
                </div>
            </div>

            <!-- Hourly Bars (0 to 23) -->
            <div class="gap-1 items-end h-20 pt-4 px-1" style="display: grid; grid-template-columns: repeat(24, minmax(0, 1fr));">
                @for($h = 0; $h < 24; $h++)
                    @php
                        $cnt = $hourly[$h] ?? 0;
                        $pctHeight = $maxHourly > 0 ? max(8, round(($cnt / $maxHourly) * 100)) : 8;
                        $isNight = ($h >= 22 || $h < 6);
                    @endphp
                    <div class="flex flex-col items-center h-full justify-end group relative">
                        <div class="w-full rounded-t-sm transition-all duration-300 {{ $cnt > 0 ? ($isNight ? 'bg-iris-500 hover:bg-iris-700' : 'bg-slate-700 hover:bg-slate-900') : 'bg-slate-200' }}"
                             style="height: {{ $cnt > 0 ? $pctHeight : 8 }}%">
                        </div>
                        <span class="text-[9px] font-mono font-bold text-slate-400 mt-1 {{ $h % 3 === 0 ? 'block' : 'hidden sm:block' }}">
                            {{ sprintf('%02d', $h) }}
                        </span>

                        <!-- Tooltip -->
                        <div class="absolute -top-8 hidden group-hover:flex px-2 py-1 bg-slate-900 text-white rounded text-[10px] font-mono whitespace-nowrap z-10 shadow-lg">
                            {{ sprintf('%02d:00', $h) }}: {{ $cnt }} txs {{ $isNight ? '(Night)' : '' }}
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Recurring Bet Clusters & Martingale Sequences Detail Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Recurring Amount Clusters -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase text-ink tracking-wider flex items-center space-x-1.5">
                        <span>Recurring Deposit / Bet Clusters</span>
                    </h4>
                    <span class="text-[10px] font-mono text-slate-500">
                        Entropy: <strong class="text-ink">{{ $round['amount_entropy'] ?? 0 }}</strong> (0 = strict fixed sizing)
                    </span>
                </div>

                @if(!empty($round['top_recurring_amounts']))
                    <div class="space-y-2">
                        @foreach($round['top_recurring_amounts'] as $cluster)
                            @php
                                $cPercentage = ($round['total_analyzed'] ?? 0) > 0 ? round(($cluster['count'] / $round['total_analyzed']) * 100, 1) : 0;
                            @endphp
                            <div class="bg-white p-3 rounded-xl border border-slate-200 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 font-mono font-black text-xs">
                                        ${{ number_format($cluster['amount_usd']) }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-700">
                                        {{ $cluster['count'] }} transactions
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-mono font-black text-ink">${{ number_format($cluster['total_volume_usd']) }}</span>
                                    <span class="text-[10px] text-slate-400 block font-mono">({{ $cPercentage }}% of total)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xs text-slate-400 italic py-3 text-center">
                        No significant recurring deposit clusters found.
                    </div>
                @endif
            </div>

            <!-- Martingale Sequences or Fast Reloads -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase text-ink tracking-wider flex items-center space-x-1.5">
                        <span>Loss-Chasing Sequences &amp; Reloads</span>
                    </h4>
                    <span class="text-[10px] font-mono text-slate-500">
                        Fast reloads (&lt;15m): <strong class="text-ink">{{ $velocity['fast_reload_count'] ?? 0 }}</strong>
                    </span>
                </div>

                @if(!empty($martingale['sequences']))
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($martingale['sequences'] as $seq)
                            <div class="bg-rose-50/70 p-3 rounded-xl border border-rose-200 text-xs space-y-1.5">
                                <div class="flex justify-between items-center text-rose-950 font-bold">
                                    <span class="flex items-center space-x-1">
                                        <span>🚨</span>
                                        <span>{{ $seq['steps_count'] }}-Step Escalation Sequence</span>
                                    </span>
                                    <span class="font-mono text-rose-700 font-black">{{ $seq['multiplier'] }}x Multiplier</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-600 font-mono">
                                    <span>${{ number_format($seq['start_amount_usd']) }} &rarr; ${{ number_format($seq['peak_amount_usd']) }} (Total: ${{ number_format($seq['total_sequence_volume_usd']) }})</span>
                                    <span>{{ $seq['duration_minutes'] }} mins</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white p-4 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-2">
                        <div class="flex items-center space-x-2 text-emerald-700 font-bold">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>No aggressive Martingale loss-chasing chains detected</span>
                        </div>
                        <p class="text-[11px] text-slate-500">
                            The player maintains stable transaction sizing without geometric doubling during rapid loss intervals.
                        </p>
                    </div>
                @endif

                <!-- Unlabeled Casino Sweeper Score -->
                <div class="bg-white p-3 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                    <div>
                        <span class="kpi-label block">Unlabeled Casino Footprint Index:</span>
                        <span class="text-slate-600 text-[11px]">Forwarding proxies &amp; round transfer destinations</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold {{ ($unlabeled['unlabeled_casino_score'] ?? 0) >= 70 ? 'bg-iris-50 text-iris-500' : 'bg-slate-100 text-slate-700' }}">
                        {{ $unlabeled['unlabeled_casino_score'] ?? 0 }}/100
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid: Financials & Gambling History -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- 1. Balance & Turnover -->
        <div class="surface p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-ink flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Financial Capacity &amp; Liquidity</span>
            </h3>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                    <div class="kpi-label">Visible Assets (USD)</div>
                    <div class="text-2xl font-black text-emerald-700 font-mono mt-1">${{ number_format($balance['visible_balance_usd'] ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Non-CEX liquid balance</div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                    <div class="kpi-label">365d Total Turnover</div>
                    <div class="text-2xl font-black text-ink font-mono mt-1">${{ number_format($turnover['365d_usd'] ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Cumulative movements</div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-600 font-medium">30d Turnover:</span>
                    <span class="text-ink font-mono font-bold">${{ number_format($turnover['30d_usd'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-600 font-medium">90d Turnover:</span>
                    <span class="text-ink font-mono font-bold">${{ number_format($turnover['90d_usd'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-600 font-medium">Lifetime Turnover:</span>
                    <span class="text-ink font-mono font-bold">${{ number_format($turnover['lifetime_usd'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-600 font-medium">Inflow / Outflow (365d):</span>
                    <span class="text-ink font-mono font-bold">
                        <span class="text-emerald-700">+${{ number_format($turnover['inflow_365d_usd'] ?? 0) }}</span> / 
                        <span class="text-rose-700">-${{ number_format($turnover['outflow_365d_usd'] ?? 0) }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Gambling Intelligence Vertical -->
        <div class="surface p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-ink flex items-center space-x-2">
                <svg class="w-4 h-4 text-iris-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Gambling Intelligence Vertical</span>
            </h3>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                    <div class="kpi-label">Gambling Flow (365d)</div>
                    <div class="text-2xl font-black text-iris-500 font-mono mt-1">${{ number_format($gambling['total_flow_365d_usd'] ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Deposits: ${{ number_format($gambling['outgoing_365d_usd'] ?? 0) }}</div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                    <div class="kpi-label">Known Gambling Brands</div>
                    <div class="text-2xl font-black text-ink font-mono mt-1">{{ $gambling['entities_count'] ?? 0 }}</div>
                    <div class="text-[10px] text-slate-500 font-medium mt-0.5 truncate">{{ implode(', ', $gambling['entities_list'] ?? []) ?: 'None detected' }}</div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-600 font-medium">Gambling Status:</span>
                    <span class="font-bold uppercase {{ ($gambling['status'] ?? '') === 'detected' ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $gambling['status'] ?? 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-600 font-medium">Gambling Share of Turnover:</span>
                    <span class="text-ink font-mono font-bold">{{ (($gambling['gambling_share_of_turnover'] ?? 0) * 100) }}%</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/60">
                    <span class="text-slate-600 font-medium">Last Gambling Activity:</span>
                    <span class="text-ink font-mono font-bold">{{ $gambling['last_gambling_activity'] ? Carbon\Carbon::parse($gambling['last_gambling_activity'])->diffForHumans() : '—' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-600 font-medium">First Gambling Activity:</span>
                    <span class="text-ink font-mono font-bold">{{ $gambling['first_gambling_activity'] ? Carbon\Carbon::parse($gambling['first_gambling_activity'])->format('Y-m-d') : '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Counterparties Section -->
    <div class="surface p-6">
        <h3 class="text-sm font-extrabold text-ink mb-3">Attributed Counterparties &amp; Protocols</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px]">
                        <th class="py-2.5 px-3">Entity Name</th>
                        <th class="py-2.5 px-3">Category</th>
                        <th class="py-2.5 px-3">Confidence</th>
                        <th class="py-2.5 px-3">Transactions</th>
                        <th class="py-2.5 px-3 text-right">Attributed Volume</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 font-medium text-slate-700">
                    @forelse($counterparties as $cp)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-3 font-extrabold text-ink">{{ $cp['name'] }}</td>
                        <td class="py-3 px-3">
                            <span class="chip uppercase {{ $cp['category'] === 'gambling' ? 'bg-indigo-50 text-indigo-800' : ($cp['category'] === 'cex' ? 'bg-purple-50 text-purple-800' : 'bg-slate-100 text-slate-800') }}">
                                {{ $cp['category'] }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-emerald-700">{{ ($cp['confidence'] ?? 1.0) * 100 }}%</td>
                        <td class="py-3 px-3 text-slate-700 font-mono">{{ $cp['tx_count'] }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-ink">${{ number_format($cp['total_volume_usd']) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">No known counterparties detected from Entity DB.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Raw JSON Inspector -->
    <div class="surface p-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-extrabold text-ink font-mono uppercase tracking-wider">Immutable Snapshot JSON (API Output)</h3>
            <span class="chip bg-slate-100 text-slate-600 font-mono">Audit Ready</span>
        </div>
        <pre class="bg-slate-900 rounded-xl p-4 text-xs font-mono text-emerald-400 overflow-x-auto max-h-80 shadow-inner">{{ json_encode($snapshot->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
</div>
@endsection
