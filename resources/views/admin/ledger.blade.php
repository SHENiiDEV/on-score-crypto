@extends('console.layout')

@section('title', 'Credits & billing — On-Score Admin')
@section('page-title', 'Credits & billing')
@section('page-subtitle', 'Immutable platform-wide credit ledger')

@section('content')
<div class="space-y-5">

    <div class="grid sm:grid-cols-3 gap-4">
        @include('console.partials.stat', ['label' => 'Credits issued', 'value' => number_format($issued), 'sub' => 'lifetime top-ups', 'tone' => 'emerald'])
        @include('console.partials.stat', ['label' => 'Credits consumed', 'value' => number_format($spent), 'sub' => 'reservations & deductions', 'tone' => 'ink'])
        @include('console.partials.stat', ['label' => 'Outstanding', 'value' => number_format($issued - $spent), 'sub' => 'liability across all clients', 'tone' => 'iris'])
    </div>

    <div class="grid lg:grid-cols-3 gap-4 items-start">
        <div class="surface p-5">
            <h3 class="text-sm font-black text-ink">Adjust a balance</h3>
            <p class="text-[11px] text-slate-400 mt-1 mb-4">Positive tops up, negative writes off.</p>
            <form action="{{ route('admin.credits.adjust') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="label">Client</label>
                    <select name="account_id" required class="input">
                        <option value="">Select a client…</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} — {{ number_format($acc->credit_balance) }} cr</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Delta</label>
                    <input name="delta" type="number" required class="input font-mono" placeholder="1000">
                </div>
                <div>
                    <label class="label">Reason</label>
                    <input name="reason" required class="input" placeholder="Invoice #2026-014 paid">
                </div>
                <button class="btn btn-iris w-full">Apply adjustment</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <form method="GET" class="surface p-4 flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="label">Client</label>
                    <select name="account_id" class="input">
                        <option value="">All clients</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" @selected(($filters['account_id'] ?? '') === $acc->id)>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-44">
                    <label class="label">Type</label>
                    <select name="type" class="input">
                        <option value="">All types</option>
                        @foreach(['topup', 'reservation', 'charge', 'refund', 'deduction', 'correction'] as $t)
                            <option value="{{ $t }}" @selected(($filters['type'] ?? '') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.ledger') }}" class="btn btn-ghost">Reset</a>
            </form>

            <div class="surface overflow-hidden">
                @if($entries->isEmpty())
                    @include('console.partials.empty', ['title' => 'No ledger entries'])
                @else
                    <div class="scroll-x">
                        <table class="w-full min-w-[820px]">
                            <thead class="bg-slate-50/70 border-b border-slate-100">
                                <tr>
                                    <th class="th">Date</th>
                                    <th class="th">Client</th>
                                    <th class="th">Type</th>
                                    <th class="th">Reason</th>
                                    <th class="th">Actor</th>
                                    <th class="th text-right">Delta</th>
                                    <th class="th text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($entries as $e)
                                    <tr class="row-hover">
                                        <td class="td whitespace-nowrap text-[12px] text-slate-500">{{ $e->created_at?->format('d M Y, H:i') }}</td>
                                        <td class="td">
                                            <a href="{{ $e->account ? route('admin.clients.show', $e->account_id) : '#' }}" class="text-[12px] font-semibold text-slate-700 hover:text-iris-500">{{ $e->account?->name ?? '—' }}</a>
                                        </td>
                                        <td class="td"><span class="chip bg-slate-100 text-slate-600 border-slate-200">{{ $e->type }}</span></td>
                                        <td class="td text-[12px] text-ink">{{ Str::limit($e->reason, 44) }}</td>
                                        <td class="td text-[11px] font-mono text-slate-400">{{ Str::limit($e->actor, 20) }}</td>
                                        <td class="td text-right font-mono font-black {{ $e->delta > 0 ? 'text-emerald-600' : ($e->delta < 0 ? 'text-rose-600' : 'text-slate-400') }}">{{ $e->delta > 0 ? '+' : '' }}{{ number_format($e->delta) }}</td>
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
    </div>
</div>
@endsection
