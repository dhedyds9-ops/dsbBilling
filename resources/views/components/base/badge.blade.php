{{--
/**
 * Badge Component - Enterprise Design System
 *
 * Variants:
 * - default, primary, secondary, success, warning, danger, info
 * - Sizes: sm, md, lg
 * - Dot indicator
 * - Dismissible
 */
--}}

@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
    'dismissible' => false,
])

@php
$baseClasses = 'inline-flex items-center font-medium rounded-full';

$variants = [
    'default' => 'bg-slate-100 text-slate-700',
    'primary' => 'bg-primary-100 text-primary-700',
    'secondary' => 'bg-secondary-100 text-secondary-700',
    'success' => 'bg-success-100 text-success-700',
    'warning' => 'bg-warning-100 text-warning-700',
    'danger' => 'bg-danger-100 text-danger-700',
    'info' => 'bg-info-100 text-info-700',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs gap-1',
    'md' => 'px-2.5 py-0.5 text-xs gap-1.5',
    'lg' => 'px-3 py-1 text-sm gap-1.5',
];

$dotColors = [
    'default' => 'bg-slate-400',
    'primary' => 'bg-primary-500',
    'secondary' => 'bg-secondary-500',
    'success' => 'bg-success-500',
    'warning' => 'bg-warning-500',
    'danger' => 'bg-danger-500',
    'info' => 'bg-info-500',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if ($dot)
        <span class="{{ $dotColors[$variant] }} w-1.5 h-1.5 rounded-full"></span>
    @endif

    {{ $slot }}

    @if ($dismissible)
        <button
            type="button"
            @click="$el.parentElement.remove()"
            class="ml-1 -mr-1 p-0.5 rounded-full hover:bg-black/10 transition-colors"
            aria-label="Dismiss"
        >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    @endif
</span>
