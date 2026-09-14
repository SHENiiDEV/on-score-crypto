@extends('layout')

@section('title', 'CEX & Casino Entity Database — On-Score Admin')

@section('content')
<div class="space-y-8 pb-12">
    
    <!-- Top Header & Breadcrumbs (Stripe Style) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 mb-1.5">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#635bff] transition">Admin Hub</a>
                <span>/</span>
                <span class="text-[#635bff]">Entity Cluster Registry</span>
            </div>
            <h1 class="text-3xl font-black text-[#0a2540] tracking-tight font-sans">
                Entity Intelligence Database
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">
                Labeled addresses for on-chain counterparty attribution, deposit router detection, and custodial CEX pool filtering.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:border-[#635bff]/40 hover:bg-slate-50 text-[#0a2540] font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <span>&larr; Return to Admin</span>
            </a>
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-[#0a2540] hover:bg-[#635bff] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center space-x-1.5">
                <span>Scoring Console</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Quick Search & Category Filters -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="entitySearch" onkeyup="filterEntities()" placeholder="Search entities, addresses, tags..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2 text-xs font-medium text-slate-900 focus:border-[#635bff] focus:bg-white focus:outline-none transition">
        </div>

        <div class="flex items-center space-x-2 text-xs font-bold text-slate-500 overflow-x-auto">
            <button type="button" onclick="filterCategory('all')" id="catBtn-all" class="px-3.5 py-1.5 rounded-lg bg-[#0a2540] text-white transition shrink-0">
                All ({{ $entities->count() }})
            </button>
            <button type="button" onclick="filterCategory('cex')" id="catBtn-cex" class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition shrink-0">
                CEX Exchanges
            </button>
            <button type="button" onclick="filterCategory('gambling')" id="catBtn-gambling" class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition shrink-0">
                Gambling & Casinos
            </button>
            <button type="button" onclick="filterCategory('dex')" id="catBtn-dex" class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition shrink-0">
                DEX & DeFi
            </button>
        </div>
    </div>

    <!-- Register New Address Card -->
    <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-[#635bff] border border-indigo-200 flex items-center justify-center font-black text-base">
                +
            </div>
            <div>
                <h3 class="text-base font-extrabold text-[#0a2540]">Add Labeled Address to Database</h3>
                <p class="text-xs text-slate-500">Register a new exchange hot wallet, casino deposit router, or smart contract address.</p>
            </div>
        </div>

        <form action="{{ route('admin.entities.add-address') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Entity Brand</label>
                    <select name="entity_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-semibold focus:border-[#635bff] focus:bg-white focus:outline-none">
                        @foreach($entities as $ent)
                            <option value="{{ $ent->id }}">
                                {{ $ent->name }} ({{ strtoupper($ent->category) }}{{ $ent->is_custodial_cex ? ' • CEX Pool Filter' : '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Network</label>
                    <select name="network" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-semibold focus:border-[#635bff] focus:bg-white focus:outline-none">
                        <option value="tron">TRON (TRC20)</option>
                        <option value="ethereum">Ethereum (ERC20)</option>
                        <option value="bsc">BNB Chain (BEP20)</option>
                        <option value="solana">Solana (SPL)</option>
                        <option value="bitcoin">Bitcoin (BTC)</option>
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Wallet Address / Contract</label>
                    <input type="text" name="address" required placeholder="T... or 0x..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-mono font-bold text-slate-900 focus:border-[#635bff] focus:bg-white focus:outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Confidence Score</label>
                    <input type="number" step="0.01" min="0.1" max="1.0" name="confidence" value="0.99" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-mono font-bold text-slate-900 focus:border-[#635bff] focus:bg-white focus:outline-none">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                <div class="flex-1 max-w-xl">
                    <input type="text" name="label" placeholder="Label description (e.g. Bybit Sweeper Pool 5 or Stake TRON Deposit Receiver)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:border-[#635bff] focus:bg-white focus:outline-none">
                </div>
                <button type="submit" class="bg-[#635bff] hover:bg-[#5851ea] text-white font-extrabold text-xs px-6 py-2.5 rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                    <span>Save Labeled Address</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Entities Cards Matrix -->
    <div id="entitiesContainer" class="space-y-6">
        @foreach($entities as $entity)
        <div class="entity-card bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-sm space-y-5" data-category="{{ $entity->category }}" data-name="{{ strtolower($entity->name) }}">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-2xl bg-slate-100 text-[#0a2540] border border-slate-200 flex items-center justify-center font-black text-sm">
                        {{ substr($entity->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-base font-black text-[#0a2540]">{{ $entity->name }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $entity->category === 'cex' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($entity->category === 'gambling' ? 'bg-indigo-50 text-[#635bff] border border-indigo-200' : 'bg-slate-100 text-slate-700') }}">
                                {{ $entity->category }} • {{ $entity->subtype }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">slug: {{ $entity->slug }} • status: {{ $entity->status }}</div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    @if($entity->is_custodial_cex)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 flex items-center space-x-1">
                            <span>🛡️</span>
                            <span>CEX Sweeper Pool Filter Active</span>
                        </span>
                    @endif
                    <span class="px-3 py-1 bg-slate-100 rounded-xl text-xs font-mono font-bold text-slate-600">
                        {{ $entity->addresses->count() }} addresses
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 uppercase font-extrabold text-[10px] tracking-wider border-b border-slate-100">
                            <th class="py-2 px-2">Network</th>
                            <th class="py-2 px-2">Address</th>
                            <th class="py-2 px-2">Label / Description</th>
                            <th class="py-2 px-2">Source</th>
                            <th class="py-2 px-2 text-right">Confidence</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($entity->addresses as $addr)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-2.5 px-2">
                                @php
                                    $netBadge = match($addr->network) {
                                        'ethereum' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'tron' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'bsc' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'solana' => 'bg-teal-50 text-teal-700 border-teal-200',
                                        'bitcoin' => 'bg-orange-50 text-orange-700 border-orange-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-extrabold uppercase border {{ $netBadge }}">
                                    {{ $addr->network }}
                                </span>
                            </td>
                            <td class="py-2.5 px-2 font-mono font-bold text-slate-900 text-xs select-all cursor-pointer hover:text-[#635bff]" onclick="navigator.clipboard.writeText('{{ $addr->address }}'); alert('Address copied!');">
                                {{ $addr->address }}
                            </td>
                            <td class="py-2.5 px-2 text-slate-600 font-medium">{{ $addr->label }}</td>
                            <td class="py-2.5 px-2 text-slate-400 font-mono text-[10px]">{{ $addr->source }}</td>
                            <td class="py-2.5 px-2 text-right font-mono font-black text-emerald-600">{{ ($addr->confidence) * 100 }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400 font-medium">No labeled addresses registered for this entity.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

</div>

<script>
let currentCategory = 'all';

function filterCategory(cat) {
    currentCategory = cat;
    ['all', 'cex', 'gambling', 'dex'].forEach(c => {
        const btn = document.getElementById('catBtn-' + c);
        if (c === cat) {
            btn.className = "px-3.5 py-1.5 rounded-lg bg-[#0a2540] text-white transition shrink-0";
        } else {
            btn.className = "px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 transition shrink-0";
        }
    });
    filterEntities();
}

function filterEntities() {
    const search = document.getElementById('entitySearch').value.toLowerCase();
    const cards = document.querySelectorAll('.entity-card');

    cards.forEach(card => {
        const cat = card.getAttribute('data-category');
        const text = card.innerText.toLowerCase();
        const matchesCat = (currentCategory === 'all' || cat === currentCategory);
        const matchesSearch = text.includes(search);

        if (matchesCat && matchesSearch) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endsection
