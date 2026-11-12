<svg viewBox="0 0 680 160" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <defs>
        <linearGradient id="techGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#0284c7" />
            <stop offset="100%" stop-color="#2563eb" />
        </linearGradient>
    </defs>

    <!-- Tech Emblem Shield & Fiber Nodes -->
    <g transform="translate(15, 15)">
        <!-- Left 'd' Network Beam -->
        <rect x="12" y="25" width="14" height="14" rx="3" class="fill-cyan-600 dark:fill-cyan-400" />
        <rect x="30" y="10" width="14" height="14" rx="3" class="fill-cyan-600 dark:fill-cyan-400" />
        <rect x="30" y="28" width="14" height="14" rx="3" class="fill-cyan-600 dark:fill-cyan-400" />
        <rect x="52" y="10" width="14" height="90" rx="4" class="fill-cyan-600 dark:fill-cyan-400" />
        <path d="M30,46 L52,46 L52,58 L42,58 L42,80 L52,80 L52,94 L30,94 C22,94 22,88 22,82 L22,58 C22,50 24,46 30,46 Z" class="fill-cyan-600 dark:fill-cyan-400" />
        <path d="M22,102 L66,102 L66,122 L22,110 Z" class="fill-cyan-600 dark:fill-cyan-400" />

        <!-- Right 's' & Fiber Nodes -->
        <path d="M72,18 L114,30 L114,44 L72,30 Z" class="fill-slate-600 dark:fill-slate-400" />
        <path d="M72,48 L110,48 L110,62 L84,62 L84,74 L110,74 L110,102 L72,102 L72,88 L98,88 L98,76 L72,76 Z" rx="2" class="fill-slate-600 dark:fill-slate-400" />
        <path d="M72,106 L110,106 L110,113 L72,126 Z" class="fill-slate-600 dark:fill-slate-400" />

        <!-- Emerald Status LED -->
        <circle cx="114" cy="20" r="6" class="fill-emerald-500 dark:fill-emerald-400" />
        
        <!-- Signal / Network Grid Dots -->
        <circle cx="42" cy="116" r="3.5" class="fill-cyan-600 dark:fill-cyan-400" />
        <circle cx="54" cy="118" r="3.5" class="fill-cyan-600 dark:fill-cyan-400" />
        <circle cx="78" cy="118" r="3.5" class="fill-slate-600 dark:fill-slate-400" />
        <circle cx="90" cy="116" r="3.5" class="fill-slate-600 dark:fill-slate-400" />
    </g>

    <!-- Typography Group -->
    <g transform="translate(165, 80)">
        <text x="0" y="0" class="fill-slate-900 dark:fill-slate-100 font-extrabold tracking-tight" style="font-family: 'Segoe UI', system-ui, sans-serif; font-size: 64px;">ds<tspan class="fill-cyan-600 dark:fill-cyan-400 font-bold">Billing</tspan></text>
        <text x="4" y="30" class="fill-cyan-600 dark:fill-cyan-400 font-bold tracking-[0.25em]" style="font-family: 'Segoe UI', system-ui, sans-serif; font-size: 15px;">TECHNICIAN &amp; MONITORING</text>
        <text x="4" y="48" class="fill-slate-500 dark:fill-slate-400 font-medium tracking-[0.1em]" style="font-family: 'Segoe UI', system-ui, sans-serif; font-size: 11px;">ISP OPERATIONS &amp; NETWORK DIAGNOSTICS</text>
    </g>
</svg>
