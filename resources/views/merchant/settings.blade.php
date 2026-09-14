@extends('console.layout')

@section('title', 'Settings — On-Score Portal')
@section('page-title', 'Settings')
@section('page-subtitle', 'Profile, password, webhooks and workspace limits')

@section('content')
@php $me = auth()->user(); @endphp

<div class="grid lg:grid-cols-2 gap-5 items-start">

    <!-- Profile -->
    <div class="surface p-5">
        <h3 class="text-sm font-black text-ink mb-4">Your profile</h3>
        <form action="{{ route('merchant.settings.profile') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="label">Full name</label>
                <input name="name" value="{{ old('name', $me->name) }}" required class="input">
            </div>
            <div>
                <label class="label">Email</label>
                <input name="email" type="email" value="{{ old('email', $me->email) }}" required class="input">
            </div>
            <button class="btn btn-primary">Save profile</button>
        </form>
    </div>

    <!-- Password -->
    <div class="surface p-5 {{ $me->must_change_password ? 'ring-2 ring-amber-300' : '' }}">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-black text-ink">Password</h3>
            @if($me->must_change_password)
                <span class="chip bg-amber-50 text-amber-800 border-amber-200">Change required</span>
            @endif
        </div>
        <form action="{{ route('merchant.settings.password') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="label">Current password</label>
                <input name="current_password" type="password" required class="input" autocomplete="current-password">
            </div>
            <div>
                <label class="label">New password</label>
                <input name="password" type="password" required minlength="10" class="input" autocomplete="new-password">
                <p class="text-[10px] text-slate-400 mt-1">At least 10 characters.</p>
            </div>
            <div>
                <label class="label">Confirm new password</label>
                <input name="password_confirmation" type="password" required minlength="10" class="input" autocomplete="new-password">
            </div>
            <button class="btn btn-primary">Change password</button>
        </form>
    </div>

    <!-- Webhook -->
    <div class="surface p-5">
        <h3 class="text-sm font-black text-ink">Webhook endpoint</h3>
        <p class="text-[11px] text-slate-400 mt-1 mb-4">We POST completed analyses here, signed with your signing secret.</p>
        <form action="{{ route('merchant.settings.webhook') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="label">Endpoint URL</label>
                <input name="webhook_url" type="url" value="{{ old('webhook_url', $account->webhook_url) }}" class="input font-mono text-[12px]" placeholder="https://api.yourcasino.com/webhooks/onscore">
            </div>
            <button class="btn btn-primary">Save endpoint</button>
        </form>

        @if($account->webhook_secret)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="label">Signing secret</div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 min-w-0 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[11px] font-mono text-slate-600 break-all">{{ $account->webhook_secret }}</code>
                    <button type="button" onclick="copyText(@js($account->webhook_secret), this)" class="btn btn-ghost py-2 px-2.5 text-[11px]">Copy</button>
                </div>
                <form action="{{ route('merchant.settings.webhook.rotate') }}" method="POST" class="mt-2.5"
                      onsubmit="return confirm('Rotate the signing secret? Existing verification will break until you update it.')">
                    @csrf
                    <button class="btn btn-danger text-[12px] py-1.5 px-3">Rotate secret</button>
                </form>
            </div>
        @endif
    </div>

    <!-- Workspace -->
    <div class="surface p-5">
        <h3 class="text-sm font-black text-ink mb-4">Workspace</h3>
        <dl class="space-y-3">
            @foreach([
                'Company' => $account->name,
                'Account ID' => $account->id,
                'Status' => ucfirst($account->status),
                'Key limit' => $account->max_api_keys . ' active keys',
                'Rate limit' => $account->default_rate_limit . ' req/min per key',
                'Low-balance alert' => number_format($account->low_balance_threshold) . ' credits',
                'Billing contact' => $account->contact_email ?: '—',
            ] as $k => $v)
                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2.5 last:border-0">
                    <dt class="text-[11px] font-bold uppercase tracking-wide text-slate-400 shrink-0">{{ $k }}</dt>
                    <dd class="text-[12px] font-semibold text-ink font-mono text-right break-all">{{ $v }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="text-[11px] text-slate-400 mt-4">
            Need a higher key or rate limit? Contact your On-Score account manager — limits are set on our side.
        </p>
    </div>

    <!-- Team -->
    <div class="lg:col-span-2 surface overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-black text-ink">Team access</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Portal users are provisioned by On-Score. Ask us to add or remove a colleague.</p>
        </div>
        <div class="scroll-x">
            <table class="w-full min-w-[560px]">
                <thead class="bg-slate-50/70 border-b border-slate-100">
                    <tr>
                        <th class="th">User</th>
                        <th class="th">Role</th>
                        <th class="th">Status</th>
                        <th class="th">Last login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($teamMembers as $member)
                        <tr class="row-hover">
                            <td class="td">
                                <div class="font-bold text-ink">{{ $member->name }} @if($member->id === $me->id)<span class="text-[10px] font-bold text-iris-500 ml-1">you</span>@endif</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $member->email }}</div>
                            </td>
                            <td class="td text-[12px] text-slate-600">{{ $member->roleLabel() }}</td>
                            <td class="td">@include('console.partials.status-badge', ['status' => $member->status])</td>
                            <td class="td text-[12px] text-slate-500">{{ $member->last_login_at?->diffForHumans() ?? 'never' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
