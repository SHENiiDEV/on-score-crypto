@extends('console.layout')

@section('title', 'Wallet Scoring Workbench — On-Score.io')

@section('content')
<div class="space-y-6 pb-12">
    
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 mb-1">
                <span>Intelligence</span>
                <span>/</span>
                <span class="text-iris-500 font-extrabold">Wallet Scoring Workbench</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Live Scoring Engine
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-ink tracking-tight">
                Wallet Intelligence Workbench
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Active Client Account: <strong class="text-ink">{{ $account->name }}</strong> • Interactive single wallet scoring, CEX pool isolation, and gambling footprint audit.
            </p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a href="{{ route('admin.analyses') }}" class="btn btn-ghost text-xs">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>All Analyses</span>
            </a>
            <a href="{{ route('docs') }}" target="_blank" class="btn btn-iris text-xs">
                <span>API Docs</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="surface p-4">
            <div class="kpi-label">Credit Balance</div>
            <div class="text-2xl font-black text-ink font-mono mt-1">{{ number_format($account->credit_balance) }}</div>
            <div class="text-[11px] text-emerald-700 font-bold mt-0.5 flex items-center space-x-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Active B2B Credits</span>
            </div>
        </div>

        <div class="surface p-4">
            <div class="kpi-label">Total Analyses</div>
            <div class="text-2xl font-black text-ink font-mono mt-1">{{ number_format($totalAnalysesCount) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Single & Batch requests</div>
        </div>

        <div class="surface p-4">
            <div class="kpi-label">VIP Players Detected</div>
            <div class="text-2xl font-black text-amber-600 font-mono mt-1">{{ number_format($vipCount) }}</div>
            <div class="text-[11px] text-amber-700 font-bold mt-0.5">Score 85–100 (Potential VIP)</div>
        </div>

        <div class="surface p-4">
            <div class="kpi-label">Entities In Database</div>
            <div class="text-2xl font-black text-iris-500 font-mono mt-1">{{ number_format($entitiesCount) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">CEX (Bybit/Binance) & Casinos</div>
        </div>
    </div>

    <!-- Main Scoring Input Card -->
    <div class="surface p-6 sm:p-8 relative overflow-hidden">
        <div class="max-w-3xl">
            <h2 class="text-xl font-black text-ink tracking-tight flex items-center space-x-2">
                <span>Real-Time On-Chain Intelligence</span>
                <span class="chip bg-iris-50 text-iris-500 border-iris-300">WPS-v1.0</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Enter player crypto wallet address. The engine automatically <strong>isolates exchange hot pools (Bybit, Binance, OKX)</strong>, analyzes behavioral betting patterns (round denominations, Martingale loss-chasing), and calculates comprehensive <strong class="text-ink">Player Score (0–100)</strong>.
            </p>
        </div>

        <form action="{{ route('dashboard.analyze') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-3">
                    <label class="label">Blockchain Network</label>
                    <select name="network" id="networkSelect" class="input font-semibold text-xs py-2.5">
                        <option value="tron">TRON (TRC-20 USDT / TRX) — 3 Credits</option>
                        <option value="ethereum">Ethereum (ERC-20 / ETH) — 1 Credit</option>
                        <option value="bsc">BNB Chain (BEP-20) — 1 Credit</option>
                        <option value="solana">Solana (SPL) — 2 Credits</option>
                        <option value="bitcoin">Bitcoin (BTC UTXO) — 3 Credits</option>
                    </select>
                </div>

                <div class="sm:col-span-6">
                    <label class="label">Wallet Address</label>
                    <input type="text" name="address" id="addressInput" required placeholder="T... (TRON) or 0x... (EVM)"
                           class="input font-mono text-xs font-bold py-2.5">
                </div>

                <div class="sm:col-span-3">
                    <label class="label">Player ID / Tag (Optional)</label>
                    <input type="text" name="external_player_id" placeholder="player_88921"
                           class="input text-xs py-2.5">
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <span class="text-[11px] font-bold text-slate-400">Quick Test Cases:</span>
                <button type="button" onclick="setPreset('tron', 'TVipWhalePlayerAddressTron7788991122', 'vip_whale_01')"
                        class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 text-xs font-bold transition">
                    🔥 VIP Whale Gambler ($1.8M turnover)
                </button>
                <button type="button" onclick="setPreset('tron', 'TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71', 'bybit_pool_test')"
                        class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-900 border border-rose-200 text-xs font-bold transition">
                    🚫 Bybit Hot Wallet (CEX Pool Filter Test)
                </button>
                <button type="button" onclick="setPreset('tron', 'TRegularGamblerUserTrx9988112233', 'regular_player_42')"
                        class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 text-xs font-bold transition">
                    🎰 Regular Player ($14k turnover)
                </button>
            </div>

            <div class="pt-3 flex justify-end">
                <button type="submit" class="btn btn-iris px-8 py-3 text-xs font-extrabold shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>Run On-Score Analysis</span>
                </button>
            </div>
        </form>
    </div>

    <!-- API & cURL Integration Quickstart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="surface p-5 lg:col-span-1 space-y-3">
            <h3 class="text-sm font-extrabold text-ink flex items-center space-x-2">
                <svg class="w-4 h-4 text-iris-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                <span>B2B API Credentials</span>
            </h3>
            <p class="text-[11px] text-slate-500">Live credential identifiers for backend casino integration.</p>

            @if($apiClient)
            <div class="space-y-2.5 pt-1">
                <div>
                    <label class="kpi-label">API Key ID (Client ID)</label>
                    <input type="text" readonly value="{{ $apiClient->key_id }}" class="input font-mono font-bold text-xs py-1.5 select-all mt-1 bg-slate-50">
                </div>
                <div>
                    <label class="kpi-label">API Secret Token</label>
                    <input type="text" readonly value="ons_sec_live_{{ substr(md5($apiClient->key_id), 0, 24) }}" class="input font-mono text-xs py-1.5 select-all mt-1 bg-slate-50 text-slate-500">
                </div>
                <div class="pt-1 flex justify-between items-center text-[11px]">
                    <span class="chip bg-emerald-50 text-emerald-700 border-emerald-200">
                        Rate Limit: {{ $apiClient->rate_limit_per_minute ?? 300 }} req/min
                    </span>
                    <a href="{{ route('admin.keys') }}" class="text-iris-500 hover:underline font-bold text-xs">Manage Keys &rarr;</a>
                </div>
            </div>
            @endif
        </div>

        <div class="surface p-5 lg:col-span-2 flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-ink flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        <span>cURL Integration Quickstart</span>
                    </h3>
                    <span class="text-[10px] font-mono font-bold text-slate-400">POST /api/v1/score/wallet</span>
                </div>
                <div class="mt-2.5 bg-slate-900 text-slate-100 rounded-xl p-3.5 font-mono text-xs overflow-x-auto shadow-inner">
                    <span class="text-emerald-400">curl</span> -X POST https://on-score.io/api/v1/score/wallet \<br>
                    &nbsp;&nbsp;-H <span class="text-amber-300">"Authorization: Bearer {{ $apiClient?->key_id }}"</span> \<br>
                    &nbsp;&nbsp;-H <span class="text-emerald-400">"Content-Type: application/json"</span> \<br>
                    &nbsp;&nbsp;-d '<span class="text-teal-300">{"chain":"TRON","address":"TVipWhalePlayerAddressTron7788991122","external_player_id":"usr_102"}</span>'
                </div>
            </div>
            <div class="text-[11px] text-slate-500 font-medium flex justify-between items-center">
                <span>Returns complete structured <strong>Player Score (0-100)</strong>, behavioral tags, and risk metrics.</span>
                <a href="{{ route('docs') }}" target="_blank" class="text-iris-500 hover:underline font-bold">Full API Specs &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Recent Analyses & Ledger Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Analyses List -->
        <div class="lg:col-span-8 surface p-5">
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-ink">Recent Wallet Analyses</h3>
                    <p class="text-[11px] text-slate-400">Live scoring request stream</p>
                </div>
                <a href="{{ route('admin.analyses') }}" class="text-xs text-iris-500 hover:text-iris-700 font-bold">View all &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px]">
                            <th class="py-2.5 px-3">Network / Address</th>
                            <th class="py-2.5 px-3">Score &amp; Segment</th>
                            <th class="py-2.5 px-3">Gambling Flow</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Report</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-medium text-slate-700">
                        @forelse($recentAnalyses as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-3">
                                <div class="font-mono font-bold text-ink text-xs">{{ Str::limit($item->address, 20) }}</div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">{{ $item->network }} • ID: {{ $item->external_player_id ?? 'n/a' }}</div>
                            </td>
                            <td class="py-3 px-3">
                                @if($item->score_value !== null)
                                    <div class="flex items-center space-x-2">
                                        <span class="font-black text-sm font-mono text-ink">{{ $item->score_value }}</span>
                                        @include('console.partials.segment-badge', ['segment' => $item->segment])
                                    </div>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($item->snapshot)
                                    <div class="text-ink font-mono font-bold">${{ number_format($item->snapshot->gambling_intelligence['total_flow_365d_usd'] ?? 0) }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold">{{ $item->snapshot->gambling_intelligence['entities_count'] ?? 0 }} casinos</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @include('console.partials.status-badge', ['status' => $item->status])
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('dashboard.report', ['id' => $item->id]) }}" class="text-iris-500 hover:text-iris-700 font-extrabold hover:underline">
                                    Report &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                No analyses recorded yet. Enter a wallet address above to run.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Credit Ledger History -->
        <div class="lg:col-span-4 surface p-5">
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold text-ink">Credit Ledger</h3>
                    <p class="text-[11px] text-slate-400">Usage & top-up audit trail</p>
                </div>
                <a href="{{ route('admin.ledger') }}" class="text-xs text-iris-500 hover:text-iris-700 font-bold">Ledger &rarr;</a>
            </div>

            <div class="space-y-2.5">
                @forelse($ledgerRecent as $ledger)
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold {{ $ledger->delta > 0 ? 'text-emerald-700 font-black' : ($ledger->delta < 0 ? 'text-rose-700 font-black' : 'text-slate-700') }} font-mono">
                            {{ $ledger->delta > 0 ? '+' : '' }}{{ $ledger->delta }} Credits
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $ledger->created_at->format('M d, H:i') }}</span>
                    </div>
                    <div class="text-slate-600 text-[11px] mt-1 line-clamp-1 font-medium">{{ $ledger->reason }}</div>
                    <div class="text-[10px] text-slate-400 mt-1 flex justify-between font-mono">
                        <span>Type: {{ $ledger->type }}</span>
                        <span>Balance: {{ $ledger->balance_after }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400 text-xs">Ledger is empty</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
function setPreset(network, address, playerId) {
    document.getElementById('networkSelect').value = network;
    document.getElementById('addressInput').value = address;
    const playerInput = document.querySelector('input[name="external_player_id"]');
    if (playerInput) playerInput.value = playerId;
}
</script>
@endsection
