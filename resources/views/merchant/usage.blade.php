@extends('console.layout')

@section('title', 'Usage & reports — On-Score Portal')
@section('page-title', 'Usage & reports')
@section('page-subtitle', 'Every scored wallet, attributed to the key that requested it')

@section('content')
<div class="space-y-5">

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 surface p-5">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-black text-ink">Request volume</h3>
                <span class="text-[11px] font-bold text-slate-400">Last 30 days</span>
            </div>
            <div id="usageChart" class="-mx-2"></div>
        </div>

        <div class="surface p-5">
            <h3 class="text-sm font-black text-ink">Score a wallet</h3>
            <p class="text-[11px] text-slate-400 mt-1 mb-4">Ad-hoc lookup from the portal. Costs credits exactly like an API call.</p>
            <form action="{{ route('merchant.lookup') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="label">Network</label>
                    <select name="network" class="input">
                        <option value="ethereum">Ethereum</option>
                        <option value="tron">TRON</option>
                        <option value="bsc">BSC</option>
                        <option value="solana">Solana</option>
                        <option value="bitcoin">Bitcoin</option>
                    </select>
                </div>
                <div>
                    <label class="label">Wallet address</label>
                    <input name="address" required class="input font-mono text-[12px]" placeholder="0x…">
                </div>
                <div>
                    <label class="label">Player ID <span class="normal-case font-medium text-slate-400">(optional)</span></label>
                    <input name="external_player_id" class="input" placeholder="crm-48213">
                </div>
                <button class="btn btn-primary w-full">Run analysis</button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="surface p-4">
        <div class="grid sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <label class="label">Search</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Address, player ID or analysis ID">
            </div>
            <div>
                <label class="label">API key</label>
                <select name="key" class="input">
                    <option value="">All keys</option>
                    @foreach($keys as $k)
                        <option value="{{ $k->id }}" @selected(($filters['key'] ?? '') === $k->id)>{{ $k->name }}</option>
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
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="label">From</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input px-2">
                </div>
                <div>
                    <label class="label">To</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input px-2">
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-3.5">
            <button class="btn btn-primary">Apply filters</button>
            <a href="{{ route('merchant.usage') }}" class="btn btn-ghost">Reset</a>
            <span class="ml-auto text-[11px] font-bold text-slate-400">{{ number_format($analyses->total()) }} result(s)</span>
        </div>
    </form>

    <!-- Results -->
    <div class="surface overflow-hidden">
        @if($analyses->isEmpty())
            @include('console.partials.empty', ['title' => 'No analyses match these filters', 'text' => 'Adjust the filters or run a lookup above.'])
        @else
            <div class="scroll-x">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-slate-50/70 border-b border-slate-100">
                        <tr>
                            <th class="th">Date</th>
                            <th class="th">Wallet</th>
                            <th class="th">Key</th>
                            <th class="th">Player ID</th>
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
                                    <div class="font-mono text-[12px] font-semibold text-ink">{{ Str::limit($a->address, 24) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-slate-400">{{ $a->network }}</div>
                                </td>
                                <td class="td text-[12px] text-slate-600">{{ $a->apiClient?->name ?? 'Portal' }}</td>
                                <td class="td text-[12px] font-mono text-slate-500">{{ $a->external_player_id ?? '—' }}</td>
                                <td class="td text-center font-mono font-black {{ ($a->score_value ?? 0) >= 70 ? 'text-emerald-600' : 'text-slate-700' }}">{{ $a->score_value ?? '—' }}</td>
                                <td class="td">@include('console.partials.segment-badge', ['segment' => $a->segment])</td>
                                <td class="td text-right font-mono text-[12px] text-slate-500">{{ $a->cost_credits }}</td>
                                <td class="td">@include('console.partials.status-badge', ['status' => $a->status])</td>
                                <td class="td text-right">
                                    <a href="{{ route('merchant.report', $a->id) }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">Report &rarr;</a>
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

@push('scripts')
<script>
    (function () {
        const usage = @js($usageSeries);
        new ApexCharts(document.querySelector('#usageChart'), {
            chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
            series: [{ name: 'Analyses', data: usage.map(d => d.value) }],
            xaxis: { categories: usage.map(d => d.label), labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 }, hideOverlappingLabels: true }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } } },
            plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
            colors: ['#635bff'],
            dataLabels: { enabled: false },
            grid: { borderColor: '#eef2f7', strokeDashArray: 4 },
            tooltip: { theme: 'light' },
        }).render();
    })();
</script>
@endpush
