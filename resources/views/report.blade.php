@extends('layout')

@section('title', 'Player Intelligence Report — ' . $analysis->address)

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

    $segmentBadgeClass = match($segment) {
        'super_vip' => 'bg-purple-100 text-purple-900 border-purple-300 ring-2 ring-purple-500/30',
        'potential_vip' => 'bg-amber-100 text-amber-900 border-amber-300 ring-2 ring-amber-400/20',
        'high_value' => 'bg-emerald-100 text-emerald-900 border-emerald-300 ring-2 ring-emerald-400/20',
        'good_player' => 'bg-blue-100 text-blue-900 border-blue-300',
        'regular' => 'bg-slate-100 text-slate-800 border-slate-300',
        default => 'bg-rose-100 text-rose-900 border-rose-300',
    };
@endphp

<div class="space-y-8">
    <!-- Header Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                @php $rp_back = (auth()->check() && !auth()->user()->isAdmin()) ? route('merchant.usage') : route('dashboard'); @endphp
                <a href="{{ $rp_back }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 flex items-center space-x-1">
                    <span>&larr; Back to Dashboard</span>
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-400 font-mono">Analysis ID: {{ $analysis->id }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1 flex items-center space-x-3">
                <span>Player On-Chain Intelligence Profile</span>
            </h1>
        </div>

        <div class="flex items-center space-x-3">
            <span class="text-xs text-slate-500">Evaluated at: <strong class="text-slate-900">{{ $analysis->created_at->format('Y-m-d H:i:s UTC') }}</strong></span>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-mono font-bold">
                {{ strtoupper($analysis->status) }}
            </span>
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
                This wallet is identified as a <strong>custodial exchange hot wallet or deposit sweeper pool (Bybit/Binance)</strong>. On-Score deliberately isolates exchange pool balances to avoid erroneously attributing hundreds of millions in collective exchange funds to a single player.
            </p>
        </div>
    @endif

    <!-- Main Score & Intelligence Hero Card -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 card-shadow">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Score Dial -->
            <div class="lg:col-span-4 flex flex-col items-center justify-center p-6 bg-slate-50 border border-slate-200 rounded-2xl">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">On-Score Player Index</div>
                <div class="relative flex items-center justify-center my-2">
                    <div class="text-6xl font-black font-mono tracking-tight text-slate-900">{{ $scoreVal }}</div>
                    <span class="text-xl font-bold text-slate-400 ml-1">/100</span>
                </div>
                <div class="mt-2">
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
                    <div class="text-xs font-bold text-slate-500 uppercase mb-2">Key Drivers & Explainability</div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($keyDrivers as $driver)
                            <span class="px-3 py-1 bg-slate-100 border border-slate-200 text-slate-800 text-xs font-mono font-bold rounded-lg flex items-center space-x-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>{{ $driver }}</span>
                            </span>
                        @empty
                            <span class="text-xs text-slate-400 font-mono">No specific drivers triggered</span>
                        @endforelse
                    </div>
                </div>

                <!-- Subscores Breakdown Bars -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-bold">Financial Capacity (35%)</span>
                            <span class="text-slate-900 font-black font-mono">{{ $subscores['financial_capacity'] ?? 0 }}/100</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $subscores['financial_capacity'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-bold">Gambling Activity (35%)</span>
                            <span class="text-slate-900 font-black font-mono">{{ $subscores['gambling_activity'] ?? 0 }}/100</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $subscores['gambling_activity'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-bold">Activity Recency (15%)</span>
                            <span class="text-slate-900 font-black font-mono">{{ $subscores['activity_recency'] ?? 0 }}/100</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $subscores['activity_recency'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-bold">Transaction Profile (15%)</span>
                            <span class="text-slate-900 font-black font-mono">{{ $subscores['transaction_profile'] ?? 0 }}/100</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $subscores['transaction_profile'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid: Financials & Gambling History -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Balance & Turnover -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4 card-shadow">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Financial Capacity & Liquidity</span>
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="text-[11px] text-slate-500 uppercase font-bold">Visible Assets (USD)</div>
                    <div class="text-2xl font-black text-emerald-700 font-mono mt-1">${{ number_format($balance['visible_balance_usd'] ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400 mt-1">Non-CEX liquid balance</div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="text-[11px] text-slate-500 uppercase font-bold">365d Total Turnover</div>
                    <div class="text-2xl font-black text-slate-900 font-mono mt-1">${{ number_format($turnover['365d_usd'] ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400 mt-1">Cumulative movements</div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-600 font-medium">30d Turnover:</span>
                    <span class="text-slate-900 font-mono font-bold">${{ number_format($turnover['30d_usd'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-600 font-medium">90d Turnover:</span>
                    <span class="text-slate-900 font-mono font-bold">${{ number_format($turnover['90d_usd'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-600 font-medium">Lifetime Turnover:</span>
                    <span class="text-slate-900 font-mono font-bold">${{ number_format($turnover['lifetime_usd'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-600 font-medium">Inflow / Outflow (365d):</span>
                    <span class="text-slate-900 font-mono font-bold">
                        <span class="text-emerald-700">+${{ number_format($turnover['inflow_365d_usd'] ?? 0) }}</span> / 
                        <span class="text-rose-700">-${{ number_format($turnover['outflow_365d_usd'] ?? 0) }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Gambling Intelligence Vertical -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4 card-shadow">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Gambling Intelligence Vertical</span>
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="text-[11px] text-slate-500 uppercase font-bold">Gambling Flow (365d)</div>
                    <div class="text-2xl font-black text-indigo-700 font-mono mt-1">${{ number_format($gambling['total_flow_365d_usd'] ?? 0) }}</div>
                    <div class="text-[10px] text-slate-400 mt-1">Deposits: ${{ number_format($gambling['outgoing_365d_usd'] ?? 0) }}</div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="text-[11px] text-slate-500 uppercase font-bold">Known Gambling Brands</div>
                    <div class="text-2xl font-black text-slate-900 font-mono mt-1">{{ $gambling['entities_count'] ?? 0 }}</div>
                    <div class="text-[10px] text-slate-500 font-medium mt-1">{{ implode(', ', $gambling['entities_list'] ?? []) ?: 'None detected' }}</div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-600 font-medium">Gambling Status:</span>
                    <span class="font-bold uppercase {{ ($gambling['status'] ?? '') === 'detected' ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $gambling['status'] ?? 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-600 font-medium">Gambling Share of Turnover:</span>
                    <span class="text-slate-900 font-mono font-bold">{{ (($gambling['gambling_share_of_turnover'] ?? 0) * 100) }}%</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-600 font-medium">Last Gambling Activity:</span>
                    <span class="text-slate-900 font-mono font-bold">{{ $gambling['last_gambling_activity'] ? Carbon\Carbon::parse($gambling['last_gambling_activity'])->diffForHumans() : '—' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-600 font-medium">First Gambling Activity:</span>
                    <span class="text-slate-900 font-mono font-bold">{{ $gambling['first_gambling_activity'] ? Carbon\Carbon::parse($gambling['first_gambling_activity'])->format('Y-m-d') : '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Counterparties Section -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
        <h3 class="text-base font-extrabold text-slate-900 mb-4">Attributed Counterparties & Protocols</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold text-[10px]">
                        <th class="py-2.5 px-3">Entity Name</th>
                        <th class="py-2.5 px-3">Category</th>
                        <th class="py-2.5 px-3">Confidence</th>
                        <th class="py-2.5 px-3">Transactions</th>
                        <th class="py-2.5 px-3 text-right">Attributed Volume</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($counterparties as $cp)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-3 font-extrabold text-slate-900">{{ $cp['name'] }}</td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $cp['category'] === 'gambling' ? 'bg-indigo-100 text-indigo-800' : ($cp['category'] === 'cex' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-800') }}">
                                {{ $cp['category'] }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-emerald-700">{{ ($cp['confidence'] ?? 1.0) * 100 }}%</td>
                        <td class="py-3 px-3 text-slate-700 font-mono">{{ $cp['tx_count'] }}</td>
                        <td class="py-3 px-3 text-right font-mono font-black text-slate-900">${{ number_format($cp['total_volume_usd']) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-slate-400">No known counterparties detected from Entity DB.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Raw JSON Inspector -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-extrabold text-slate-900 font-mono">Immutable Snapshot JSON (API Output)</h3>
            <span class="text-xs text-slate-400 font-mono">Audit Ready</span>
        </div>
        <pre class="bg-slate-900 rounded-xl p-4 text-xs font-mono text-emerald-400 overflow-x-auto max-h-80 shadow-inner">{{ json_encode($snapshot->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
</div>
@endsection
