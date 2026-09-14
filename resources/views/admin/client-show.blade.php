@extends('console.layout')

@section('title', $account->name . ' — On-Score Admin')
@section('page-title', $account->name)
@section('page-subtitle', 'Client workspace: access, keys, credits and scoring configuration')

@section('actions')
    <form action="{{ route('admin.clients.impersonate', $account->id) }}" method="POST">
        @csrf
        <button class="btn btn-ghost">View as client</button>
    </form>
    <a href="{{ route('admin.clients') }}" class="btn btn-primary hidden sm:inline-flex">All merchants</a>
@endsection

@section('content')
<div class="space-y-5">

    <!-- Summary -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('console.partials.stat', ['label' => 'Credit balance', 'value' => number_format($account->credit_balance), 'sub' => $account->isLowBalance() ? 'below alert threshold' : 'healthy', 'tone' => $account->isLowBalance() ? 'rose' : 'emerald'])
        @include('console.partials.stat', ['label' => 'Credits consumed', 'value' => number_format($creditsSpent), 'sub' => 'lifetime', 'tone' => 'ink'])
        @include('console.partials.stat', ['label' => 'Analyses', 'value' => number_format($account->analyses_count), 'sub' => 'all keys combined', 'tone' => 'ink'])
        @include('console.partials.stat', ['label' => 'Active keys', 'value' => $keys->where('status', 'active')->count() . ' / ' . $account->max_api_keys, 'sub' => $account->self_service_keys ? 'self-service enabled' : 'self-service disabled', 'tone' => 'iris'])
    </div>

    <!-- Tabs -->
    <div class="surface p-1.5 inline-flex flex-wrap gap-1" id="tabBar">
        @foreach(['access' => 'Portal access', 'keys' => 'API keys', 'credits' => 'Credits', 'scoring' => 'Scoring rules', 'settings' => 'Limits & settings', 'activity' => 'Activity'] as $id => $label)
            <button type="button" data-tab="{{ $id }}" class="tab-btn px-3.5 py-2 rounded-xl text-[12px] font-bold text-slate-500 hover:text-ink transition">{{ $label }}</button>
        @endforeach
    </div>

    <!-- Portal access -->
    <section data-panel="access" class="tab-panel space-y-4">
        <div class="grid lg:grid-cols-3 gap-4 items-start">
            <div class="surface p-5">
                <h3 class="text-sm font-black text-ink">Grant portal access</h3>
                <p class="text-[11px] text-slate-400 mt-1 mb-4">Creates a login for this client. A one-time password is generated and shown once.</p>
                <form action="{{ route('admin.clients.users.create', $account->id) }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="label">Full name</label>
                        <input name="name" required class="input" placeholder="VIP Manager">
                    </div>
                    <div>
                        <label class="label">Email (login)</label>
                        <input name="email" type="email" required class="input" placeholder="vip@client.com">
                    </div>
                    <div>
                        <label class="label">Role</label>
                        <select name="role" class="input">
                            <option value="merchant_owner">Account owner — full portal access</option>
                            <option value="merchant_member">Team member — full portal access</option>
                        </select>
                    </div>
                    <button class="btn btn-iris w-full">Create access</button>
                </form>
            </div>

            <div class="lg:col-span-2 surface overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-black text-ink">Portal users</h3>
                </div>
                @if($users->isEmpty())
                    @include('console.partials.empty', ['title' => 'No portal users', 'text' => 'This client cannot sign in yet. Create access on the left.'])
                @else
                    <div class="scroll-x">
                        <table class="w-full min-w-[680px]">
                            <thead class="bg-slate-50/70 border-b border-slate-100">
                                <tr>
                                    <th class="th">User</th>
                                    <th class="th">Role</th>
                                    <th class="th">Status</th>
                                    <th class="th">Last login</th>
                                    <th class="th"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($users as $u)
                                    <tr class="row-hover">
                                        <td class="td">
                                            <div class="font-bold text-ink">{{ $u->name }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                                            @if($u->must_change_password)
                                                <span class="chip bg-amber-50 text-amber-800 border-amber-200 mt-1">Password change pending</span>
                                            @endif
                                        </td>
                                        <td class="td text-[12px] text-slate-600">{{ $u->roleLabel() }}</td>
                                        <td class="td">@include('console.partials.status-badge', ['status' => $u->status])</td>
                                        <td class="td text-[12px] text-slate-500">{{ $u->last_login_at?->diffForHumans() ?? 'never' }}</td>
                                        <td class="td text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <form action="{{ route('admin.clients.users.reset', [$account->id, $u->id]) }}" method="POST"
                                                      onsubmit="return confirm('Generate a new password for {{ $u->email }}?')">
                                                    @csrf
                                                    <button class="btn btn-ghost py-1 px-2.5 text-[11px]">Reset password</button>
                                                </form>
                                                <form action="{{ route('admin.clients.users.toggle', [$account->id, $u->id]) }}" method="POST">
                                                    @csrf
                                                    <button class="btn {{ $u->status === 'active' ? 'btn-danger' : 'btn-ghost' }} py-1 px-2.5 text-[11px]">
                                                        {{ $u->status === 'active' ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- API keys -->
    <section data-panel="keys" class="tab-panel space-y-4">
        <div class="grid lg:grid-cols-3 gap-4 items-start">
            <div class="surface p-5">
                <h3 class="text-sm font-black text-ink">Provision a key</h3>
                <p class="text-[11px] text-slate-400 mt-1 mb-4">Admin-issued keys bypass the client's self-service limit.</p>
                <form action="{{ route('admin.api-keys.generate') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="account_id" value="{{ $account->id }}">
                    <div>
                        <label class="label">Key name</label>
                        <input name="name" required class="input" placeholder="Production — casino core">
                    </div>
                    <div>
                        <label class="label">Rate limit (req/min)</label>
                        <input name="rate_limit" type="number" min="10" max="10000" value="{{ $account->default_rate_limit }}" class="input font-mono">
                    </div>
                    <button class="btn btn-iris w-full">Generate key</button>
                </form>
            </div>

            <div class="lg:col-span-2 surface overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-black text-ink">Keys ({{ $keys->count() }})</h3>
                </div>
                @if($keys->isEmpty())
                    @include('console.partials.empty', ['title' => 'No keys issued'])
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
                                    @php $st = $keyStats[$key->id] ?? ['calls' => 0, 'credits' => 0]; @endphp
                                    <tr class="row-hover {{ $key->status !== 'active' ? 'opacity-60' : '' }}">
                                        <td class="td">
                                            <div class="font-bold text-ink">{{ $key->name }}</div>
                                            <code class="text-[11px] text-slate-400 font-mono">{{ $key->maskedKeyId() }}</code>
                                            <div class="text-[10px] text-slate-400">{{ $key->rate_limit_per_minute }} req/min @if($key->created_by) · by {{ $key->created_by }} @endif</div>
                                        </td>
                                        <td class="td">@include('console.partials.status-badge', ['status' => $key->status])</td>
                                        <td class="td text-right font-mono font-bold">{{ number_format($st['calls']) }}</td>
                                        <td class="td text-right font-mono text-slate-500">{{ number_format($st['credits']) }}</td>
                                        <td class="td text-[12px] text-slate-500">{{ $key->last_used_at?->diffForHumans() ?? 'never' }}</td>
                                        <td class="td text-right">
                                            @if($key->status === 'active')
                                                <form action="{{ route('admin.api-keys.revoke', $key->id) }}" method="POST" onsubmit="return confirm('Revoke this key?')">
                                                    @csrf
                                                    <button class="btn btn-danger py-1 px-2.5 text-[11px]">Revoke</button>
                                                </form>
                                            @else
                                                <span class="text-[11px] text-slate-400">Revoked</span>
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
    </section>

    <!-- Credits -->
    <section data-panel="credits" class="tab-panel space-y-4">
        <div class="grid lg:grid-cols-3 gap-4 items-start">
            <div class="surface p-5">
                <h3 class="text-sm font-black text-ink">Adjust balance</h3>
                <p class="text-[11px] text-slate-400 mt-1 mb-4">Positive tops up, negative writes off. Every change is written to the immutable ledger.</p>
                <form action="{{ route('admin.credits.adjust') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="account_id" value="{{ $account->id }}">
                    <div>
                        <label class="label">Delta (credits)</label>
                        <input name="delta" type="number" required class="input font-mono" placeholder="1000">
                        <div class="flex gap-1.5 mt-2">
                            @foreach([500, 1000, 5000, 10000] as $preset)
                                <button type="button" onclick="this.closest('form').delta.value={{ $preset }}" class="chip bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200">+{{ number_format($preset) }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="label">Reason</label>
                        <input name="reason" required class="input" placeholder="Invoice #2026-014 paid">
                    </div>
                    <button class="btn btn-iris w-full">Apply adjustment</button>
                </form>
            </div>

            <div class="lg:col-span-2 surface overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-black text-ink">Recent ledger</h3>
                    <a href="{{ route('admin.ledger', ['account_id' => $account->id]) }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">Full ledger &rarr;</a>
                </div>
                @if($ledger->isEmpty())
                    @include('console.partials.empty', ['title' => 'No movements yet'])
                @else
                    <div class="scroll-x">
                        <table class="w-full min-w-[680px]">
                            <thead class="bg-slate-50/70 border-b border-slate-100">
                                <tr>
                                    <th class="th">Date</th>
                                    <th class="th">Type</th>
                                    <th class="th">Reason</th>
                                    <th class="th">Actor</th>
                                    <th class="th text-right">Delta</th>
                                    <th class="th text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($ledger as $e)
                                    <tr class="row-hover">
                                        <td class="td whitespace-nowrap text-[12px] text-slate-500">{{ $e->created_at?->format('d M, H:i') }}</td>
                                        <td class="td"><span class="chip bg-slate-100 text-slate-600 border-slate-200">{{ $e->type }}</span></td>
                                        <td class="td text-[12px] text-ink">{{ Str::limit($e->reason, 46) }}</td>
                                        <td class="td text-[11px] text-slate-400 font-mono">{{ Str::limit($e->actor, 22) }}</td>
                                        <td class="td text-right font-mono font-black {{ $e->delta > 0 ? 'text-emerald-600' : ($e->delta < 0 ? 'text-rose-600' : 'text-slate-400') }}">{{ $e->delta > 0 ? '+' : '' }}{{ number_format($e->delta) }}</td>
                                        <td class="td text-right font-mono text-slate-600">{{ number_format($e->balance_after) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Scoring rules -->
    <section data-panel="scoring" class="tab-panel">
        <form action="{{ route('admin.clients.scoring-rules', $account->id) }}" method="POST" class="space-y-4">
            @csrf
            <div class="surface p-5">
                <h3 class="text-sm font-black text-ink">Value tiers</h3>
                <p class="text-[11px] text-slate-400 mt-1 mb-4">Thresholds must decrease: Super VIP &gt; Potential VIP &gt; High value. The client can also edit these in their portal.</p>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="label">Super VIP (USD)</label>
                        <input type="number" step="0.01" min="0" name="super_vip_threshold_usd" value="{{ $rules['super_vip_threshold_usd'] }}" class="input font-mono">
                    </div>
                    <div>
                        <label class="label">Potential VIP (USD)</label>
                        <input type="number" step="0.01" min="0" name="potential_vip_threshold_usd" value="{{ $rules['potential_vip_threshold_usd'] }}" class="input font-mono">
                    </div>
                    <div>
                        <label class="label">High value (USD)</label>
                        <input type="number" step="0.01" min="0" name="high_value_threshold_usd" value="{{ $rules['high_value_threshold_usd'] }}" class="input font-mono">
                    </div>
                </div>
            </div>

            <div class="surface overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-ink">Custom rules</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Evaluated after scoring; a match can tag the report and/or force a segment.</p>
                    </div>
                    <button type="button" id="adminAddRule" class="btn btn-iris py-1.5 px-3 text-[12px]">+ Add rule</button>
                </div>
                <div class="px-5 py-4">
                    <div id="adminRulesList" class="space-y-3"></div>
                    <div id="adminRulesEmpty" class="hidden py-8 text-center text-[12px] text-slate-400">No custom rules — engine defaults apply.</div>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="btn btn-primary">Save scoring rules</button>
            </div>
        </form>
    </section>

    <!-- Limits & settings -->
    <section data-panel="settings" class="tab-panel">
        <div class="surface p-5 max-w-3xl">
            <h3 class="text-sm font-black text-ink mb-4">Limits &amp; settings</h3>
            <form action="{{ route('admin.clients.update', $account->id) }}" method="POST" class="grid sm:grid-cols-2 gap-4">
                @csrf
                <div class="sm:col-span-2">
                    <label class="label">Company name</label>
                    <input name="name" value="{{ $account->name }}" required class="input">
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        @foreach(['active' => 'Active', 'trial' => 'Trial', 'suspended' => 'Suspended'] as $v => $l)
                            <option value="{{ $v }}" @selected($account->status === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Billing contact</label>
                    <input name="contact_email" type="email" value="{{ $account->contact_email }}" class="input">
                </div>
                <div>
                    <label class="label">Max active API keys</label>
                    <input name="max_api_keys" type="number" min="0" max="100" value="{{ $account->max_api_keys }}" required class="input font-mono">
                </div>
                <div>
                    <label class="label">Rate limit (req/min)</label>
                    <input name="default_rate_limit" type="number" min="10" max="10000" value="{{ $account->default_rate_limit }}" required class="input font-mono">
                </div>
                <div>
                    <label class="label">Low-balance alert at</label>
                    <input name="low_balance_threshold" type="number" min="0" value="{{ $account->low_balance_threshold }}" required class="input font-mono">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none pb-2.5">
                        <input type="checkbox" name="self_service_keys" value="1" @checked($account->self_service_keys) class="w-4 h-4 rounded border-slate-300 text-iris-500 focus:ring-iris-500">
                        <span class="text-[12px] font-semibold text-ink">Client may generate their own keys</span>
                    </label>
                </div>
                <div class="sm:col-span-2 flex items-center gap-3 pt-1">
                    <button class="btn btn-primary">Save settings</button>
                    <span class="text-[11px] text-slate-400 font-mono">ID: {{ $account->id }}</span>
                </div>
            </form>
        </div>
    </section>

    <!-- Activity -->
    <section data-panel="activity" class="tab-panel">
        <div class="surface overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-ink">Latest analyses</h3>
                <a href="{{ route('admin.analyses', ['account_id' => $account->id]) }}" class="text-[11px] font-bold text-iris-500 hover:text-iris-700">View all &rarr;</a>
            </div>
            @if($recentAnalyses->isEmpty())
                @include('console.partials.empty', ['title' => 'No analyses yet'])
            @else
                <div class="scroll-x">
                    <table class="w-full min-w-[760px]">
                        <thead class="bg-slate-50/70 border-b border-slate-100">
                            <tr>
                                <th class="th">Date</th>
                                <th class="th">Wallet</th>
                                <th class="th">Key</th>
                                <th class="th text-center">Score</th>
                                <th class="th">Segment</th>
                                <th class="th">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($recentAnalyses as $a)
                                <tr class="row-hover">
                                    <td class="td whitespace-nowrap text-[12px] text-slate-500">{{ $a->created_at->format('d M, H:i') }}</td>
                                    <td class="td">
                                        <div class="font-mono text-[12px] font-semibold text-ink">{{ Str::limit($a->address, 22) }}</div>
                                        <div class="text-[10px] uppercase font-bold text-slate-400">{{ $a->network }}</div>
                                    </td>
                                    <td class="td text-[12px] text-slate-600">{{ $a->apiClient?->name ?? 'Internal' }}</td>
                                    <td class="td text-center font-mono font-black {{ ($a->score_value ?? 0) >= 70 ? 'text-emerald-600' : 'text-slate-700' }}">{{ $a->score_value ?? '—' }}</td>
                                    <td class="td">@include('console.partials.segment-badge', ['segment' => $a->segment])</td>
                                    <td class="td">@include('console.partials.status-badge', ['status' => $a->status])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        // Tabs
        const bar = document.getElementById('tabBar');
        const buttons = [...bar.querySelectorAll('.tab-btn')];
        const panels = [...document.querySelectorAll('.tab-panel')];

        function activate(id, push) {
            buttons.forEach(b => {
                const on = b.dataset.tab === id;
                b.classList.toggle('bg-ink', on);
                b.classList.toggle('text-white', on);
                b.classList.toggle('text-slate-500', !on);
            });
            panels.forEach(p => p.classList.toggle('hidden', p.dataset.panel !== id));
            if (push) history.replaceState(null, '', '#' + id);
        }

        buttons.forEach(b => b.addEventListener('click', () => activate(b.dataset.tab, true)));
        const initial = location.hash.replace('#', '');
        activate(panels.some(p => p.dataset.panel === initial) ? initial : 'access', false);

        // Scoring rule builder
        const metrics = @js($metrics);
        const operators = @js($operators);
        const tiers = @js($tiers);
        const existing = @js($rules['custom_rules']);

        const list = document.getElementById('adminRulesList');
        const empty = document.getElementById('adminRulesEmpty');
        let idx = 0;

        const option = (v, l, sel) => { const o = document.createElement('option'); o.value = v; o.textContent = l; o.selected = !!sel; return o; };
        const toggleEmpty = () => empty.classList.toggle('hidden', list.children.length > 0);

        function addRow(rule) {
            rule = rule || {};
            const i = idx++;
            const row = document.createElement('div');
            row.className = 'grid lg:grid-cols-[1.4fr_1fr_1fr_1.1fr_1.1fr_auto] gap-3 items-start rounded-xl border border-slate-200 lg:border-transparent p-3 lg:p-0';

            const metricSel = document.createElement('select');
            metricSel.className = 'input'; metricSel.name = `rules[${i}][metric]`;
            Object.entries(metrics).forEach(([k, m]) => metricSel.appendChild(option(k, m.label, rule.metric === k)));

            const opSel = document.createElement('select');
            opSel.className = 'input'; opSel.name = `rules[${i}][operator]`;
            Object.entries(operators).forEach(([k, l]) => opSel.appendChild(option(k, l, (rule.operator || '>=') === k)));

            const val = document.createElement('input');
            val.type = 'number'; val.step = 'any'; val.className = 'input font-mono';
            val.name = `rules[${i}][value]`; val.value = rule.value ?? ''; val.placeholder = '0';

            const tag = document.createElement('input');
            tag.type = 'text'; tag.maxLength = 40; tag.className = 'input font-mono uppercase';
            tag.name = `rules[${i}][tag]`; tag.value = rule.tag ?? ''; tag.placeholder = 'VIP_HOST';

            const tierSel = document.createElement('select');
            tierSel.className = 'input'; tierSel.name = `rules[${i}][tier]`;
            Object.entries(tiers).forEach(([k, l]) => tierSel.appendChild(option(k, l, (rule.tier || '') === k)));

            const rm = document.createElement('button');
            rm.type = 'button'; rm.className = 'btn btn-danger w-full lg:w-auto px-3'; rm.innerHTML = '&times;';
            rm.addEventListener('click', () => { row.remove(); toggleEmpty(); });

            row.append(metricSel, opSel, val, tag, tierSel, rm);
            list.appendChild(row);
            toggleEmpty();
        }

        document.getElementById('adminAddRule').addEventListener('click', () => addRow());
        (Array.isArray(existing) ? existing : []).forEach(addRow);
        toggleEmpty();
    })();
</script>
@endpush
