@php
    $seg = $segment ?? null;
    $map = [
        'super_vip' => ['Super VIP', 'bg-purple-50 text-purple-800 border-purple-200'],
        'potential_vip' => ['Potential VIP', 'bg-amber-50 text-amber-800 border-amber-200'],
        'high_value' => ['High value', 'bg-emerald-50 text-emerald-800 border-emerald-200'],
        'good_player' => ['Good player', 'bg-sky-50 text-sky-800 border-sky-200'],
        'regular' => ['Regular', 'bg-slate-100 text-slate-700 border-slate-200'],
        'low_value' => ['Low value', 'bg-slate-50 text-slate-500 border-slate-200'],
    ];
    [$label, $classes] = $map[$seg] ?? ['—', 'bg-slate-50 text-slate-400 border-slate-200'];
@endphp
<span class="chip {{ $classes }}">{{ $label }}</span>
