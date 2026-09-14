@extends('console.layout')

@section('title', 'Credits & billing — On-Score Portal')
@section('page-title', 'Credits & billing')
@section('page-subtitle', 'Immutable ledger of every credit issued, reserved and refunded')

@section('content')
<div class="space-y-5">

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('console.partials.stat', ['label' => 'Current balance', 'value' => number_format($account->credit_balance), 'sub' => 'shared across all keys', 'tone' => $account->isLowBalance() ? 'amber' : 'emerald'])
        @include('console.partials.stat', ['label' => 'Total topped up', 'value' => number_format($toppedUpTotal), 'sub' => 'lifetime credits issued', 'tone' => 'ink'])
        @include('console.partials.stat', ['label' => 'Total spent', 'value' => number_format($spentTotal), 'sub' => 'lifetime credits consumed', 'tone' => 'ink'])
        @include('console.partials.stat', ['label' => 'Low-balance alert at', 'value' => number_format($account->low_balance_threshold), 'sub' => 'credits', 'tone' => 'ink'])
    </div>

    <div class="surface p-5">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-sm font-black text-ink">Daily credit consumption</h3>
            <span class="text-[11px] font-bold text-slate-400">Last 30 days</span>
        </div>
        <div id="spendChart" class="-mx-2"></div>
    </div>

    @if($account->isLowBalance())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-[12px] font-black shrink-0">!</div>
            <div>
                <div class="text-[13px] font-black text-amber-900">Balance is running low</div>
                <div class="text-[12px] text-amber-800 mt-0.5">
                    Once credits hit zero, scoring requests are rejected with <code class="font-mono">INSUFFICIENT_CREDITS</code>.
                    Contact your On-Score account manager{{ $account->contact_email ? ' (' . $account->contact_email . ')' : '' }} to top up.
                </div>
            </div>
        </div>
    @endif

    <div class="surface overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-sm font-black text-ink">Ledger</h3>
            <form method="GET" class="flex items-center gap-2">
                <select name="type" class="input py-1.5 text-[12px] w-auto">
                    <option value="">All movement types</option>
                    @foreach(['topup' => 'Top-up', 'reservation' => 'Reservation', 'charge' => 'Charge', 'refund' => 'Refund', 'deduction' => 'Deduction', 'correction' => 'Correction'] as $v => $l)
                        <option value="{{ $v }}" @selected(($filters['type'] ?? '') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                <button class="btn btn-ghost py-1.5 px-3 text-[12px]">Filter</button>
            </form>
        </div>

        @if($entries->isEmpty())
            @include('console.partials.empty', ['title' => 'No ledger entries', 'text' => 'Credit movements appear here as soon as your keys start working.'])
        @else
            <div class="scroll-x">
                <table class="w-full min-w-[760px]">
                    <thead class="bg-slate-50/70 border-b border-slate-100">
                        <tr>
                            <th class="th">Date</th>
                            <th class="th">Type</th>
                            <th class="th">Reason</th>
                            <th class="th">Reference</th>
                            <th class="th text-right">Delta</th>
                            <th class="th text-right">Balance after</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($entries as $e)
                            <tr class="row-hover">
                                <td class="td whitespace-nowrap text-[12px] text-slate-500">{{ $e->created_at?->format('d M Y, H:i') }}</td>
                                <td class="td"><span class="chip bg-slate-100 text-slate-600 border-slate-200">{{ $e->type }}</span></td>
                                <td class="td text-[12px] text-ink font-medium">{{ $e->reason }}</td>
                                <td class="td"><code class="text-[11px] text-slate-400 font-mono">{{ Str::limit($e->reference_id ?? '—', 22) }}</code></td>
                                <td class="td text-right font-mono font-black {{ $e->delta > 0 ? 'text-emerald-600' : ($e->delta < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                    {{ $e->delta > 0 ? '+' : '' }}{{ number_format($e->delta) }}
                                </td>
                                <td class="td text-right font-mono text-slate-600">{{ number_format($e->balance_after) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100">{{ $entries->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const spend = @js($spendSeries);
        new ApexCharts(document.querySelector('#spendChart'), {
            chart: { type: 'area', height: 240, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
            series: [{ name: 'Credits', data: spend.map(d => d.value) }],
            xaxis: { categories: spend.map(d => d.label), labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 }, hideOverlappingLabels: true }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } } },
            stroke: { curve: 'smooth', width: 2.5 },
            colors: ['#10b981'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.02 } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#eef2f7', strokeDashArray: 4 },
            tooltip: { theme: 'light', y: { formatter: v => v + ' credits' } },
        }).render();
    })();
</script>
@endpush
