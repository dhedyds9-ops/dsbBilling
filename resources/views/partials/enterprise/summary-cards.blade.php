{{--
Summary Widgets Partial SSOT
Usage:
@include('partials.enterprise.summary-cards', [
   'items' => [
     ['label'=>'Total','value'=>$total,'color'=>'blue','icon'=>'users'],
     ...
   ]
])
--}}
@props(['items' => []])
@if (count($items) > 0)
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 p-2 lg:p-3 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
    @foreach ($items as $it)
        @php
            $colorMap = [
                'blue'    => 'text-blue-600 bg-blue-50 dark:text-blue-300 dark:bg-blue-900/30',
                'sky'     => 'text-sky-600 bg-sky-50 dark:text-sky-300 dark:bg-sky-900/30',
                'cyan'    => 'text-cyan-600 bg-cyan-50 dark:text-cyan-300 dark:bg-cyan-900/30',
                'teal'    => 'text-teal-600 bg-teal-50 dark:text-teal-300 dark:bg-teal-900/30',
                'green'   => 'text-emerald-600 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-900/30',
                'emerald' => 'text-emerald-600 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-900/30',
                'amber'   => 'text-amber-600 bg-amber-50 dark:text-amber-300 dark:bg-amber-900/30',
                'yellow'  => 'text-yellow-600 bg-yellow-50 dark:text-yellow-300 dark:bg-yellow-900/30',
                'orange'  => 'text-orange-600 bg-orange-50 dark:text-orange-300 dark:bg-orange-900/30',
                'red'     => 'text-red-600 bg-red-50 dark:text-red-300 dark:bg-red-900/30',
                'rose'    => 'text-rose-600 bg-rose-50 dark:text-rose-300 dark:bg-rose-900/30',
                'purple'  => 'text-purple-600 bg-purple-50 dark:text-purple-300 dark:bg-purple-900/30',
                'indigo'  => 'text-indigo-600 bg-indigo-50 dark:text-indigo-300 dark:bg-indigo-900/30',
                'slate'   => 'text-slate-600 bg-slate-100 dark:text-slate-300 dark:bg-slate-700',
                'gray'    => 'text-slate-600 bg-slate-100 dark:text-slate-300 dark:bg-slate-700',
                'zinc'    => 'text-slate-600 bg-slate-100 dark:text-slate-300 dark:bg-slate-700',
            ];
            $colorKey = strtolower((string)($it['color'] ?? 'slate'));
            $c = $colorMap[$colorKey] ?? $colorMap['slate'];
            $icon = (string)($it['icon'] ?? 'file-text');
        @endphp
        <div class="flex items-center gap-3 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
            <div class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-md {{ $c }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if ($icon === 'users')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    @elseif ($icon === 'credit-card')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    @elseif ($icon === 'clock')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @elseif ($icon === 'check-circle')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @elseif ($icon === 'alert-triangle')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    @elseif ($icon === 'dollar-sign')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @elseif ($icon === 'server')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                    @elseif ($icon === 'wifi-off')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/>
                    @elseif ($icon === 'wifi')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    @elseif ($icon === 'file-text')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    @elseif ($icon === 'activity' || $icon === 'loader')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    @elseif ($icon === 'trending-up')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    @elseif ($icon === 'trending-down')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    @elseif ($icon === 'ticket')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    @elseif ($icon === 'calendar')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    @elseif ($icon === 'wrench' || $icon === 'wrench-screwdriver')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                    @elseif ($icon === 'shield' || $icon === 'shield-check')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    @elseif ($icon === 'clipboard-list')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @endif
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] lg:text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $it['label'] }}</div>
                <div class="text-sm lg:text-lg font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">{{ $it['value'] }}</div>
            </div>
        </div>
    @endforeach
</div>
@endif
