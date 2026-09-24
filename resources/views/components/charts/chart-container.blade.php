@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'iconBg' => 'primary',
    'height' => 320,
    'toolbar' => null,
    'legend' => null,
    'footer' => null,
    'noPadding' => false,
    'bodyClass' => '',
])

@php
$iconBgMap = [
    'primary'   => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
    'success'   => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300',
    'warning'   => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300',
    'danger'    => 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300',
    'info'      => 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-300',
    'secondary' => 'bg-secondary-100 text-secondary-700 dark:bg-secondary-900/30 dark:text-secondary-300',
    'neutral'   => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-[var(--ds-outline-variant)] bg-[var(--ds-surface)] shadow-soft-sm overflow-hidden flex flex-col']) }}>
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 px-6 py-5 border-b border-[var(--ds-outline-variant)]">
        <div class="flex items-start gap-3 min-w-0">
            @if ($icon)
                <div class="w-10 h-10 flex-shrink-0 rounded-xl {{ $iconBgMap[$iconBg] ?? $iconBgMap['primary'] }} inline-flex items-center justify-center">
                    <span class="material-symbols-outlined fill weight-500 ms-20">{{ $icon }}</span>
                </div>
            @endif

            <div class="min-w-0">
                @if ($title)
                    <h3 class="text-[15px] font-semibold text-[var(--ds-on-surface)] leading-tight">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-[var(--ds-on-surface-variant)] mt-1 leading-relaxed">{{ $subtitle }}</p>
                @endif
                @if (isset($headerMeta))
                    <div class="mt-2">{{ $headerMeta }}</div>
                @endif
            </div>
        </div>

        {{-- Toolbar kanan (actions / period dropdown / legend) --}}
        <div class="flex flex-wrap items-center gap-2">
            @if ($legend)
                <div class="flex flex-wrap items-center gap-3 text-xs text-[var(--ds-on-surface-variant)] mr-2">
                    {!! $legend !!}
                </div>
            @elseif (isset($legendSlot))
                <div class="flex flex-wrap items-center gap-3 text-xs text-[var(--ds-on-surface-variant)] mr-2">
                    {{ $legendSlot }}
                </div>
            @endif

            @if ($toolbar)
                {!! $toolbar !!}
            @elseif (isset($toolbarSlot))
                {{ $toolbarSlot }}
            @endif

            @if (isset($actions))
                {{ $actions }}
            @endif
        </div>
    </div>

    {{-- Chart Body --}}
    <div @class([
        'relative flex-1 w-full',
        'p-0' => $noPadding,
        'p-5' => !$noPadding,
        $bodyClass,
    ]) style="min-height: {{ $height }}px;">
        {{ $slot }}
    </div>

    {{-- Footer (source info / footnotes / trend summary) --}}
    @if ($footer || isset($footerSlot))
        <div class="px-6 py-3.5 border-t border-[var(--ds-outline-variant)] bg-[var(--ds-surface-container-low)] text-xs text-[var(--ds-on-surface-variant)]">
            @if ($footer){{ $footer }}@endif
            @if (isset($footerSlot)){{ $footerSlot }}@endif
        </div>
    @endif
</div>






