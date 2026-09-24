@props([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'description' => null,
    'variant' => 'default',
    'ctaLabel' => null,
    'ctaRoute' => null,
    'ctaIcon' => 'add',
    'ctaAction' => null,
])

@php
$iconBgVariants = [
    'default' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    'primary' => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
    'success' => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300',
    'warning' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300',
    'danger'  => 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300',
    'info'    => 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-300',
];

$iconBg = $iconBgVariants[$variant] ?? $iconBgVariants['default'];
@endphp

<div class="flex flex-col items-center justify-center py-16 px-6 text-center">
    <div class="w-20 h-20 rounded-3xl {{ $iconBg }} flex items-center justify-center mb-6 shadow-soft">
        <span class="material-symbols-outlined fill weight-500 ms-48">{{ $icon }}</span>
    </div>

    <h3 class="text-lg font-semibold text-[var(--ds-on-surface)] mb-2">{{ $title }}</h3>

    @if ($description)
        <p class="text-sm text-[var(--ds-on-surface-variant)] max-w-md mb-6 leading-relaxed">
            {{ $description }}
        </p>
    @endif

    @if ($ctaLabel)
        @if ($ctaAction)
            <button
                type="button"
                wire:click="{{ $ctaAction }}"
                class="inline-flex items-center gap-2 px-5 h-11 px-5 rounded-xl bg-primary-600 text-white font-medium hover:bg-primary-700 transition-colors shadow-soft-md"
            >
                <span class="material-symbols-outlined ms-20">{{ $ctaIcon }}</span>
                {{ $ctaLabel }}
            </button>
        @elseif ($ctaRoute)
            <a
                href="{{ $ctaRoute }}"
                @if (!str_starts_with($ctaRoute, '#') @else wire:navigate @endif
                class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-primary-600 text-white font-medium hover:bg-primary-700 transition-colors shadow-soft-md"
            >
                <span class="material-symbols-outlined ms-20">{{ $ctaIcon }}</span>
                {{ $ctaLabel }}
            </a>
        @else
            {{ $cta ?? '' }}
        @endif
    @elseif (isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>






