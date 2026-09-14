@extends('console.layout')

@section('title', 'Merchants — On-Score Admin')
@section('page-title', 'Merchants')
@section('page-subtitle', 'B2B accounts, their shared balance, keys and portal access')

@section('content')
<div class="space-y-5">

    <!-- Create client -->
    <div class="surface overflow-hidden" id="new-client">
        <details class="group" {{ $errors->any() || old('name') ? 'open' : '' }}>
            <summary class="px-5 py-4 flex items-center justify-between cursor-pointer list-none select-none hover:bg-slate-50/60">
                <div>
                    <h3 class="text-sm font-black text-ink">Onboard a new client</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Creates the account, its first portal user and grants the starting credits in one step.</p>
                </div>
                <span class="btn btn-iris py-1.5 px-3 text-[12px] group-open:hidden">+ New client</span>
                <span class="btn btn-ghost py-1.5 px-3 text-[12px] hidden group-open:inline-flex">Close</span>
            </summary>

            <form action="{{ route('admin.clients.create') }}" method="POST" class="px-5 pb-5 pt-1 border-t border-slate-100">
                @csrf
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                    <div class="lg:col-span-2">
                        <label class="label">Company name</label>
                        <input name="name" value="{{ old('name') }}" required class="input" placeholder="Apex Gaming Group">
                    </div>
                    <div>
                        <label class="label">Starting credits</label>
                        <input name="initial_credits" type="number" min="0" value="{{ old('initial_credits', 1000) }}" required class="input font-mono">
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="trial" @selected(old('status') === 'trial')>Trial</option>
                            <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Owner name</label>
                        <input name="owner_name" value="{{ old('owner_name') }}" required class="input" placeholder="CRM Manager">
                    </div>
                    <div>
                        <label class="label">Owner email (login)</label>
                        <input name="owner_email" type="email" value="{{ old('owner_email') }}" required class="input" placeholder="crm@apexgaming.com">
                    </div>
                    <div>
                        <label class="label">Max API keys</label>
                        <input name="max_api_keys" type="number" min="1" max="100" value="{{ old('max_api_keys', 5) }}" class="input font-mono">
                    </div>
                    <div>
                        <label class="label">Rate limit (req/min)</label>
                        <input name="default_rate_limit" type="number" min="10" max="10000" value="{{ old('default_rate_limit', 120) }}" class="input font-mono">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-4">
                    <button class="btn btn-primary">Create client &amp; issue access</button>
                    <span class="text-[11px] text-slate-400">A one-time password will be generated and shown once.</span>
                </div>
            </form>
        </details>
    </div>

    <!-- Filters -->
    <form method="GET" class="surface p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="label">Search</label>
            <input name="search" value="{{ $filters['search'] ?? '' }}" class="input" placeholder="Company, slug or contact email">
        </div>
        <div class="w-44">
            <label class="label">Status</label>
            <select name="status" class="input">
                <option value="">All statuses</option>
                @foreach(['active' => 'Active', 'trial' => 'Trial', 'suspended' => 'Suspended'] as $v => $l)
                    <option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.clients') }}" class="btn btn-ghost">Reset</a>
    </form>

    <!-- Table -->
    <div class="surface overflow-hidden">
        @if($accounts->isEmpty())
            @include('console.partials.empty', ['title' => 'No clients found', 'text' => 'Onboard your first B2B client above.'])
        @else
            <div class="scroll-x">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-slate-50/70 border-b border-slate-100">
                        <tr>
                            <th class="th">Client</th>
                            <th class="th">Status</th>
                            <th class="th text-right">Balance</th>
                            <th class="th text-right">Keys</th>
                            <th class="th text-right">Users</th>
                            <th class="th text-right">Analyses</th>
                            <th class="th">Scoring</th>
                            <th class="th"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($accounts as $acc)
                            <tr class="row-hover">
                                <td class="td">
                                    <a href="{{ route('admin.clients.show', $acc->id) }}" class="font-bold text-ink hover:text-iris-500">{{ $acc->name }}</a>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $acc->slug }}</div>
                                </td>
                                <td class="td">@include('console.partials.status-badge', ['status' => $acc->status])</td>
                                <td class="td text-right font-mono font-black {{ $acc->isLowBalance() ? 'text-rose-600' : 'text-ink' }}">{{ number_format($acc->credit_balance) }}</td>
                                <td class="td text-right font-mono text-slate-600">{{ $acc->active_keys_count }} / {{ $acc->max_api_keys }}</td>
                                <td class="td text-right font-mono text-slate-600">{{ $acc->users_count }}</td>
                                <td class="td text-right font-mono text-slate-600">{{ number_format($acc->analyses_count) }}</td>
                                <td class="td">
                                    @if(!empty($acc->scoring_rules))
                                        <span class="chip bg-iris-50 text-iris-700 border-iris-100">Custom</span>
                                    @else
                                        <span class="chip bg-slate-100 text-slate-500 border-slate-200">Default</span>
                                    @endif
                                </td>
                                <td class="td text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form action="{{ route('admin.clients.impersonate', $acc->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-ghost py-1 px-2.5 text-[11px]">View as client</button>
                                        </form>
                                        <a href="{{ route('admin.clients.show', $acc->id) }}" class="btn btn-primary py-1 px-2.5 text-[11px]">Manage</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100">{{ $accounts->links() }}</div>
        @endif
    </div>
</div>
@endsection
