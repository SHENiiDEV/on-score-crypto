@extends('layout')

@section('title', 'CEX & Casino Entity Database — On-Score Admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">&larr; Back to Admin Dashboard</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center space-x-3">
                <span>Entity Intelligence Database (CEX, Casino, DEX)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Labeled addresses for on-chain counterparty attribution and custodial CEX pool filtering.</p>
        </div>
    </div>

    <!-- Add Address Form -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
        <h3 class="text-base font-extrabold text-slate-900 mb-2 flex items-center space-x-2">
            <span class="text-indigo-600">+</span>
            <span>Add Labeled Address to Database</span>
        </h3>
        <p class="text-xs text-slate-500 mb-4">Register a new exchange hot wallet (Bybit, etc.) or casino deposit router contract.</p>

        <form action="{{ route('admin.entities.add-address') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Entity Brand</label>
                    <select name="entity_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-semibold focus:border-indigo-500">
                        @foreach($entities as $ent)
                            <option value="{{ $ent->id }}">
                                {{ $ent->name }} ({{ strtoupper($ent->category) }}{{ $ent->is_custodial_cex ? ' - CEX Pool Filter Active' : '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Network</label>
                    <select name="network" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-semibold focus:border-indigo-500">
                        <option value="tron">TRON (TRC20)</option>
                        <option value="ethereum">Ethereum (ERC20)</option>
                        <option value="bsc">BNB Chain (BEP20)</option>
                        <option value="solana">Solana (SPL)</option>
                        <option value="bitcoin">Bitcoin</option>
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Wallet Address / Smart Contract</label>
                    <input type="text" name="address" required placeholder="T... or 0x..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-mono text-slate-900 focus:border-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Confidence (0.1 - 1.0)</label>
                    <input type="number" step="0.01" min="0.1" max="1.0" name="confidence" value="0.99" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-mono text-slate-900 focus:border-indigo-500">
                </div>
            </div>

            <div class="flex justify-between items-center pt-2">
                <div class="w-2/3">
                    <input type="text" name="label" placeholder="Label (e.g. Bybit Hot Wallet 5 or Stake TRC20 Gateway)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-700">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs px-6 py-2.5 rounded-xl shadow-md transition">
                    Save Address
                </button>
            </div>
        </form>
    </div>

    <!-- Entities List & Addresses -->
    <div class="space-y-6">
        @foreach($entities as $entity)
        <div class="bg-white border border-slate-200 rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center space-x-3">
                    <h3 class="text-base font-extrabold text-slate-900">{{ $entity->name }}</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $entity->category === 'cex' ? 'bg-purple-100 text-purple-800' : ($entity->category === 'gambling' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-800') }}">
                        {{ $entity->category }} • {{ $entity->subtype }}
                    </span>
                    @if($entity->is_custodial_cex)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                            🛡️ Custodial CEX Pool Filter Active
                        </span>
                    @endif
                </div>
                <span class="text-xs font-mono text-slate-400">{{ $entity->addresses->count() }} addresses</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 uppercase font-bold text-[10px] border-b border-slate-100">
                            <th class="py-2 px-2">Network</th>
                            <th class="py-2 px-2">Address</th>
                            <th class="py-2 px-2">Label / Description</th>
                            <th class="py-2 px-2">Source</th>
                            <th class="py-2 px-2 text-right">Confidence</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($entity->addresses as $addr)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-2 uppercase font-bold text-[11px] text-slate-500">{{ $addr->network }}</td>
                            <td class="py-2.5 px-2 font-mono font-bold text-slate-900 text-xs select-all">{{ $addr->address }}</td>
                            <td class="py-2.5 px-2 text-slate-600">{{ $addr->label }}</td>
                            <td class="py-2.5 px-2 text-slate-400 font-mono text-[10px]">{{ $addr->source }}</td>
                            <td class="py-2.5 px-2 text-right font-mono font-bold text-emerald-600">{{ ($addr->confidence) * 100 }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-3 text-center text-slate-400">No linked addresses</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
