@php
    $tone = $tone ?? 'ink';
    $toneClass = match ($tone) {
        'emerald' => 'text-emerald-600',
        'iris' => 'text-iris-500',
        'amber' => 'text-amber-600',
        'rose' => 'text-rose-600',
        default => 'text-ink',
    };
@endphp
<div class="surface p-4">
    <div class="kpi-label">{{ $label }}</div>
    <div class="mt-1.5 text-[26px] leading-none font-black font-mono {{ $toneClass }}">{{ $value }}</div>
    @if(!empty($sub))
        <div class="mt-1.5 text-[11px] text-slate-400 font-medium">{{ $sub }}</div>
    @endif
</div>
