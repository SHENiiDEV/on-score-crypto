@php
    $s = $status ?? '';
    $map = [
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'trial' => 'bg-sky-50 text-sky-700 border-sky-200',
        'queued' => 'bg-sky-50 text-sky-700 border-sky-200',
        'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'partial' => 'bg-amber-50 text-amber-700 border-amber-200',
        'suspended' => 'bg-rose-50 text-rose-700 border-rose-200',
        'revoked' => 'bg-rose-50 text-rose-700 border-rose-200',
        'disabled' => 'bg-rose-50 text-rose-700 border-rose-200',
        'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
    $cls = $map[$s] ?? 'bg-slate-100 text-slate-600 border-slate-200';
@endphp
<span class="chip {{ $cls }}">
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>{{ ucfirst(str_replace('_', ' ', $s)) }}
</span>
