@extends('console.layout')

@section('title', 'Entity registry — On-Score Admin')
@section('page-title', 'CEX & casino registry')
@section('page-subtitle', 'Labeled clusters powering attribution and CEX pool filtering')

@section('content')
@php
    $totalAddresses = $entities->sum(fn ($e) => $e->addresses->count());
    $byCategory = $entities->groupBy('category');
@endphp

<div class="space-y-5">

    <div class="grid sm:grid-cols-3 gap-4">
        @include('console.partials.stat', ['label' => 'Entities', 'value' => number_format($entities->count()), 'sub' => 'casinos, exchanges, bridges, mixers', 'tone' => 'ink'])
        @include('console.partials.stat', ['label' => 'Labeled addresses', 'value' => number_format($totalAddresses), 'sub' => 'across all networks', 'tone' => 'iris'])
        @include('console.partials.stat', ['label' => 'Custodial CEX clusters', 'value' => number_format($entities->where('is_custodial_cex', true)->count()), 'sub' => 'excluded from player liquidity', 'tone' => 'emerald'])
    </div>

    <div class="grid lg:grid-cols-3 gap-4 items-start">

        <div class="surface p-5">
            <h3 class="text-sm font-black text-ink">Link an address</h3>
            <p class="text-[11px] text-slate-400 mt-1 mb-4">Adding a known deposit or hot-wallet address instantly improves attribution for every client.</p>
            <form action="{{ route('admin.entities.add-address') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="label">Entity</label>
                    <select name="entity_id" required class="input">
                        <option value="">Select an entity…</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}">{{ $entity->name }} ({{ $entity->category }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Network</label>
                    <select name="network" required class="input">
                        @foreach(['tron' => 'TRON', 'ethereum' => 'Ethereum', 'bsc' => 'BSC', 'solana' => 'Solana', 'bitcoin' => 'Bitcoin'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Address</label>
                    <input name="address" required class="input font-mono text-[12px]" placeholder="0x… / T… / bc1…">
                </div>
                <div>
                    <label class="label">Label</label>
                    <input name="label" class="input" placeholder="Stake TRC20 deposit vault">
                </div>
                <div>
                    <label class="label">Confidence</label>
                    <select name="confidence" class="input">
                        <option value="1.0">1.00 — confirmed</option>
                        <option value="0.9">0.90 — very high</option>
                        <option value="0.75">0.75 — high</option>
                        <option value="0.5">0.50 — medium</option>
                        <option value="0.3">0.30 — low / under review</option>
                    </select>
                </div>
                <button class="btn btn-iris w-full">Add to registry</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-4">
            @forelse($byCategory as $category => $group)
                <div class="surface overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-black text-ink capitalize">{{ str_replace('_', ' ', $category) }}</h3>
                        <span class="chip bg-slate-100 text-slate-600 border-slate-200">{{ $group->count() }} entities</span>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @foreach($group as $entity)
                            <details class="group">
                                <summary class="px-5 py-3 flex items-center gap-3 cursor-pointer list-none hover:bg-slate-50/70">
                                    <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center text-[11px] font-black shrink-0">
                                        {{ mb_strtoupper(mb_substr($entity->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-[13px] font-bold text-ink truncate">
                                            {{ $entity->name }}
                                            @if($entity->is_custodial_cex)
                                                <span class="chip bg-sky-50 text-sky-700 border-sky-200 ml-1.5">Custodial CEX</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $entity->slug }}{{ $entity->subtype ? ' · ' . $entity->subtype : '' }}</div>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-400 shrink-0">{{ $entity->addresses->count() }} addr</span>
                                    <svg class="w-4 h-4 text-slate-300 shrink-0 group-open:rotate-180 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                                </summary>
                                @if($entity->addresses->isNotEmpty())
                                    <div class="px-5 pb-4 pt-1 bg-slate-50/50">
                                        <div class="scroll-x">
                                            <table class="w-full min-w-[560px]">
                                                <thead>
                                                    <tr>
                                                        <th class="th">Network</th>
                                                        <th class="th">Address</th>
                                                        <th class="th">Label</th>
                                                        <th class="th text-right">Confidence</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach($entity->addresses as $addr)
                                                        <tr>
                                                            <td class="td"><span class="chip bg-white text-slate-600 border-slate-200 uppercase">{{ $addr->network }}</span></td>
                                                            <td class="td"><code class="text-[11px] font-mono text-slate-600">{{ Str::limit($addr->address, 26) }}</code></td>
                                                            <td class="td text-[12px] text-slate-500">{{ $addr->label }}</td>
                                                            <td class="td text-right font-mono text-[12px] {{ $addr->confidence >= 0.9 ? 'text-emerald-600 font-bold' : 'text-slate-500' }}">{{ number_format($addr->confidence, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @else
                                    <div class="px-5 pb-4 text-[12px] text-slate-400">No addresses linked yet.</div>
                                @endif
                            </details>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="surface">
                    @include('console.partials.empty', ['title' => 'Registry is empty', 'text' => 'Seed the entity database to enable cluster attribution.'])
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
