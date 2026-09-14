@php
    $icon = fn($path) => '<svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="'.$path.'"/></svg>';
    $mAccount = \App\Support\MerchantContext::account();
@endphp

@if($mAccount)
    <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 mb-1">
        <div class="text-[9px] font-black uppercase tracking-wider text-slate-400">Workspace</div>
        <div class="text-[13px] font-black text-ink truncate">{{ $mAccount->name }}</div>
        <div class="mt-2 flex items-baseline gap-1.5">
            <span class="text-lg font-black font-mono {{ $mAccount->isLowBalance() ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($mAccount->credit_balance) }}</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase">credits</span>
        </div>
    </div>
@endif

<div class="nav-group">Workspace</div>
<a href="{{ route('merchant.dashboard') }}" class="nav-link {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">
    {!! $icon('M4 5a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 17a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z') !!}
    <span>Overview</span>
</a>
<a href="{{ route('merchant.keys') }}" class="nav-link {{ request()->routeIs('merchant.keys') ? 'active' : '' }}">
    {!! $icon('M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z') !!}
    <span>API keys</span>
</a>
<a href="{{ route('merchant.usage') }}" class="nav-link {{ request()->routeIs('merchant.usage') || request()->routeIs('merchant.report') ? 'active' : '' }}">
    {!! $icon('M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z') !!}
    <span>Usage &amp; reports</span>
</a>
<a href="{{ route('merchant.billing') }}" class="nav-link {{ request()->routeIs('merchant.billing') ? 'active' : '' }}">
    {!! $icon('M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z') !!}
    <span>Credits &amp; billing</span>
</a>

<div class="nav-group">Configuration</div>
<a href="{{ route('merchant.scoring-rules') }}" class="nav-link {{ request()->routeIs('merchant.scoring-rules') ? 'active' : '' }}">
    {!! $icon('M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4') !!}
    <span>Scoring rules</span>
</a>
<a href="{{ route('merchant.settings') }}" class="nav-link {{ request()->routeIs('merchant.settings') ? 'active' : '' }}">
    {!! $icon('M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z') !!}
    <span>Settings</span>
</a>

<div class="nav-group">Developers</div>
<a href="{{ route('docs') }}" target="_blank" class="nav-link">
    {!! $icon('M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253') !!}
    <span>API documentation</span>
</a>
