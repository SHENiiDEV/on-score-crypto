@extends('console.layout')

@section('title', 'Analyses — On-Score Admin')
@section('page-title', 'Analyses')
@section('page-subtitle', 'Every wallet scored on the platform, across all clients and keys')

@section('content')
<div class="space-y-5">

    <form method="GET" class="surface p-4">
        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <label class="label">Search</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Wallet address or player ID">
            </div>
            <div>
                <label class="label">Client</label>
                <select name="account_id" class="input">
                    <option value="">All clients</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(($filters['account_id'] ?? '') === $acc->id)>{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Network</label>
                <select name="network" class="input">
                    <option value="">All</option>
                    @foreach(['ethereum', 'tron', 'bsc', 'solana', 'bitcoin'] as $n)
                        <option value="{{ $n }}" @selected(($filters['network'] ?? '') === $n)>{{ ucfirst($n) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Segment</label>
                <select name="segment" class="input">
                    <option value="">All</option>
                    @foreach(['super_vip' => 'Super VIP', 'potential_vip' => 'Potential VIP', 'high_value' => 'High value', 'good_player' => 'Good player', 'regular' => 'Regular', 'low_value' => 'Low value'] as $v => $l)
                        <option value="{{ $v }}" @selected(($filters['segment'] ?? '') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-3.5">
            <button class="btn btn-primary">Apply filters</button>
            <a href="{{ route('admin.analyses') }}" class="btn btn-ghost">Reset</a>
            <span class="ml-auto text-[11px] font-bold text-slate-400">{{ number_format($analyses->total()) }} result(s)</span>
        </div>
    </form>

    <div class="surface overflow-hidden">
        @if($analyses->isEmpty())
            @include('console.partials.empty', ['title' => 'No analyses match these filters'])
        @else
            <div class="scroll-x">
                <table class="w-full min-w-[940px]">
                    <thead class="bg-slate-50/70 border-b border-slate-100">
                        <tr>
                            <th class="th">Date</th>
                            <th class="th">Wallet</th>
                            <th class="th">Client</th>
                            <th class="th">Key</th>
                            <th class="th text-center">Score</th>
                            <th class="th">Segment</th>
                            <th class="th text-right">Cost</th>
                            <th class="th">Status</th>
                            <th class="th"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($analyses as $a)
                            <tr class="row-hover">
                                <td class="td whitespace-nowrap text-[12px] text-slate-500">{{ $a->created_at->format('d M, H:i') }}</td>
                                <td class="td">
                                    <div class="font-mono text-[12px] font-semibold text-ink">{{ Str::limit($a->address, 22) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-slate-400">{{ $a->network }}</div>
                                </td>
                                <td class="td">
                                    <a href="{{ $a->account ? route('admin.clients.show', $a->account_id) : '#' }}" class="text-[12px] font-semibold text-slate-700 hover:text-iris-500">{{ $a->account?->name ?? '—' }}</a>
                                </td>
                                <td class="td text-[12px] text-slate-500">{{ $a->apiClient?->name ?? 'Internal' }}</td>
                                <td class="td text-center font-mono font-black {{ ($a->score_value ?? 0) >= 70 ? 'text-emerald-600' : 'text-slate-700' }}">{{ $a->score_value ?? '—' }}</td>
                                <td class="td">@include('console.partials.segment-badge', ['segment' => $a->segment])</td>
                                <td class="td text-right font-mono text-[12px] text-slate-500">{{ $a->cost_credits }}</td>
                                <td class="td">@include('console.partials.status-badge', ['status' => $a->status])</td>
                                <td class="td text-right">
                                    <a href="{{ route('dashboard.report', $a->id) }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">Report &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100">{{ $analyses->links() }}</div>
        @endif
    </div>
</div>
@endsection
