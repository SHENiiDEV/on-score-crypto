@extends('layout')

@section('title', 'Dashboard — on-score.io')

@section('content')
<div class="space-y-8">
    <!-- Top Stats Bar -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 card-shadow relative overflow-hidden">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Credit Balance</div>
            <div class="text-3xl font-black text-slate-900 font-mono mt-2">{{ number_format($account->credit_balance) }}</div>
            <div class="text-xs text-emerald-700 font-semibold mt-1 flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Active B2B Account</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 card-shadow relative overflow-hidden">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Analyses</div>
            <div class="text-3xl font-black text-slate-900 font-mono mt-2">{{ number_format($totalAnalysesCount) }}</div>
            <div class="text-xs text-slate-500 mt-1">Single & Batch requests</div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 card-shadow relative overflow-hidden">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">VIP Players Detected</div>
            <div class="text-3xl font-black text-amber-600 font-mono mt-2">{{ number_format($vipCount) }}</div>
            <div class="text-xs text-amber-700 font-semibold mt-1">Score 85–100 (Potential VIP)</div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 card-shadow relative overflow-hidden">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Entities in Database</div>
            <div class="text-3xl font-black text-indigo-600 font-mono mt-2">{{ number_format($entitiesCount) }}</div>
            <div class="text-xs text-slate-500 mt-1">CEX (Bybit/Binance) & Casinos</div>
        </div>
    </div>

    <!-- Main Analyzer Section -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 card-shadow relative">
        <div class="max-w-3xl">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center space-x-3">
                <span>On-Chain Wallet Intelligence & Scoring</span>
                <span class="text-xs px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800 font-extrabold uppercase">Live Engine</span>
            </h2>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                Enter player crypto wallet address. The engine automatically <strong>filters out exchange pools (Bybit, Binance)</strong>, calculates visible liquid assets, historical gambling turnover, and delivers <strong class="text-slate-900">Player Score (0–100)</strong>.
            </p>
        </div>

        <form action="{{ route('dashboard.analyze') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Blockchain Network</label>
                    <select name="network" id="networkSelect" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-semibold focus:border-emerald-500 focus:bg-white focus:outline-none text-xs">
                        <option value="tron">TRON (TRC-20 USDT / TRX) — 3 Credits</option>
                        <option value="ethereum">Ethereum (ERC-20 / ETH) — 1 Credit</option>
                        <option value="bsc">BNB Chain (BEP-20) — 1 Credit</option>
                        <option value="solana">Solana (SPL) — 2 Credits</option>
                        <option value="bitcoin">Bitcoin (BTC UTXO) — 3 Credits</option>
                    </select>
                </div>

                <div class="sm:col-span-6">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Wallet Address</label>
                    <input type="text" name="address" id="addressInput" required placeholder="T... (TRON) or 0x... (EVM)"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 font-mono text-xs font-semibold focus:border-emerald-500 focus:bg-white focus:outline-none">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Casino Player ID (Optional)</label>
                    <input type="text" name="external_player_id" placeholder="player_88921"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-900 text-xs font-semibold focus:border-emerald-500 focus:bg-white focus:outline-none">
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="flex flex-wrap items-center gap-2 pt-2">
                <span class="text-xs font-bold text-slate-500">Quick Test Cases:</span>
                <button type="button" onclick="setPreset('tron', 'TVipWhalePlayerAddressTron7788991122', 'vip_player_01')"
                        class="text-xs bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-900 px-3 py-1.5 rounded-lg transition font-bold">
                    🔥 VIP Whale Gambler ($1.8M turnover)
                </button>
                <button type="button" onclick="setPreset('tron', 'TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71', 'bybit_pool_test')"
                        class="text-xs bg-rose-50 hover:bg-rose-100 border border-rose-300 text-rose-900 px-3 py-1.5 rounded-lg transition font-bold">
                    🚫 Bybit Hot Wallet (CEX Pool Filter Test)
                </button>
                <button type="button" onclick="setPreset('tron', 'TRegularGamblerUserTrx9988112233', 'regular_player_42')"
                        class="text-xs bg-blue-50 hover:bg-blue-100 border border-blue-300 text-blue-900 px-3 py-1.5 rounded-lg transition font-bold">
                    🎰 Regular Player ($14k turnover)
                </button>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black px-8 py-3.5 rounded-xl transition shadow-md shadow-emerald-600/20 text-xs uppercase tracking-wider flex items-center space-x-2">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>Run On-Score Analysis</span>
                </button>
            </div>
        </form>
    </div>

    <!-- API & Integration Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow lg:col-span-1">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                <span>B2B API Credentials</span>
            </h3>
            <p class="text-xs text-slate-500 mt-1">Credentials for casino backend integration.</p>

            @if($apiClient)
            <div class="mt-4 space-y-3">
                <div>
                    <label class="text-[11px] font-bold uppercase text-slate-500">API Key ID</label>
                    <input type="text" readonly value="{{ $apiClient->key_id }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-slate-900 select-all mt-1">
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase text-slate-500">Demo Secret Key</label>
                    <input type="text" readonly value="ons_sec_demo_secret_apex_778899aabbcc" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono text-slate-600 select-all mt-1">
                </div>
                <div class="pt-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        Rate limit: 300 req/min
                    </span>
                </div>
            </div>
            @endif
        </div>

        <!-- Quick API Code Snippet -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        <span>cURL Integration Quickstart</span>
                    </h3>
                    <span class="text-xs text-slate-500 font-mono font-bold">POST /api/v1/analyses</span>
                </div>
                <div class="mt-3 bg-slate-900 text-slate-100 rounded-xl p-4 font-mono text-xs overflow-x-auto shadow-inner">
                    <span class="text-emerald-400">curl</span> -X POST https://on-score.io/api/v1/analyses \<br>
                    &nbsp;&nbsp;-H <span class="text-amber-300">"X-API-Key: {{ $apiClient?->key_id }}"</span> \<br>
                    &nbsp;&nbsp;-H <span class="text-amber-300">"X-API-Secret: ons_sec_demo_secret_apex_778899aabbcc"</span> \<br>
                    &nbsp;&nbsp;-H <span class="text-emerald-400">"Content-Type: application/json"</span> \<br>
                    &nbsp;&nbsp;-d '<span class="text-teal-300">{"network":"tron","address":"TVipWhalePlayerAddressTron7788991122","external_player_id":"usr_102"}</span>'
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>Returns complete structured <strong>Player Score (0-100)</strong> profile, liquidity, and gambling flows.</span>
            </div>
        </div>
    </div>

    <!-- Recent Analyses & Ledger Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Analyses List -->
        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
            <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center justify-between">
                <span>Recent Analyses</span>
                <span class="text-xs text-slate-400 font-normal">Request History</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold text-[10px]">
                            <th class="py-3 px-3">Network / Address</th>
                            <th class="py-3 px-3">Score & Segment</th>
                            <th class="py-3 px-3">Gambling Flow</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Report</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($recentAnalyses as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-3">
                                <div class="font-mono font-bold text-slate-900 text-xs">{{ Str::limit($item->address, 18) }}</div>
                                <div class="text-[10px] text-slate-400 uppercase font-semibold">{{ $item->network }} • ID: {{ $item->external_player_id ?? 'n/a' }}</div>
                            </td>
                            <td class="py-3.5 px-3">
                                @if($item->score_value !== null)
                                    @php
                                        $badgeColor = match($item->segment) {
                                            'super_vip' => 'bg-purple-100 text-purple-800 border-purple-300 font-black',
                                            'potential_vip' => 'bg-amber-100 text-amber-800 border-amber-300',
                                            'high_value' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                            'good_player' => 'bg-blue-100 text-blue-800 border-blue-300',
                                            'regular' => 'bg-slate-100 text-slate-800 border-slate-300',
                                            default => 'bg-rose-100 text-rose-800 border-rose-300',
                                        };
                                    @endphp
                                    <div class="flex items-center space-x-2">
                                        <span class="font-black text-sm font-mono text-slate-900">{{ $item->score_value }}</span>
                                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full border {{ $badgeColor }}">
                                            {{ strtoupper(str_replace('_', ' ', $item->segment)) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3">
                                @if($item->snapshot)
                                    <div class="text-slate-900 font-mono font-bold">${{ number_format($item->snapshot->gambling_intelligence['total_flow_365d_usd'] ?? 0) }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold">{{ $item->snapshot->gambling_intelligence['entities_count'] ?? 0 }} casinos</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $item->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                <a href="{{ route('dashboard.report', ['id' => $item->id]) }}" class="text-emerald-700 hover:text-emerald-900 font-extrabold hover:underline">
                                    View Report &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">
                                No analyses recorded yet. Enter a wallet address above to run.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Credit Ledger History -->
        <div class="lg:col-span-4 bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
            <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center justify-between">
                <span>Credit Ledger</span>
                <span class="text-xs text-slate-400 font-mono">Audit Trail</span>
            </h3>

            <div class="space-y-3">
                @forelse($ledgerRecent as $ledger)
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs">
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
                <div class="text-center py-6 text-slate-400 text-xs">Ledger is empty</div>
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
