@extends('console.layout')

@section('title', 'API keys — On-Score Admin')
@section('page-title', 'API keys')
@section('page-subtitle', 'Every credential issued across the platform')

@section('content')
<div class="space-y-5">

    <div class="grid lg:grid-cols-3 gap-4 items-start">
        <div class="surface p-5">
            <h3 class="text-sm font-black text-ink">Provision a key</h3>
            <p class="text-[11px] text-slate-400 mt-1 mb-4">The secret is displayed once — deliver it to the client securely.</p>
            <form action="{{ route('admin.api-keys.generate') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="label">Client</label>
                    <select name="account_id" required class="input">
                        <option value="">Select a client…</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Key name</label>
                    <input name="name" required class="input" placeholder="Production — casino core">
                </div>
                <div>
                    <label class="label">Rate limit (req/min)</label>
                    <input name="rate_limit" type="number" min="10" max="10000" placeholder="Client default" class="input font-mono">
                </div>
                <button class="btn btn-iris w-full">Generate key</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <form method="GET" class="surface p-4 flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="label">Search</label>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Key name or ID">
                </div>
                <div class="w-52">
                    <label class="label">Client</label>
                    <select name="account_id" class="input">
                        <option value="">All clients</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" @selected(($filters['account_id'] ?? '') === $acc->id)>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        <option value="">All</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                        <option value="revoked" @selected(($filters['status'] ?? '') === 'revoked')>Revoked</option>
                    </select>
                </div>
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.keys') }}" class="btn btn-ghost">Reset</a>
            </form>

            <div class="surface overflow-hidden">
                @if($keys->isEmpty())
                    @include('console.partials.empty', ['title' => 'No keys match', 'text' => 'Try a different filter or provision a new key.'])
                @else
                    <div class="scroll-x">
                        <table class="w-full min-w-[820px]">
                            <thead class="bg-slate-50/70 border-b border-slate-100">
                                <tr>
                                    <th class="th">Key</th>
                                    <th class="th">Client</th>
                                    <th class="th">Status</th>
                                    <th class="th text-right">Rate</th>
                                    <th class="th">Last used</th>
                                    <th class="th"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($keys as $key)
                                    <tr class="row-hover {{ $key->status !== 'active' ? 'opacity-60' : '' }}">
                                        <td class="td">
                                            <div class="font-bold text-ink">{{ $key->name }}</div>
                                            <div class="flex items-center gap-1.5">
                                                <code class="text-[11px] text-slate-400 font-mono">{{ $key->maskedKeyId() }}</code>
                                                <button type="button" onclick="copyText(@js($key->key_id), this)" class="text-[10px] font-bold text-iris-500 hover:text-iris-700">Copy</button>
                                            </div>
                                        </td>
                                        <td class="td">
                                            <a href="{{ $key->account ? route('admin.clients.show', $key->account_id) : '#' }}" class="text-[12px] font-semibold text-slate-600 hover:text-iris-500">{{ $key->account?->name ?? '—' }}</a>
                                        </td>
                                        <td class="td">@include('console.partials.status-badge', ['status' => $key->status])</td>
                                        <td class="td text-right font-mono text-[12px] text-slate-500">{{ $key->rate_limit_per_minute }}</td>
                                        <td class="td text-[12px] text-slate-500">
                                            {{ $key->last_used_at?->diffForHumans() ?? 'never' }}
                                            @if($key->last_used_ip)<div class="text-[10px] text-slate-400 font-mono">{{ $key->last_used_ip }}</div>@endif
                                        </td>
                                        <td class="td text-right">
                                            @if($key->status === 'active')
                                                <form action="{{ route('admin.api-keys.revoke', $key->id) }}" method="POST" onsubmit="return confirm('Revoke “{{ $key->name }}”?')">
                                                    @csrf
                                                    <button class="btn btn-danger py-1 px-2.5 text-[11px]">Revoke</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-slate-100">{{ $keys->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
