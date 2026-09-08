@extends('layout')

@section('title', 'Admin Panel — Client & API Key Management')

@section('content')
<div class="space-y-8">
    <!-- Top Bar Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center space-x-3">
                <span>On-Score Admin Dashboard</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                    B2B & API Management
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Client onboarding, API key provisioning, credit billing and audit logs.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.entities') }}" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>CEX & Casino Database</span>
            </a>
        </div>
    </div>

    <!-- Alert for newly generated API Key (One-time Secret Display) -->
    @if(session('new_api_key'))
        @php $keyData = session('new_api_key'); @endphp
        <div class="p-6 rounded-2xl bg-amber-50 border-2 border-amber-300 shadow-md space-y-4">
            <div class="flex items-center space-x-3 text-amber-900">
                <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center font-black text-lg shrink-0">🔑</div>
                <div>
                    <h3 class="text-base font-extrabold text-amber-950">New API Key Successfully Generated!</h3>
                    <p class="text-xs text-amber-800 font-medium">Account: <strong class="text-slate-900">{{ $keyData['account_name'] }}</strong> • Label: <strong class="text-slate-900">{{ $keyData['key_name'] }}</strong></p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-amber-200 space-y-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-500">API Key ID (Public Identifier):</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <input type="text" id="copyKeyId" readonly value="{{ $keyData['key_id'] }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono text-slate-900 font-bold select-all">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $keyData['key_id'] }}'); alert('Key ID copied to clipboard!');" class="px-3 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition shrink-0">
                            Copy ID
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-rose-600">API Secret Key (Secret Token — Displayed ONLY NOW):</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <input type="text" id="copySecret" readonly value="{{ $keyData['secret'] }}" class="w-full bg-rose-50 border border-rose-300 rounded-lg px-3 py-2 text-xs font-mono text-rose-700 font-bold select-all">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $keyData['secret'] }}'); alert('API Secret copied to clipboard!');" class="px-3 py-2 bg-rose-600 text-white text-xs font-bold rounded-lg hover:bg-rose-700 transition shrink-0">
                            Copy Secret
                        </button>
                    </div>
                </div>
            </div>

            <p class="text-xs text-amber-800 font-medium">
                ⚠️ <strong>Important:</strong> Please store this secret key safely. For security, only the SHA-256 hash is stored in the database, and this secret cannot be retrieved again.
            </p>
        </div>
    @endif

    <!-- Top KPI Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 card-shadow">
            <div class="text-xs font-bold uppercase text-slate-400">B2B Clients</div>
            <div class="text-3xl font-black text-slate-900 font-mono mt-1">{{ $accounts->count() }}</div>
            <div class="text-xs text-emerald-600 font-semibold mt-1">Active Accounts</div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 card-shadow">
            <div class="text-xs font-bold uppercase text-slate-400">Total API Keys</div>
            <div class="text-3xl font-black text-indigo-600 font-mono mt-1">{{ $apiClients->count() }}</div>
            <div class="text-xs text-slate-500 mt-1">Active: {{ $apiClients->where('status', 'active')->count() }}</div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 card-shadow">
            <div class="text-xs font-bold uppercase text-slate-400">Total Analyses Performed</div>
            <div class="text-3xl font-black text-slate-900 font-mono mt-1">{{ number_format($totalAnalyses) }}</div>
            <div class="text-xs text-slate-500 mt-1">Via API & Web</div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 card-shadow">
            <div class="text-xs font-bold uppercase text-slate-400">Credits Issued</div>
            <div class="text-3xl font-black text-emerald-600 font-mono mt-1">{{ number_format($totalCreditsIssued) }}</div>
            <div class="text-xs text-slate-500 mt-1">Total provisioned</div>
        </div>
    </div>

    <!-- Actions Row: Create Client & Generate Key -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Generate API Key Form -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
            <div class="flex items-center space-x-2 text-indigo-600 mb-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                <h3 class="text-base font-extrabold text-slate-900">Provision API Key for Client</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">Generate secure `ons_live_...` credentials linked to client account.</p>

            <form action="{{ route('admin.api-keys.generate') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Select B2B Client</label>
                    <select name="account_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-semibold focus:border-indigo-500">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: {{ number_format($acc->credit_balance) }} credits)</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Key Label</label>
                        <input type="text" name="name" required placeholder="Production Casino Gateway" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-medium focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Rate Limit (req/min)</label>
                        <input type="number" name="rate_limit" value="300" min="10" max="5000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-mono focus:border-indigo-500">
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs py-3 rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                    <span>Generate New API Key</span>
                    <span>&rarr;</span>
                </button>
            </form>
        </div>

        <!-- 2. Create B2B Account Form -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
            <div class="flex items-center space-x-2 text-emerald-600 mb-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <h3 class="text-base font-extrabold text-slate-900">Register New B2B Client</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">Onboard a new casino, sportsbook or aggregator account.</p>

            <form action="{{ route('admin.accounts.create') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Company / Casino Name</label>
                    <input type="text" name="name" required placeholder="Nova Gaming Holdings" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-medium focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Initial Credits</label>
                        <input type="number" name="initial_credits" value="1000" min="0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-mono focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Status</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-semibold focus:border-emerald-500">
                            <option value="active">Active</option>
                            <option value="trial">Trial</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs py-3 rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                    <span>Create B2B Account</span>
                    <span>&rarr;</span>
                </button>
            </form>
        </div>
    </div>

    <!-- B2B Accounts Table -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
        <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center justify-between">
            <span>B2B Client Directory & Balances</span>
            <span class="text-xs text-slate-400 font-normal">Total Clients: {{ $accounts->count() }}</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold text-[11px]">
                        <th class="py-3 px-3">Company</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Credit Balance</th>
                        <th class="py-3 px-3">API Keys</th>
                        <th class="py-3 px-3">Scoring Rules & Tags</th>
                        <th class="py-3 px-3 text-right">Top Up / Deduct</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($accounts as $acc)
                    @php
                        $customRulesCount = count($acc->scoring_rules['custom_rules'] ?? []);
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-3">
                            <div class="font-extrabold text-slate-900 text-sm">{{ $acc->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">ID: {{ $acc->id }}</div>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $acc->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $acc->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="font-black text-sm font-mono text-emerald-700">{{ number_format($acc->credit_balance) }}</span>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="font-mono text-xs font-bold text-slate-600">{{ $acc->apiClients->count() }} keys</span>
                        </td>
                        <td class="py-3.5 px-3">
                            <button onclick="openRulesModal('{{ $acc->id }}', '{{ addslashes($acc->name) }}', {{ json_encode($acc->scoring_rules ?? (object)[]) }})" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs transition border border-indigo-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                <span>Custom Rules ({{ $customRulesCount }})</span>
                            </button>
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            <form action="{{ route('admin.credits.adjust') }}" method="POST" class="inline-flex items-center space-x-2">
                                @csrf
                                <input type="hidden" name="account_id" value="{{ $acc->id }}">
                                <input type="number" name="delta" placeholder="+/- Credits" required class="w-24 bg-slate-50 border border-slate-300 rounded-lg px-2 py-1 text-xs font-mono text-right">
                                <input type="text" name="reason" placeholder="Reason" required class="w-32 bg-slate-50 border border-slate-300 rounded-lg px-2 py-1 text-xs">
                                <button type="submit" class="px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition shadow-sm">
                                    Apply
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Custom Scoring Rules -->
    <div id="rulesModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-3xl w-full border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm" id="modalClientName">Configure Scoring Rules</h4>
                        <p class="text-[11px] text-slate-300">Customize VIP thresholds, conditions, and tags for this client</p>
                    </div>
                </div>
                <button onclick="closeRulesModal()" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
            </div>

            <form id="rulesForm" method="POST" class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                @csrf
                <!-- Tier Thresholds -->
                <div>
                    <h5 class="text-xs font-bold uppercase text-slate-600 mb-3 flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        <span>Tier Balance Thresholds (USD)</span>
                    </h5>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Super VIP / Shark</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-slate-400 text-xs">$</span>
                                <input type="number" id="superVipInput" name="super_vip_threshold_usd" placeholder="400000" class="w-full pl-6 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold">
                            </div>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Potential VIP</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-slate-400 text-xs">$</span>
                                <input type="number" id="potentialVipInput" name="potential_vip_threshold_usd" placeholder="100000" class="w-full pl-6 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold">
                            </div>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3">
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">High Value Player</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-slate-400 text-xs">$</span>
                                <input type="number" id="highValueInput" name="high_value_threshold_usd" placeholder="25000" class="w-full pl-6 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Condition Rules -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="text-xs font-bold uppercase text-slate-600 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>Custom Tag & Status Rules (e.g., $200k &rarr; SUPER_SHARK)</span>
                        </h5>
                        <button type="button" onclick="addRuleRow()" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg text-xs transition border border-emerald-200 flex items-center space-x-1">
                            <span>+ Add Rule</span>
                        </button>
                    </div>

                    <div id="rulesContainer" class="space-y-3">
                        <!-- Dynamic rows rendered by JS -->
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeRulesModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center space-x-1.5">
                        <span>Save Scoring Rules</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- All API Keys List -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
        <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center justify-between">
            <span>Provisioned API Keys</span>
            <span class="text-xs text-slate-400 font-normal">Active & Revoked</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold text-[11px]">
                        <th class="py-3 px-3">Client / Label</th>
                        <th class="py-3 px-3">API Key ID</th>
                        <th class="py-3 px-3">Rate Limit</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Last Used</th>
                        <th class="py-3 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($apiClients as $client)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-3">
                            <div class="font-bold text-slate-900">{{ $client->account->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $client->name }}</div>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-indigo-600">
                            {{ $client->key_id }}
                        </td>
                        <td class="py-3 px-3 font-mono text-slate-600">
                            {{ $client->rate_limit_per_minute }} req/min
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $client->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $client->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-slate-500 font-mono">
                            {{ $client->last_used_at ? $client->last_used_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="py-3 px-3 text-right">
                            @if($client->status === 'active')
                            <form action="{{ route('admin.api-keys.revoke', ['id' => $client->id]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to revoke this API key?');">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline">
                                    Revoke Key
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-xs">Revoked</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400">No keys provisioned yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openRulesModal(accountId, accountName, scoringRules) {
    document.getElementById('modalClientName').innerText = 'Configure Scoring Rules: ' + accountName;
    document.getElementById('rulesForm').action = '/admin/accounts/' + accountId + '/scoring-rules';
    
    // Thresholds
    document.getElementById('superVipInput').value = scoringRules.super_vip_threshold_usd || '';
    document.getElementById('potentialVipInput').value = scoringRules.potential_vip_threshold_usd || '';
    document.getElementById('highValueInput').value = scoringRules.high_value_threshold_usd || '';
    
    // Custom condition rules
    const container = document.getElementById('rulesContainer');
    container.innerHTML = '';
    
    const rules = scoringRules.custom_rules || scoringRules.rules || [];
    if (rules.length === 0) {
        addRuleRow();
    } else {
        rules.forEach(rule => {
            addRuleRow(rule);
        });
    }
    
    document.getElementById('rulesModal').classList.remove('hidden');
}

function closeRulesModal() {
    document.getElementById('rulesModal').classList.add('hidden');
}

function addRuleRow(rule = {}) {
    const container = document.getElementById('rulesContainer');
    const rowId = 'rule_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    
    const div = document.createElement('div');
    div.id = rowId;
    div.className = 'grid grid-cols-12 gap-2 bg-slate-50 border border-slate-200 rounded-2xl p-3 items-center';
    
    div.innerHTML = `
        <div class="col-span-3">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Metric</label>
            <select name="metrics[]" class="w-full bg-white border border-slate-300 rounded-xl px-2 py-1.5 text-xs font-semibold focus:border-indigo-500">
                <option value="visible_balance_usd" ${rule.metric === 'visible_balance_usd' ? 'selected' : ''}>Visible Balance (USD)</option>
                <option value="gambling_flow_365d_usd" ${rule.metric === 'gambling_flow_365d_usd' ? 'selected' : ''}>Gambling Flow (USD)</option>
                <option value="turnover_365d_usd" ${rule.metric === 'turnover_365d_usd' ? 'selected' : ''}>Turnover 365d (USD)</option>
                <option value="transactions_count" ${rule.metric === 'transactions_count' ? 'selected' : ''}>Transactions Count</option>
                <option value="avg_tx_size_usd" ${rule.metric === 'avg_tx_size_usd' ? 'selected' : ''}>Avg Tx Size (USD)</option>
            </select>
        </div>
        <div class="col-span-1">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Op</label>
            <select name="operators[]" class="w-full bg-white border border-slate-300 rounded-xl px-1 py-1.5 text-xs font-mono font-bold focus:border-indigo-500">
                <option value=">=" ${rule.operator === '>=' ? 'selected' : ''}>&ge;</option>
                <option value=">" ${rule.operator === '>' ? 'selected' : ''}>&gt;</option>
                <option value="<=" ${rule.operator === '<=' ? 'selected' : ''}>&le;</option>
                <option value="<" ${rule.operator === '<' ? 'selected' : ''}>&lt;</option>
                <option value="==" ${rule.operator === '==' ? 'selected' : ''}>=</option>
            </select>
        </div>
        <div class="col-span-3">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Value (Threshold)</label>
            <input type="number" name="values[]" value="${rule.value !== undefined ? rule.value : ''}" placeholder="200000" class="w-full bg-white border border-slate-300 rounded-xl px-2 py-1.5 text-xs font-mono font-bold focus:border-indigo-500">
        </div>
        <div class="col-span-3">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Custom Tag</label>
            <input type="text" name="tags[]" value="${rule.tag || ''}" placeholder="SUPER_SHARK" class="w-full bg-white border border-slate-300 rounded-xl px-2 py-1.5 text-xs font-bold uppercase text-indigo-700 focus:border-indigo-500">
        </div>
        <div class="col-span-2 flex items-end space-x-1">
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">VIP Tier</label>
                <select name="tiers[]" class="w-full bg-white border border-slate-300 rounded-xl px-1.5 py-1.5 text-[11px] font-bold focus:border-indigo-500">
                    <option value="">(None)</option>
                    <option value="super_vip" ${rule.tier === 'super_vip' ? 'selected' : ''}>SUPER_VIP</option>
                    <option value="potential_vip" ${rule.tier === 'potential_vip' ? 'selected' : ''}>POTENTIAL_VIP</option>
                    <option value="high_value" ${rule.tier === 'high_value' ? 'selected' : ''}>HIGH_VALUE</option>
                    <option value="good_player" ${rule.tier === 'good_player' ? 'selected' : ''}>GOOD_PLAYER</option>
                </select>
            </div>
            <button type="button" onclick="document.getElementById('${rowId}').remove()" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>
    `;
    
    container.appendChild(div);
}
</script>
@endsection
