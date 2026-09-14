@extends('console.layout')

@section('title', 'API keys — On-Score Portal')
@section('page-title', 'API keys')
@section('page-subtitle', 'All keys share one credit balance. Usage is attributed per key.')

@section('content')
@php
    $activeCount = $keys->where('status', 'active')->count();
    $remaining = $account->remainingKeySlots();
@endphp

<div class="space-y-5">

    <div class="grid sm:grid-cols-3 gap-4">
        @include('console.partials.stat', ['label' => 'Active keys', 'value' => $activeCount . ' / ' . $account->max_api_keys, 'sub' => $remaining . ' slot(s) left', 'tone' => $remaining ? 'ink' : 'amber'])
        @include('console.partials.stat', ['label' => 'Shared balance', 'value' => number_format($account->credit_balance), 'sub' => 'credits available to every key', 'tone' => 'emerald'])
        @include('console.partials.stat', ['label' => 'Rate limit', 'value' => number_format($account->default_rate_limit), 'sub' => 'requests / minute / key', 'tone' => 'ink'])
    </div>

    <div class="grid lg:grid-cols-3 gap-4 items-start">

        <!-- Create key -->
        <div class="surface p-5">
            <h3 class="text-sm font-black text-ink">Generate a key</h3>
            <p class="text-[11px] text-slate-400 mt-1 mb-4">The secret is displayed once. Store it in your secrets manager immediately.</p>

            @if(! $account->self_service_keys)
                <div class="rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-3 text-[12px] text-slate-500 font-medium">
                    Self-service key provisioning is disabled for your account. Contact On-Score support to issue a key.
                </div>
            @elseif($remaining <= 0)
                <div class="rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-3 text-[12px] text-amber-800 font-semibold">
                    You have reached your limit of {{ $account->max_api_keys }} active keys. Revoke an unused key or ask us to raise the limit.
                </div>
            @else
                <form action="{{ route('merchant.keys.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="label">Key name</label>
                        <input name="name" required maxlength="120" class="input" placeholder="Production — casino core">
                        <p class="text-[10px] text-slate-400 mt-1">Use one key per environment or integration so you can revoke them independently.</p>
                    </div>
                    <div>
                        <label class="label">Rate limit (req/min)</label>
                        <input name="rate_limit" type="number" min="10" max="{{ $account->default_rate_limit }}" value="{{ $account->default_rate_limit }}" class="input">
                        <p class="text-[10px] text-slate-400 mt-1">Cannot exceed your plan limit of {{ $account->default_rate_limit }}.</p>
                    </div>
                    <button class="btn btn-iris w-full">Generate key</button>
                </form>
            @endif

            <div class="mt-5 pt-4 border-t border-slate-100">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Quick start</div>
                <pre class="bg-ink text-[11px] leading-relaxed text-emerald-300 font-mono rounded-xl p-3 overflow-x-auto">curl -X POST {{ config('app.url') }}/api/v1/score/wallet \
  -H "Authorization: Bearer KEY_ID:SECRET" \
  -H "Content-Type: application/json" \
  -d '{"chain":"ETH","address":"0x..."}'</pre>
            </div>
        </div>

        <!-- Keys table -->
        <div class="lg:col-span-2 surface overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-black text-ink">Your keys</h3>
            </div>
            @if($keys->isEmpty())
                @include('console.partials.empty', ['title' => 'No keys yet', 'text' => 'Generate your first key to start calling the scoring API.'])
            @else
                <div class="scroll-x">
                    <table class="w-full min-w-[760px]">
                        <thead class="bg-slate-50/70 border-b border-slate-100">
                            <tr>
                                <th class="th">Key</th>
                                <th class="th">Status</th>
                                <th class="th text-right">Calls</th>
                                <th class="th text-right">Credits</th>
                                <th class="th">Last used</th>
                                <th class="th"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($keys as $key)
                                @php $st = $keyStats[$key->id] ?? ['calls' => 0, 'credits' => 0, 'last_call' => null]; @endphp
                                <tr class="row-hover {{ $key->status !== 'active' ? 'opacity-60' : '' }}">
                                    <td class="td">
                                        <div class="font-bold text-ink">{{ $key->name }}</div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <code class="text-[11px] text-slate-400 font-mono">{{ $key->maskedKeyId() }}</code>
                                            <button type="button" onclick="copyText(@js($key->key_id), this)" class="text-[10px] font-bold text-iris-500 hover:text-iris-700">Copy ID</button>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            {{ $key->rate_limit_per_minute }} req/min · created {{ $key->created_at->format('d M Y') }}@if($key->created_by) by {{ $key->created_by }}@endif
                                        </div>
                                    </td>
                                    <td class="td">@include('console.partials.status-badge', ['status' => $key->status])</td>
                                    <td class="td text-right font-mono font-bold">{{ number_format($st['calls']) }}</td>
                                    <td class="td text-right font-mono text-slate-500">{{ number_format($st['credits']) }}</td>
                                    <td class="td text-[12px] text-slate-500">{{ $key->last_used_at?->diffForHumans() ?? 'never' }}</td>
                                    <td class="td text-right">
                                        @if($key->status === 'active')
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ route('merchant.usage', ['key' => $key->id]) }}" class="text-[11px] font-bold text-slate-500 hover:text-ink">History</a>
                                                <form action="{{ route('merchant.keys.revoke', $key->id) }}" method="POST"
                                                      onsubmit="return confirm('Revoke “{{ $key->name }}”? Requests using this key will start failing immediately.')">
                                                    @csrf
                                                    <button class="btn btn-danger text-[11px] py-1 px-2.5">Revoke</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-[11px] text-slate-400">Revoked {{ $key->revoked_at?->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
