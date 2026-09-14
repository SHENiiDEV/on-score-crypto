@extends('layout')

@section('title', 'Admin Panel — Stripe-Grade Client & Intelligence Hub')

@section('content')
<div class="space-y-8 pb-12">
    
    <!-- Top Header & Breadcrumbs (Stripe Dashboard Style) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 mb-1.5">
                <span>Infrastructure</span>
                <span>/</span>
                <span class="text-[#635bff]">Enterprise Gateway Admin</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Live Production
                </span>
            </div>
            <h1 class="text-3xl font-black text-[#0a2540] tracking-tight font-sans">
                On-Score Admin Dashboard
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Client onboarding, API credential lifecycle, on-chain scoring audit, and entity cluster management.
            </p>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.entities') }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:border-[#635bff]/40 hover:bg-slate-50 text-[#0a2540] font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-[#635bff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>CEX & Casino Registry</span>
            </a>
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-[#0a2540] hover:bg-[#635bff] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center space-x-1.5">
                <span>Scoring Console</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Alert for newly generated API Key (One-time Secret Display in Stripe Style) -->
    @if(session('new_api_key'))
        @php $keyData = session('new_api_key'); @endphp
        <div class="p-6 rounded-3xl bg-gradient-to-r from-amber-50/90 via-orange-50/50 to-amber-50/90 border-2 border-amber-300 shadow-xl space-y-4 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center space-x-3.5 text-amber-950">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-lg shadow-md shadow-amber-500/20 shrink-0">🔑</div>
                <div>
                    <h3 class="text-base font-extrabold text-[#0a2540]">New API Key Successfully Provisioned!</h3>
                    <p class="text-xs text-amber-900 font-medium mt-0.5">
                        Account: <strong class="text-slate-900">{{ $keyData['account_name'] }}</strong> • Label: <strong class="text-slate-900">{{ $keyData['key_name'] }}</strong>
                    </p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-amber-200 shadow-sm space-y-4">
                <div>
                    <label class="block text-[11px] font-extrabold uppercase text-slate-400 tracking-wider">API Key ID (Public Identifier):</label>
                    <div class="flex items-center space-x-2 mt-1.5">
                        <input type="text" id="copyKeyId" readonly value="{{ $keyData['key_id'] }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-mono text-[#0a2540] font-bold select-all focus:outline-none">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $keyData['key_id'] }}'); alert('API Key ID copied!');" class="px-4 py-2.5 bg-[#0a2540] hover:bg-[#635bff] text-white text-xs font-bold rounded-xl transition shadow-sm shrink-0">
                            Copy ID
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold uppercase text-rose-600 tracking-wider">API Secret Key (Secret Token — One-Time Reveal):</label>
                    <div class="flex items-center space-x-2 mt-1.5">
                        <input type="text" id="copySecret" readonly value="{{ $keyData['secret'] }}" class="w-full bg-rose-50/60 border border-rose-300 rounded-xl px-3.5 py-2.5 text-xs font-mono text-rose-700 font-bold select-all focus:outline-none">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $keyData['secret'] }}'); alert('API Secret copied!');" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm shrink-0">
                            Copy Secret
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2 text-xs text-amber-900 font-medium">
                <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Store this secret key securely. For security, only the SHA-256 hash is saved in the database.</span>
            </div>
        </div>
    @endif

    <!-- Top KPI Metrics Grid (Stripe Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Metric 1: B2B Clients -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-extrabold uppercase text-slate-400">
                <span>B2B Clients</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-3xl font-black text-[#0a2540] font-mono mt-2">{{ $accounts->count() }}</div>
            <div class="text-xs font-semibold text-emerald-600 mt-1 flex items-center space-x-1">
                <span>Active Operators</span>
            </div>
        </div>

        <!-- Metric 2: Total API Keys -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-extrabold uppercase text-slate-400">
                <span>Live API Keys</span>
                <span class="w-2 h-2 rounded-full bg-[#635bff]"></span>
            </div>
            <div class="text-3xl font-black text-[#635bff] font-mono mt-2">{{ $apiClients->count() }}</div>
            <div class="text-xs font-semibold text-slate-500 mt-1">
                Active: <strong class="text-slate-800">{{ $apiClients->where('status', 'active')->count() }}</strong> • Revoked: {{ $apiClients->where('status', 'revoked')->count() }}
            </div>
        </div>

        <!-- Metric 3: Total Analyses -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-extrabold uppercase text-slate-400">
                <span>Scored Wallets</span>
                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
            </div>
            <div class="text-3xl font-black text-[#0a2540] font-mono mt-2">{{ number_format($totalAnalyses) }}</div>
            <div class="text-xs font-semibold text-slate-500 mt-1">
                Multi-Chain Live Requests
            </div>
        </div>

        <!-- Metric 4: Credits Provisioned -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-extrabold uppercase text-slate-400">
                <span>Total Credits Issued</span>
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            </div>
            <div class="text-3xl font-black text-emerald-600 font-mono mt-2">{{ number_format($totalCreditsIssued) }}</div>
            <div class="text-xs font-semibold text-slate-500 mt-1">
                Ledger Provisioned
            </div>
        </div>
    </div>

    <!-- Quick Search & Action Bar -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="adminQuickSearch" onkeyup="filterAdminTables()" placeholder="Filter clients, addresses, keys..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2 text-xs font-medium text-slate-900 focus:border-[#635bff] focus:bg-white focus:outline-none transition">
        </div>

        <div class="flex items-center space-x-2 text-xs font-bold text-slate-500 overflow-x-auto">
            <a href="#section-clients" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition shrink-0">B2B Clients</a>
            <a href="#section-analyses" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition shrink-0">Live Analyses</a>
            <a href="#section-keys" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition shrink-0">API Keys</a>
            <a href="#section-ledger" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition shrink-0">Audit Ledger</a>
        </div>
    </div>

    <!-- Stripe-Style Action Panels (Client Registration & Key Provisioning) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- 1. Register New B2B Client -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center font-black text-base">
                    +
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#0a2540]">Register New B2B Client</h3>
                    <p class="text-xs text-slate-500">Onboard a casino operator, crypto gateway or sports betting platform.</p>
                </div>
            </div>

            <form action="{{ route('admin.accounts.create') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Company / Operator Name</label>
                    <input type="text" name="name" required placeholder="e.g. Apex Gaming Group, Roobet Global" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-medium focus:border-[#635bff] focus:bg-white focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Initial Credit Pack</label>
                        <input type="number" name="initial_credits" value="1000" min="0" max="1000000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-mono font-bold text-slate-900 focus:border-[#635bff] focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Account Status</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-semibold focus:border-[#635bff] focus:bg-white focus:outline-none">
                            <option value="active">Active (Full Access)</option>
                            <option value="trial">Trial (Sandbox)</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#0a2540] hover:bg-[#635bff] text-white font-extrabold text-xs py-3 rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                    <span>Create Client Account</span>
                    <span>&rarr;</span>
                </button>
            </form>
        </div>

        <!-- 2. Provision API Key -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-[#635bff] border border-indigo-200 flex items-center justify-center font-black text-base">
                    🔑
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#0a2540]">Provision API Key for Client</h3>
                    <p class="text-xs text-slate-500">Generate cryptographically secure live credentials linked to client account.</p>
                </div>
            </div>

            <form action="{{ route('admin.api-keys.generate') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Select B2B Client Account</label>
                    <select name="account_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-semibold focus:border-[#635bff] focus:bg-white focus:outline-none">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: {{ number_format($acc->credit_balance) }} credits)</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Key Label</label>
                        <input type="text" name="name" required placeholder="Production Casino Core" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-medium focus:border-[#635bff] focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Rate Limit (req/min)</label>
                        <input type="number" name="rate_limit" value="300" min="10" max="5000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-mono font-bold text-slate-900 focus:border-[#635bff] focus:bg-white focus:outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#635bff] hover:bg-[#5851ea] text-white font-extrabold text-xs py-3 rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                    <span>Generate API Key Pair</span>
                    <span>&rarr;</span>
                </button>
            </form>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- SECTION 1: LIVE SCORED WALLETS (SYSTEM-WIDE) -->
    <!-- ========================================== -->
    <div id="section-analyses" class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-[#0a2540] flex items-center space-x-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Analyzed & Scored Wallets</span>
                </h3>
                <p class="text-xs text-slate-500">Real-time incoming scoring queries triggered via REST API and Web Console.</p>
            </div>
            <span class="text-xs font-mono text-slate-400 font-semibold">{{ $recentAnalyses->count() }} recent evaluations</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs search-table">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-3">Network / Wallet Address</th>
                        <th class="py-3 px-3">B2B Client / Key</th>
                        <th class="py-3 px-3">Score & VIP Segment</th>
                        <th class="py-3 px-3">Gambling Flow (365d)</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Time</th>
                        <th class="py-3 px-3 text-right">Dossier</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($recentAnalyses as $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-3">
                            <div class="flex items-center space-x-2">
                                @php
                                    $netBadge = match($item->network) {
                                        'ethereum' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'tron' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'bsc' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'solana' => 'bg-teal-50 text-teal-700 border-teal-200',
                                        'bitcoin' => 'bg-orange-50 text-orange-700 border-orange-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-extrabold uppercase border {{ $netBadge }}">
                                    {{ $item->network }}
                                </span>
                                <span class="font-mono font-bold text-slate-900 select-all cursor-pointer hover:text-[#635bff]" title="{{ $item->address }}" onclick="navigator.clipboard.writeText('{{ $item->address }}'); alert('Address copied!');">
                                    {{ Str::limit($item->address, 16) }}
                                </span>
                            </div>
                            @if($item->external_player_id)
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">Player: {{ $item->external_player_id }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-3">
                            <div class="font-bold text-slate-900">{{ $item->account->name ?? 'System' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">
                                {{ $item->apiClient ? $item->apiClient->name : 'Web Console' }}
                            </div>
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
                                    <span class="font-black text-sm font-mono text-slate-900">{{ $item->score_value }}/100</span>
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full border {{ $badgeColor }}">
                                        {{ strtoupper(str_replace('_', ' ', $item->segment)) }}
                                    </span>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
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
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $item->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 text-slate-500 font-mono text-[11px]">
                            {{ $item->created_at->diffForHumans() }}
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            <a href="{{ route('dashboard.report', ['id' => $item->id]) }}" class="inline-flex items-center space-x-1 px-3 py-1 bg-slate-100 hover:bg-[#635bff] hover:text-white text-slate-800 font-extrabold rounded-lg transition text-xs">
                                <span>Report</span>
                                <span>&rarr;</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-medium">
                            No analyses recorded yet. Send a query via API or run a test in the scoring console.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SECTION 2: B2B CLIENT ACCOUNTS             -->
    <!-- ========================================== -->
    <div id="section-clients" class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-[#0a2540] flex items-center space-x-2">
                    <span>B2B Client Accounts & Credit Allocations</span>
                </h3>
                <p class="text-xs text-slate-500">Manage client balances, webhook subscriptions, and custom scoring rule thresholds.</p>
            </div>
            <span class="text-xs font-mono text-slate-400 font-semibold">{{ $accounts->count() }} active clients</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs search-table">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-3">Client Name</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Credit Balance</th>
                        <th class="py-3 px-3">API Keys</th>
                        <th class="py-3 px-3">Scoring Rules</th>
                        <th class="py-3 px-3 text-right">Adjust Credits</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @foreach($accounts as $acc)
                    @php
                        $customRulesCount = count($acc->scoring_rules['custom_rules'] ?? []);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-3">
                            <div class="font-extrabold text-slate-900 text-sm">{{ $acc->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">slug: {{ $acc->slug }} • ID: {{ $acc->id }}</div>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $acc->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $acc->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="font-black text-sm font-mono text-emerald-700">{{ number_format($acc->credit_balance) }}</span>
                            <span class="text-[10px] text-slate-400">credits</span>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">{{ $acc->apiClients->count() }} active</span>
                        </td>
                        <td class="py-3.5 px-3">
                            <button onclick="openRulesModal('{{ $acc->id }}', '{{ addslashes($acc->name) }}', {{ json_encode($acc->scoring_rules ?? (object)[]) }})" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-[#635bff] hover:text-white text-[#635bff] font-extrabold rounded-xl text-xs transition border border-indigo-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                <span>Scoring Rules ({{ $customRulesCount }})</span>
                            </button>
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            <form action="{{ route('admin.credits.adjust') }}" method="POST" class="inline-flex items-center space-x-2">
                                @csrf
                                <input type="hidden" name="account_id" value="{{ $acc->id }}">
                                <input type="number" name="delta" placeholder="+/- Credits" required class="w-24 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-mono text-right focus:border-[#635bff] focus:outline-none">
                                <input type="text" name="reason" placeholder="Reason (e.g. Invoiced top-up)" required class="w-36 bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1 text-xs focus:border-[#635bff] focus:outline-none">
                                <button type="submit" class="px-3 py-1 bg-[#0a2540] hover:bg-[#635bff] text-white font-bold text-xs rounded-lg transition shadow-sm">
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

    <!-- ========================================== -->
    <!-- SECTION 3: PROVISIONED API KEYS            -->
    <!-- ========================================== -->
    <div id="section-keys" class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-[#0a2540] flex items-center space-x-2">
                    <span>Provisioned API Credentials</span>
                </h3>
                <p class="text-xs text-slate-500">Live API Key IDs, rate limits, and revocation controls.</p>
            </div>
            <span class="text-xs font-mono text-slate-400 font-semibold">{{ $apiClients->count() }} keys</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs search-table">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-extrabold text-[10px] tracking-wider">
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
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-3">
                            <div class="font-extrabold text-slate-900">{{ $client->account->name }}</div>
                            <div class="text-[11px] text-slate-500 font-medium">{{ $client->name }}</div>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-[#635bff]">
                            <span class="select-all cursor-pointer hover:underline" onclick="navigator.clipboard.writeText('{{ $client->key_id }}'); alert('Key copied!');">
                                {{ $client->key_id }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-mono text-slate-600 font-bold">
                            {{ $client->rate_limit_per_minute }} req/min
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $client->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $client->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-slate-500 font-mono text-[11px]">
                            {{ $client->last_used_at ? $client->last_used_at->diffForHumans() : 'Never used' }}
                        </td>
                        <td class="py-3 px-3 text-right">
                            @if($client->status === 'active')
                            <form action="{{ route('admin.api-keys.revoke', ['id' => $client->id]) }}" method="POST" class="inline" onsubmit="return confirm('Revoke this API key? Connected applications will lose access immediately.');">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline px-2.5 py-1 rounded-lg hover:bg-rose-50 transition">
                                    Revoke
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-xs font-mono">Revoked</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 font-medium">No API keys provisioned yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SECTION 4: CREDIT AUDIT LEDGER             -->
    <!-- ========================================== -->
    <div id="section-ledger" class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-[#0a2540] flex items-center space-x-2">
                    <span>Credit Billing Audit Ledger</span>
                </h3>
                <p class="text-xs text-slate-500">Immutable ledger tracking top-ups, scoring deductions, and manual adjustments.</p>
            </div>
            <span class="text-xs font-mono text-slate-400 font-semibold">{{ $recentLedger->count() }} recent ledger rows</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs search-table">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-3">B2B Account</th>
                        <th class="py-3 px-3">Delta Amount</th>
                        <th class="py-3 px-3">Type</th>
                        <th class="py-3 px-3">Balance After</th>
                        <th class="py-3 px-3">Reason / Ref</th>
                        <th class="py-3 px-3">Actor</th>
                        <th class="py-3 px-3 text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($recentLedger as $entry)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-3 font-extrabold text-slate-900">
                            {{ $entry->account->name ?? 'System' }}
                        </td>
                        <td class="py-3 px-3 font-mono font-black {{ $entry->delta > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $entry->delta > 0 ? '+' : '' }}{{ number_format($entry->delta) }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase {{ $entry->type === 'topup' ? 'bg-emerald-50 text-emerald-700' : ($entry->type === 'deduction' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $entry->type }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-slate-900">
                            {{ number_format($entry->balance_after) }}
                        </td>
                        <td class="py-3 px-3 text-slate-600">
                            {{ $entry->reason }}
                            @if($entry->reference_id)
                                <div class="text-[10px] font-mono text-slate-400">Ref: {{ $entry->reference_id }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-3 font-mono text-[11px] text-slate-500">
                            {{ $entry->actor }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-[11px] text-slate-400">
                            {{ $entry->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-medium">No ledger entries recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal for Custom Scoring Rules (Stripe Style) -->
<div id="rulesModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-3xl w-full border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="bg-[#0a2540] px-6 py-4 flex items-center justify-between text-white">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <h4 class="font-black text-sm text-white" id="modalClientName">Configure Scoring Rules</h4>
                    <p class="text-[11px] text-slate-300">Customize VIP thresholds, conditions, and tags for this client</p>
                </div>
            </div>
            <button onclick="closeRulesModal()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <form id="rulesForm" method="POST" class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
            @csrf
            <!-- Tier Thresholds -->
            <div>
                <h5 class="text-xs font-extrabold uppercase text-[#0a2540] mb-3 flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-[#635bff]"></span>
                    <span>Tier Balance Thresholds (USD)</span>
                </h5>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5">
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Super VIP / Shark</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-400 text-xs">$</span>
                            <input type="number" id="superVipInput" name="super_vip_threshold_usd" placeholder="400000" class="w-full pl-6 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold focus:border-[#635bff] focus:outline-none">
                        </div>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5">
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Potential VIP</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-400 text-xs">$</span>
                            <input type="number" id="potentialVipInput" name="potential_vip_threshold_usd" placeholder="100000" class="w-full pl-6 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold focus:border-[#635bff] focus:outline-none">
                        </div>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5">
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">High Value Player</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-400 text-xs">$</span>
                            <input type="number" id="highValueInput" name="high_value_threshold_usd" placeholder="25000" class="w-full pl-6 pr-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold focus:border-[#635bff] focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Condition Rules -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h5 class="text-xs font-extrabold uppercase text-[#0a2540] flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Custom Tag & Status Rules (e.g., $200k &rarr; SUPER_SHARK)</span>
                    </h5>
                    <button type="button" onclick="addRuleRow()" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-extrabold rounded-xl text-xs transition border border-emerald-200 flex items-center space-x-1">
                        <span>+ Add Rule</span>
                    </button>
                </div>

                <div id="rulesContainer" class="space-y-3">
                    <!-- Dynamic rows rendered by JS -->
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeRulesModal()" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900 transition">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-[#635bff] hover:bg-[#5851ea] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center space-x-1.5">
                    <span>Save Scoring Rules</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
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
    div.className = 'grid grid-cols-12 gap-2 bg-slate-50 border border-slate-200 rounded-2xl p-3.5 items-center';
    
    div.innerHTML = `
        <div class="col-span-3">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Metric</label>
            <select name="metrics[]" class="w-full bg-white border border-slate-300 rounded-xl px-2 py-1.5 text-xs font-semibold focus:border-[#635bff] focus:outline-none">
                <option value="visible_balance_usd" ${rule.metric === 'visible_balance_usd' ? 'selected' : ''}>Visible Balance (USD)</option>
                <option value="gambling_flow_365d_usd" ${rule.metric === 'gambling_flow_365d_usd' ? 'selected' : ''}>Gambling Flow (USD)</option>
                <option value="turnover_365d_usd" ${rule.metric === 'turnover_365d_usd' ? 'selected' : ''}>Turnover 365d (USD)</option>
                <option value="transactions_count" ${rule.metric === 'transactions_count' ? 'selected' : ''}>Transactions Count</option>
                <option value="avg_tx_size_usd" ${rule.metric === 'avg_tx_size_usd' ? 'selected' : ''}>Avg Tx Size (USD)</option>
            </select>
        </div>
        <div class="col-span-1">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Op</label>
            <select name="operators[]" class="w-full bg-white border border-slate-300 rounded-xl px-1 py-1.5 text-xs font-mono font-bold focus:border-[#635bff] focus:outline-none">
                <option value=">=" ${rule.operator === '>=' ? 'selected' : ''}>&ge;</option>
                <option value=">" ${rule.operator === '>' ? 'selected' : ''}>&gt;</option>
                <option value="<=" ${rule.operator === '<=' ? 'selected' : ''}>&le;</option>
                <option value="<" ${rule.operator === '<' ? 'selected' : ''}>&lt;</option>
                <option value="==" ${rule.operator === '==' ? 'selected' : ''}>=</option>
            </select>
        </div>
        <div class="col-span-3">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Value (Threshold)</label>
            <input type="number" name="values[]" value="${rule.value !== undefined ? rule.value : ''}" placeholder="200000" class="w-full bg-white border border-slate-300 rounded-xl px-2 py-1.5 text-xs font-mono font-bold focus:border-[#635bff] focus:outline-none">
        </div>
        <div class="col-span-3">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Custom Tag</label>
            <input type="text" name="tags[]" value="${rule.tag || ''}" placeholder="SUPER_SHARK" class="w-full bg-white border border-slate-300 rounded-xl px-2 py-1.5 text-xs font-extrabold uppercase text-[#635bff] focus:border-[#635bff] focus:outline-none">
        </div>
        <div class="col-span-2 flex items-end space-x-1">
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">VIP Tier</label>
                <select name="tiers[]" class="w-full bg-white border border-slate-300 rounded-xl px-1.5 py-1.5 text-[11px] font-bold focus:border-[#635bff] focus:outline-none">
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

function filterAdminTables() {
    const input = document.getElementById('adminQuickSearch').value.toLowerCase();
    const tables = document.querySelectorAll('.search-table');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    });
}
</script>
@endsection
