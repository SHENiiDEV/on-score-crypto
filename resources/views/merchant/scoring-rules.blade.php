@extends('console.layout')

@section('title', 'Scoring rules — On-Score Portal')
@section('page-title', 'Scoring rules')
@section('page-subtitle', 'Tune how On-Score segments your players. Applies to every key on this account.')

@section('actions')
    <form action="{{ route('merchant.scoring-rules.reset') }}" method="POST"
          onsubmit="return confirm('Reset all scoring rules to On-Score platform defaults?')">
        @csrf
        <button class="btn btn-ghost">Reset to defaults</button>
    </form>
@endsection

@section('content')
<form action="{{ route('merchant.scoring-rules.update') }}" method="POST" class="space-y-5">
    @csrf

    <!-- Thresholds -->
    <div class="surface p-5">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div>
                <h3 class="text-sm font-black text-ink">Value tiers</h3>
                <p class="text-[11px] text-slate-400 mt-1 max-w-xl">
                    A wallet is promoted into a tier when its visible (non-custodial) balance crosses the threshold —
                    or when the engine score is high enough. Thresholds must decrease from Super VIP down to High value.
                </p>
            </div>
            <span class="chip bg-iris-50 text-iris-700 border-iris-100 shrink-0">USD</span>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div class="rounded-xl border border-purple-200 bg-purple-50/40 p-4">
                <div class="flex items-center gap-2 mb-2.5">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    <span class="text-[11px] font-black uppercase tracking-wide text-purple-800">Super VIP</span>
                </div>
                <input type="number" step="0.01" min="0" name="super_vip_threshold_usd" id="t_super"
                       value="{{ old('super_vip_threshold_usd', $rules['super_vip_threshold_usd']) }}" class="input font-mono">
                <p class="text-[10px] text-purple-700/70 mt-1.5">Whale liquidity — triggers <code class="font-mono">potential_shark</code> driver.</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-4">
                <div class="flex items-center gap-2 mb-2.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="text-[11px] font-black uppercase tracking-wide text-amber-800">Potential VIP</span>
                </div>
                <input type="number" step="0.01" min="0" name="potential_vip_threshold_usd" id="t_potential"
                       value="{{ old('potential_vip_threshold_usd', $rules['potential_vip_threshold_usd']) }}" class="input font-mono">
                <p class="text-[10px] text-amber-700/70 mt-1.5">Route these to your VIP host / retention team.</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4">
                <div class="flex items-center gap-2 mb-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px] font-black uppercase tracking-wide text-emerald-800">High value</span>
                </div>
                <input type="number" step="0.01" min="0" name="high_value_threshold_usd" id="t_high"
                       value="{{ old('high_value_threshold_usd', $rules['high_value_threshold_usd']) }}" class="input font-mono">
                <p class="text-[10px] text-emerald-700/70 mt-1.5">Above-average depositors worth a tailored bonus.</p>
            </div>
        </div>

        <div id="thresholdWarning" class="hidden mt-3 rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-[12px] font-semibold text-rose-800">
            Thresholds must decrease: Super VIP &gt; Potential VIP &gt; High value.
        </div>
    </div>

    <!-- Custom rules -->
    <div class="surface overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-black text-ink">Custom rules</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Each rule is evaluated after scoring. A match can attach a tag to the report and/or force a segment.
                </p>
            </div>
            <button type="button" id="addRule" class="btn btn-iris py-1.5 px-3 text-[12px]">+ Add rule</button>
        </div>

        <div class="px-5 py-4">
            <div class="hidden lg:grid grid-cols-[1.4fr_1fr_1fr_1.1fr_1.1fr_auto] gap-3 pb-2 mb-1">
                <div class="kpi-label">Metric</div>
                <div class="kpi-label">Condition</div>
                <div class="kpi-label">Value</div>
                <div class="kpi-label">Tag</div>
                <div class="kpi-label">Force segment</div>
                <div></div>
            </div>

            <div id="rulesList" class="space-y-3"></div>

            <div id="rulesEmpty" class="hidden py-10 text-center">
                <div class="text-[13px] font-bold text-ink">No custom rules</div>
                <div class="text-[12px] text-slate-400 mt-1">Scoring falls back to the On-Score engine defaults and your value tiers above.</div>
            </div>
        </div>

        <div class="px-5 py-4 bg-slate-50/60 border-t border-slate-100">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">Available metrics</div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-1.5">
                @foreach($metrics as $key => $meta)
                    <div class="text-[11px] text-slate-500 flex gap-1.5">
                        <code class="font-mono font-bold text-slate-700 shrink-0">{{ $key }}</code>
                        <span class="truncate">— {{ $meta['hint'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Save bar -->
    <div class="sticky bottom-4 z-10">
        <div class="surface px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 shadow-lift">
            <div class="text-[11px] text-slate-500">
                @if(!empty($account->scoring_rules['updated_at']))
                    Last saved {{ \Carbon\Carbon::parse($account->scoring_rules['updated_at'])->diffForHumans() }}
                    @if(!empty($account->scoring_rules['updated_by'])) by {{ $account->scoring_rules['updated_by'] }} @endif
                @else
                    Running On-Score platform defaults.
                @endif
            </div>
            <button class="btn btn-primary">Save scoring rules</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        const metrics = @js($metrics);
        const operators = @js($operators);
        const tiers = @js($tiers);
        const existing = @js(old('rules', $rules['custom_rules']));

        const list = document.getElementById('rulesList');
        const empty = document.getElementById('rulesEmpty');
        let idx = 0;

        function option(value, label, selected) {
            const o = document.createElement('option');
            o.value = value;
            o.textContent = label;
            if (selected) o.selected = true;
            return o;
        }

        function addRow(rule) {
            rule = rule || {};
            const i = idx++;
            const row = document.createElement('div');
            row.className = 'grid lg:grid-cols-[1.4fr_1fr_1fr_1.1fr_1.1fr_auto] gap-3 items-start rounded-xl border border-slate-200 lg:border-transparent bg-white lg:bg-transparent p-3 lg:p-0';

            // Metric
            const metricWrap = document.createElement('div');
            const metricSel = document.createElement('select');
            metricSel.className = 'input';
            metricSel.name = `rules[${i}][metric]`;
            Object.entries(metrics).forEach(([k, m]) => metricSel.appendChild(option(k, m.label, rule.metric === k)));
            const hint = document.createElement('p');
            hint.className = 'text-[10px] text-slate-400 mt-1';
            const setHint = () => { hint.textContent = metrics[metricSel.value]?.unit ? 'Unit: ' + metrics[metricSel.value].unit : ''; };
            metricSel.addEventListener('change', setHint);
            setHint();
            metricWrap.append(metricSel, hint);

            // Operator
            const opSel = document.createElement('select');
            opSel.className = 'input';
            opSel.name = `rules[${i}][operator]`;
            Object.entries(operators).forEach(([k, l]) => opSel.appendChild(option(k, l, (rule.operator || '>=') === k)));

            // Value
            const val = document.createElement('input');
            val.type = 'number';
            val.step = 'any';
            val.className = 'input font-mono';
            val.name = `rules[${i}][value]`;
            val.value = rule.value ?? '';
            val.placeholder = '0';

            // Tag
            const tag = document.createElement('input');
            tag.type = 'text';
            tag.maxLength = 40;
            tag.className = 'input font-mono uppercase';
            tag.name = `rules[${i}][tag]`;
            tag.value = rule.tag ?? '';
            tag.placeholder = 'VIP_HOST';

            // Tier
            const tierSel = document.createElement('select');
            tierSel.className = 'input';
            tierSel.name = `rules[${i}][tier]`;
            Object.entries(tiers).forEach(([k, l]) => tierSel.appendChild(option(k, l, (rule.tier || '') === k)));

            // Remove
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'btn btn-danger w-full lg:w-auto px-3';
            rm.innerHTML = '&times;';
            rm.title = 'Remove rule';
            rm.addEventListener('click', () => { row.remove(); toggleEmpty(); });

            row.append(metricWrap, opSel, val, tag, tierSel, rm);
            list.appendChild(row);
            toggleEmpty();
        }

        function toggleEmpty() {
            empty.classList.toggle('hidden', list.children.length > 0);
        }

        document.getElementById('addRule').addEventListener('click', () => addRow());
        (Array.isArray(existing) ? existing : []).forEach(addRow);
        toggleEmpty();

        // Threshold ordering guard
        const s = document.getElementById('t_super');
        const p = document.getElementById('t_potential');
        const h = document.getElementById('t_high');
        const warn = document.getElementById('thresholdWarning');
        function check() {
            const ok = Number(s.value) > Number(p.value) && Number(p.value) > Number(h.value);
            warn.classList.toggle('hidden', ok);
            return ok;
        }
        [s, p, h].forEach(el => el.addEventListener('input', check));
        check();
        document.querySelector('form').addEventListener('submit', (e) => {
            if (!check()) {
                e.preventDefault();
                warn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    })();
</script>
@endpush
