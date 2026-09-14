@php
    $icon = fn($path) => '<svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="'.$path.'"/></svg>';
@endphp

<div class="nav-group">Overview</div>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    {!! $icon('M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6') !!}
    <span>Command center</span>
</a>

<div class="nav-group">Clients</div>
<a href="{{ route('admin.clients') }}" class="nav-link {{ request()->routeIs('admin.clients') || request()->routeIs('admin.clients.show') ? 'active' : '' }}">
    {!! $icon('M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z') !!}
    <span>Merchants</span>
</a>
<a href="{{ route('admin.keys') }}" class="nav-link {{ request()->routeIs('admin.keys') ? 'active' : '' }}">
    {!! $icon('M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z') !!}
    <span>API keys</span>
</a>
<a href="{{ route('admin.ledger') }}" class="nav-link {{ request()->routeIs('admin.ledger') ? 'active' : '' }}">
    {!! $icon('M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z') !!}
    <span>Credits &amp; billing</span>
</a>

<div class="nav-group">Intelligence</div>
<a href="{{ route('admin.analyses') }}" class="nav-link {{ request()->routeIs('admin.analyses') ? 'active' : '' }}">
    {!! $icon('M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z') !!}
    <span>Analyses</span>
</a>
<a href="{{ route('admin.entities') }}" class="nav-link {{ request()->routeIs('admin.entities') ? 'active' : '' }}">
    {!! $icon('M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4') !!}
    <span>CEX &amp; casino registry</span>
</a>
<a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
    {!! $icon('M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z') !!}
    <span>Wallet workbench</span>
</a>

<div class="nav-group">Reference</div>
<a href="{{ route('docs') }}" target="_blank" class="nav-link">
    {!! $icon('M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253') !!}
    <span>API documentation</span>
</a>
<a href="{{ route('landing') }}" target="_blank" class="nav-link">
    {!! $icon('M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18 15 15 0 010-18z') !!}
    <span>Public site</span>
</a>
