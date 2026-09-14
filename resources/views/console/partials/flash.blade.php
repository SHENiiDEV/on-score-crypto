@if(session('success'))
    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
        <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[11px] font-black shrink-0 mt-0.5">✓</div>
        <div class="text-[13px] font-semibold text-emerald-900">{{ session('success') }}</div>
    </div>
@endif

@if(session('warning'))
    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
        <div class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[11px] font-black shrink-0 mt-0.5">!</div>
        <div class="text-[13px] font-semibold text-amber-900">{{ session('warning') }}</div>
    </div>
@endif

@if(session('error'))
    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
        <div class="w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center text-[11px] font-black shrink-0 mt-0.5">!</div>
        <div class="text-[13px] font-semibold text-rose-900">{{ session('error') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
        <div class="text-[11px] font-black uppercase tracking-wide text-rose-700 mb-1.5">Please fix the following</div>
        <ul class="space-y-0.5">
            @foreach($errors->all() as $error)
                <li class="text-[13px] font-semibold text-rose-900">• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('new_api_key'))
    @php $k = session('new_api_key'); @endphp
    <div class="mb-5 rounded-2xl border border-ink/10 bg-gradient-to-br from-ink to-[#123a63] p-5 text-white shadow-lift">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div>
                <div class="text-sm font-black">API key provisioned{{ !empty($k['account_name']) ? ' for ' . $k['account_name'] : '' }}</div>
                <div class="text-[11px] text-white/60 mt-0.5">The secret is shown once and never stored in plain text. Copy it now.</div>
            </div>
        </div>

        <div class="space-y-2.5">
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-white/50 mb-1">Key ID</div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 min-w-0 bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-[12px] font-mono text-emerald-300 break-all">{{ $k['key_id'] }}</code>
                    <button onclick="copyText(@js($k['key_id']), this)" class="shrink-0 text-[11px] font-bold bg-white/10 hover:bg-white/20 border border-white/15 px-2.5 py-2 rounded-lg transition">Copy</button>
                </div>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-white/50 mb-1">Secret</div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 min-w-0 bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-[12px] font-mono text-amber-300 break-all">{{ $k['secret'] }}</code>
                    <button onclick="copyText(@js($k['secret']), this)" class="shrink-0 text-[11px] font-bold bg-white/10 hover:bg-white/20 border border-white/15 px-2.5 py-2 rounded-lg transition">Copy</button>
                </div>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-white/50 mb-1">Authorization header</div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 min-w-0 bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-[12px] font-mono text-white/80 break-all">Bearer {{ $k['key_id'] }}:{{ $k['secret'] }}</code>
                    <button onclick="copyText(@js('Bearer ' . $k['key_id'] . ':' . $k['secret']), this)" class="shrink-0 text-[11px] font-bold bg-white/10 hover:bg-white/20 border border-white/15 px-2.5 py-2 rounded-lg transition">Copy</button>
                </div>
            </div>
        </div>
    </div>
@endif

@if(session('new_merchant_access'))
    @php $m = session('new_merchant_access'); @endphp
    <div class="mb-5 rounded-2xl border border-iris-300/60 bg-gradient-to-br from-iris-500 to-[#8b5cf6] p-5 text-white shadow-lift">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-9 h-9 rounded-xl bg-white/15 border border-white/25 flex items-center justify-center shrink-0">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <div class="text-sm font-black">Portal access created for {{ $m['account_name'] }}</div>
                <div class="text-[11px] text-white/70 mt-0.5">Send these credentials to the client through a secure channel. The password is shown only once.</div>
            </div>
        </div>
        <div class="grid sm:grid-cols-3 gap-2.5">
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-white/60 mb-1">Portal URL</div>
                <code class="block bg-black/25 border border-white/15 rounded-lg px-3 py-2 text-[12px] font-mono break-all">{{ $m['portal_url'] }}</code>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-white/60 mb-1">Email</div>
                <code class="block bg-black/25 border border-white/15 rounded-lg px-3 py-2 text-[12px] font-mono break-all">{{ $m['email'] }}</code>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-wider text-white/60 mb-1">Temporary password</div>
                <code class="block bg-black/25 border border-white/15 rounded-lg px-3 py-2 text-[12px] font-mono text-amber-200 break-all">{{ $m['password'] }}</code>
            </div>
        </div>
        <button onclick="copyText(@js($m['portal_url'] . ' — ' . $m['email'] . ' / ' . $m['password']), this)" class="mt-3 text-[11px] font-bold bg-white/15 hover:bg-white/25 border border-white/20 px-3 py-1.5 rounded-lg transition">Copy all</button>
    </div>
@endif
