@php $uid = $uid ?? 'mark'; @endphp
<svg viewBox="0 0 100 100" class="w-full h-full" fill="none">
    <defs>
        <linearGradient id="orbit-{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#00d4ff" />
            <stop offset="35%" stop-color="#635bff" />
            <stop offset="70%" stop-color="#7a73ff" />
            <stop offset="100%" stop-color="#ff6080" />
        </linearGradient>
        <linearGradient id="pulse-{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#00d4ff" />
            <stop offset="50%" stop-color="#635bff" />
            <stop offset="100%" stop-color="#ff6080" />
        </linearGradient>
    </defs>
    <circle cx="50" cy="50" r="32" stroke="url(#orbit-{{ $uid }})" stroke-width="7.5" stroke-linecap="round" />
    <ellipse cx="50" cy="50" rx="44" ry="18" stroke="url(#orbit-{{ $uid }})" stroke-width="6" transform="rotate(-30 50 50)" stroke-linecap="round" />
    <path d="M 24 50 L 38 50 L 44 65 L 56 32 L 63 56 L 68 50 L 76 50" stroke="url(#pulse-{{ $uid }})" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" />
</svg>
