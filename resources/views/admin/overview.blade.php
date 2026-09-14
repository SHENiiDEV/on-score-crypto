@extends('console.layout')

@section('title', 'Command center — On-Score Admin')
@section('page-title', 'Command center')
@section('page-subtitle', 'Platform-wide B2B activity, credit economics and scoring throughput')

@section('actions')
    <a href="{{ route('admin.clients') }}" class="btn btn-ghost hidden sm:inline-flex">Merchants</a>
    <a href="{{ route('admin.clients') }}#new-client" class="btn btn-primary">+ New client</a>
@endsection

@section('content')
<div class="space-y-5">

    <!-- KPIs -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('console.partials.stat', ['label' => 'B2B clients', 'value' => number_format($accountsCount), 'sub' => $activeAccountsCount . ' active', 'tone' => 'ink'])
        @include('console.partials.stat', ['label' => 'Active API keys', 'value' => number_format($activeKeysCount), 'sub' => 'across all merchants', 'tone' => 'iris'])
        @include('console.partials.stat', ['label' => 'Analyses — 30d', 'value' => number_format($analyses30d), 'sub' => number_format($totalAnalyses) . ' lifetime', 'tone' => 'ink'])
        @include('console.partials.stat', ['label' => 'Entity registry', 'value' => number_format($totalEntities), 'sub' => 'CEX, casino & mixer clusters', 'tone' => 'ink'])
    </div>

    <!-- Credit economics -->
    <div class="grid lg:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-gradient-to-br from-ink to-[#153e69] text-white p-5 shadow-lift relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-emerald-400/20 blur-2xl"></div>
            <div class="relative">
                <div class="text-[10px] font-black uppercase tracking-wider text-white/50">Outstanding credits</div>
                <div class="mt-2 text-[34px] leading-none font-black font-mono text-emerald-300">{{ number_format($outstandingCredits) }}</div>
                <div class="mt-3 pt-3 border-t border-white/10 grid grid-cols-2 gap-3 text-[11px]">
                    <div>
                        <div class="text-white/45 font-bold uppercase tracking-wide text-[9px]">Issued</div>
                        <div class="font-mono font-black text-sm mt-0.5">{{ number_format($totalCreditsIssued) }}</div>
                    </div>
                    <div>
                        <div class="text-white/45 font-bold uppercase tracking-wide text-[9px]">Consumed</div>
                        <div class="font-mono font-black text-sm mt-0.5">{{ number_format($totalCreditsSpent) }}</div>
                    </div>
                </div>
                <a href="{{ route('admin.ledger') }}" class="mt-4 inline-flex items-center gap-1.5 text-[11px] font-bold text-white/80 hover:text-white">Open ledger <span>&rarr;</span></a>
            </div>
        </div>

        <div class="lg:col-span-2 surface p-5">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-black text-ink">Platform throughput</h3>
                <span class="text-[11px] font-bold text-slate-400">Last 30 days</span>
            </div>
            <div id="usageChart" class="-mx-2"></div>
        </div>

        <div class="surface p-5">
            <h3 class="text-sm font-black text-ink mb-1">Segment mix</h3>
            @if(count($segmentBreakdown))
                <div id="segmentChart"></div>
            @else
                <div class="py-12 text-center text-[12px] text-slate-400">No scored wallets yet.</div>
            @endif
        </div>
    </div>

    <!-- Attention + top clients -->
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="surface overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-black text-ink">Needs attention — low balance</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Active clients at or below their alert threshold.</p>
            </div>
            @if($lowBalanceAccounts->isEmpty())
                @include('console.partials.empty', ['title' => 'All clients funded', 'text' => 'No active account is below its low-balance threshold.'])
            @else
                <ul class="divide-y divide-slate-50">
                    @foreach($lowBalanceAccounts as $acc)
                        <li class="px-5 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center text-[11px] font-black shrink-0">!</div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.clients.show', $acc->id) }}" class="text-[13px] font-bold text-ink hover:text-iris-500 truncate block">{{ $acc->name }}</a>
                                <div class="text-[10px] text-slate-400">alert at {{ number_format($acc->low_balance_threshold) }} credits</div>
                            </div>
                            <div class="font-mono font-black text-sm text-rose-600 shrink-0">{{ number_format($acc->credit_balance) }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="surface overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-ink">Top clients by volume</h3>
                <a href="{{ route('admin.clients') }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">All &rarr;</a>
            </div>
            @if($topAccounts->isEmpty())
                @include('console.partials.empty', ['title' => 'No clients yet'])
            @else
                <ul class="divide-y divide-slate-50">
                    @foreach($topAccounts as $acc)
                        <li class="px-5 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-iris-50 border border-iris-100 text-iris-700 flex items-center justify-center text-[11px] font-black shrink-0">
                                {{ mb_strtoupper(mb_substr($acc->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.clients.show', $acc->id) }}" class="text-[13px] font-bold text-ink hover:text-iris-500 truncate block">{{ $acc->name }}</a>
                                <div class="text-[10px] text-slate-400">{{ number_format($acc->credit_balance) }} credits left</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-mono font-black text-sm text-ink">{{ number_format($acc->analyses_count) }}</div>
                                <div class="text-[10px] text-slate-400">analyses</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <!-- Live feed -->
    <div class="grid lg:grid-cols-5 gap-4">
        <div class="lg:col-span-3 surface overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-ink">Live analyses</h3>
                <a href="{{ route('admin.analyses') }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">View all &rarr;</a>
            </div>
            @if($recentAnalyses->isEmpty())
                @include('console.partials.empty', ['title' => 'No analyses yet'])
            @else
                <div class="scroll-x">
                    <table class="w-full min-w-[640px]">
                        <thead class="bg-slate-50/70 border-b border-slate-100">
                            <tr>
                                <th class="th">Wallet</th>
                                <th class="th">Client</th>
                                <th class="th text-center">Score</th>
                                <th class="th">Segment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($recentAnalyses as $a)
                                <tr class="row-hover">
                                    <td class="td">
                                        <div class="font-mono text-[12px] font-semibold text-ink">{{ Str::limit($a->address, 20) }}</div>
                                        <div class="text-[10px] uppercase font-bold text-slate-400">{{ $a->network }} · {{ $a->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="td text-[12px]">
                                        <a href="{{ $a->account ? route('admin.clients.show', $a->account_id) : '#' }}" class="font-semibold text-slate-600 hover:text-iris-500">{{ $a->account?->name ?? '—' }}</a>
                                        <div class="text-[10px] text-slate-400">{{ $a->apiClient?->name ?? 'Internal' }}</div>
                                    </td>
                                    <td class="td text-center font-mono font-black {{ ($a->score_value ?? 0) >= 70 ? 'text-emerald-600' : 'text-slate-700' }}">{{ $a->score_value ?? '—' }}</td>
                                    <td class="td">@include('console.partials.segment-badge', ['segment' => $a->segment])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2 surface overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-ink">Credit movements</h3>
                <a href="{{ route('admin.ledger') }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">All &rarr;</a>
            </div>
            @if($recentLedger->isEmpty())
                @include('console.partials.empty', ['title' => 'No movements yet'])
            @else
                <ul class="divide-y divide-slate-50">
                    @foreach($recentLedger as $e)
                        <li class="px-5 py-3 flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[11px] font-black shrink-0 {{ $e->delta >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">{{ $e->delta >= 0 ? '+' : '−' }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[12px] font-semibold text-ink truncate">{{ $e->account?->name ?? 'Unknown' }}</div>
                                <div class="text-[10px] text-slate-400 truncate">{{ $e->reason }}</div>
                            </div>
                            <div class="text-[12px] font-mono font-black shrink-0 {{ $e->delta > 0 ? 'text-emerald-600' : ($e->delta < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                {{ $e->delta > 0 ? '+' : '' }}{{ number_format($e->delta) }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const usage = @js($usageSeries);
        new ApexCharts(document.querySelector('#usageChart'), {
            chart: { type: 'area', height: 250, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
            series: [{ name: 'Analyses', data: usage.map(d => d.value) }],
            xaxis: { categories: usage.map(d => d.label), labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 }, hideOverlappingLabels: true }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } } },
            stroke: { curve: 'smooth', width: 2.5 },
            colors: ['#635bff'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.32, opacityTo: 0.02 } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#eef2f7', strokeDashArray: 4 },
            tooltip: { theme: 'light' },
        }).render();

        const segments = @js($segmentBreakdown);
        const labels = Object.keys(segments).map(k => k.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
        if (labels.length) {
            new ApexCharts(document.querySelector('#segmentChart'), {
                chart: { type: 'donut', height: 250, fontFamily: 'Plus Jakarta Sans, sans-serif' },
                series: Object.values(segments),
                labels: labels,
                colors: ['#8b5cf6', '#f59e0b', '#10b981', '#0ea5e9', '#64748b', '#cbd5e1'],
                legend: { position: 'bottom', fontSize: '11px', fontWeight: 600, markers: { width: 8, height: 8 } },
                dataLabels: { enabled: false },
                stroke: { width: 2, colors: ['#fff'] },
                plotOptions: { pie: { donut: { size: '68%' } } },
            }).render();
        }
    })();
</script>
@endpush
