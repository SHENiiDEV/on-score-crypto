<div class="px-6 py-14 text-center">
    <div class="w-11 h-11 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto mb-3">
        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
    </div>
    <div class="text-sm font-bold text-ink">{{ $title ?? 'Nothing here yet' }}</div>
    @if(!empty($text))<div class="text-[12px] text-slate-400 mt-1 max-w-sm mx-auto">{{ $text }}</div>@endif
</div>
