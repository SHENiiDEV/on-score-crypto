@extends('console.layout')

@section('title', $account->name . ' — On-Score Portal')
@section('page-title', 'Overview')
@section('page-subtitle', 'Shared credit balance, key activity and scoring configuration for ' . $account->name)

@section('actions')
    <a href="{{ route('merchant.keys') }}" class="btn btn-ghost hidden sm:inline-flex">API keys</a>
    <a href="{{ route('merchant.scoring-rules') }}" class="btn btn-primary">Scoring rules</a>
@endsection

@section('content')
<div class="space-y-5">

    <!-- Balance hero -->
    <div class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-1 rounded-2xl bg-gradient-to-br from-ink to-[#153e69] text-white p-5 shadow-lift relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-iris-500/25 blur-2xl"></div>
            <div class="relative">
                <div class="text-[10px] font-black uppercase tracking-wider text-white/50">Account balance — shared across all keys</div>
                <div class="mt-2 flex items-end gap-2">
                    <span class="text-[40px] leading-none font-black font-mono {{ $account->isLowBalance() ? 'text-amber-300' : 'text-emerald-300' }}">{{ number_format($account->credit_balance) }}</span>
                    <span class="text-xs font-bold text-white/50 pb-1.5">credits</span>
                </div>
                @if($account->isLowBalance())
                    <div class="mt-3 text-[11px] font-bold bg-amber-400/20 border border-amber-300/40 text-amber-100 rounded-lg px-2.5 py-1.5 inline-block">
                        Low balance — top-up before your integration starts failing.
                    </div>
                @endif
                <div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-3 text-[11px]">
                    <div>
                        <div class="text-white/45 font-bold uppercase tracking-wide text-[9px]">Spent · 30d</div>
                        <div class="font-mono font-black text-sm mt-0.5">{{ number_format($creditsSpent30d) }}</div>
                    </div>
                    <div>
                        <div class="text-white/45 font-bold uppercase tracking-wide text-[9px]">Active keys</div>
                        <div class="font-mono font-black text-sm mt-0.5">{{ $keys->where('status', 'active')->count() }} / {{ $account->max_api_keys }}</div>
                    </div>
                </div>
                <a href="{{ route('merchant.billing') }}" class="mt-4 inline-flex items-center gap-1.5 text-[11px] font-bold text-white/80 hover:text-white">
                    View billing history <span>&rarr;</span>
                </a>
            </div>
        </div>

        <div class="lg:col-span-2 grid sm:grid-cols-2 gap-4">
            @include('console.partials.stat', ['label' => 'Analyses — 30 days', 'value' => number_format($analyses30d), 'sub' => number_format($analysesTotal) . ' lifetime', 'tone' => 'ink'])
            @include('console.partials.stat', ['label' => 'VIP-grade wallets', 'value' => number_format($vipCount), 'sub' => 'Super VIP + Potential VIP', 'tone' => 'iris'])
            @include('console.partials.stat', ['label' => 'Rate limit per key', 'value' => number_format($account->default_rate_limit), 'sub' => 'requests / minute', 'tone' => 'ink'])
            @include('console.partials.stat', ['label' => 'Custom scoring rules', 'value' => count($account->effectiveScoringRules()['custom_rules']), 'sub' => 'applied to every analysis', 'tone' => 'emerald'])
        </div>
    </div>

    <!-- Charts -->
    <div class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 surface p-5">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-black text-ink">Request volume</h3>
                <span class="text-[11px] font-bold text-slate-400">Last 30 days</span>
            </div>
            <div id="usageChart" class="-mx-2"></div>
        </div>
        <div class="surface p-5">
            <h3 class="text-sm font-black text-ink mb-1">Player segments</h3>
            @if(count($segmentBreakdown))
                <div id="segmentChart"></div>
            @else
                <div class="py-12 text-center text-[12px] text-slate-400">No scored wallets yet.</div>
            @endif
        </div>
    </div>

    <!-- Keys -->
    <div class="surface overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-black text-ink">API keys</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Every key draws from the same balance. Usage is tracked per key.</p>
            </div>
            <a href="{{ route('merchant.keys') }}" class="btn btn-ghost text-[12px] py-1.5 px-3">Manage</a>
        </div>
        @if($keys->isEmpty())
            @include('console.partials.empty', ['title' => 'No API keys yet', 'text' => 'Generate your first key to start scoring wallets through the API.'])
        @else
            <div class="scroll-x">
                <table class="w-full min-w-[720px]">
                    <thead class="bg-slate-50/70 border-b border-slate-100">
                        <tr>
                            <th class="th">Key</th>
                            <th class="th">Status</th>
                            <th class="th text-right">Calls</th>
                            <th class="th text-right">Credits used</th>
                            <th class="th">Last used</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($keys->take(6) as $key)
                            @php $st = $keyStats[$key->id] ?? ['calls' => 0, 'credits' => 0, 'last_call' => null]; @endphp
                            <tr class="row-hover">
                                <td class="td">
                                    <div class="font-bold text-ink">{{ $key->name }}</div>
                                    <code class="text-[11px] text-slate-400 font-mono">{{ $key->maskedKeyId() }}</code>
                                </td>
                                <td class="td">@include('console.partials.status-badge', ['status' => $key->status])</td>
                                <td class="td text-right font-mono font-bold">{{ number_format($st['calls']) }}</td>
                                <td class="td text-right font-mono text-slate-500">{{ number_format($st['credits']) }}</td>
                                <td class="td text-[12px] text-slate-500">{{ $key->last_used_at?->diffForHumans() ?? 'never' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Recent activity -->
    <div class="grid lg:grid-cols-5 gap-4">
        <div class="lg:col-span-3 surface overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-ink">Latest analyses</h3>
                <a href="{{ route('merchant.usage') }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">View all &rarr;</a>
            </div>
            @if($recentAnalyses->isEmpty())
                @include('console.partials.empty', ['title' => 'No analyses yet', 'text' => 'Results from every key will appear here.'])
            @else
                <div class="scroll-x">
                    <table class="w-full min-w-[600px]">
                        <thead class="bg-slate-50/70 border-b border-slate-100">
                            <tr>
                                <th class="th">Wallet</th>
                                <th class="th">Key</th>
                                <th class="th text-center">Score</th>
                                <th class="th">Segment</th>
                                <th class="th"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($recentAnalyses as $a)
                                <tr class="row-hover">
                                    <td class="td">
                                        <div class="font-mono text-[12px] text-ink font-semibold">{{ Str::limit($a->address, 22) }}</div>
                                        <div class="text-[10px] uppercase font-bold text-slate-400">{{ $a->network }} · {{ $a->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="td text-[12px] text-slate-500">{{ $a->apiClient?->name ?? 'Portal' }}</td>
                                    <td class="td text-center font-mono font-black {{ ($a->score_value ?? 0) >= 70 ? 'text-emerald-600' : 'text-slate-600' }}">{{ $a->score_value ?? '—' }}</td>
                                    <td class="td">@include('console.partials.segment-badge', ['segment' => $a->segment])</td>
                                    <td class="td text-right">
                                        <a href="{{ route('merchant.report', $a->id) }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">Report</a>
                                    </td>
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
                <a href="{{ route('merchant.billing') }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">All &rarr;</a>
            </div>
            @if($recentLedger->isEmpty())
                @include('console.partials.empty', ['title' => 'No movements yet'])
            @else
                <ul class="divide-y divide-slate-50">
                    @foreach($recentLedger as $e)
                        <li class="px-5 py-3 flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[11px] font-black shrink-0 {{ $e->delta >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                {{ $e->delta >= 0 ? '+' : '−' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[12px] font-semibold text-ink truncate">{{ $e->reason }}</div>
                                <div class="text-[10px] text-slate-400">{{ $e->created_at?->format('d M Y, H:i') }} · {{ $e->type }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-[12px] font-mono font-black {{ $e->delta > 0 ? 'text-emerald-600' : ($e->delta < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                    {{ $e->delta > 0 ? '+' : '' }}{{ number_format($e->delta) }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ number_format($e->balance_after) }}</div>
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
            chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif', sparkline: { enabled: false } },
            series: [{ name: 'Analyses', data: usage.map(d => d.value) }],
            xaxis: {
                categories: usage.map(d => d.label),
                labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 }, rotate: 0, hideOverlappingLabels: true },
                axisBorder: { show: false }, axisTicks: { show: false }, tooltip: { enabled: false },
            },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5, colors: ['#635bff'] },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 100], colorStops: [{ offset: 0, color: '#635bff', opacity: 0.35 }, { offset: 100, color: '#635bff', opacity: 0 }] } },
            grid: { borderColor: '#eef2f7', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: { theme: 'light', y: { formatter: v => v + ' analyses' } },
        }).render();

        const segments = @js($segmentBreakdown);
        const labels = Object.keys(segments).map(k => k.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
        if (labels.length) {
            new ApexCharts(document.querySelector('#segmentChart'), {
                chart: { type: 'donut', height: 260, fontFamily: 'Plus Jakarta Sans, sans-serif' },
                series: Object.values(segments),
                labels: labels,
                colors: ['#8b5cf6', '#f59e0b', '#10b981', '#0ea5e9', '#64748b', '#cbd5e1'],
                legend: { position: 'bottom', fontSize: '11px', fontWeight: 600, markers: { width: 8, height: 8 } },
                dataLabels: { enabled: false },
                stroke: { width: 2, colors: ['#fff'] },
                plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Scored', fontSize: '11px', fontWeight: 700, color: '#94a3b8' } } } } },
            }).render();
        }
    })();
</script>
@endpush
