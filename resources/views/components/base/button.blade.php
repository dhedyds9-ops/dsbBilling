{{--
/**
 * Button Component - Enterprise Design System
 *
 * Available variants:
 * - primary, secondary, success, danger, warning, ghost, outline
 * - sm, md, lg, xl sizes
 * - Icons: leading, trailing, icon-only
 * - States: loading, disabled
 */
--}}

@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left',
])

@php
$baseClasses = 'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
    'primary' => 'bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800 shadow-sm hover:shadow focus:ring-primary-500',
    'secondary' => 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-slate-100 hover:bg-slate-300 dark:hover:bg-slate-600 focus:ring-slate-500 dark:focus:ring-slate-400',
    'success' => 'bg-green-600 text-white hover:bg-green-700 active:bg-green-800 focus:ring-green-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800 focus:ring-red-500',
    'warning' => 'bg-yellow-500 text-white hover:bg-yellow-600 active:bg-yellow-700 focus:ring-yellow-500',
    'info' => 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 focus:ring-blue-500',
    'ghost' => 'bg-transparent hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 focus:ring-slate-500 dark:focus:ring-slate-400',
    'outline' => 'border-2 border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 focus:ring-slate-500 dark:focus:ring-slate-400',
    'link' => 'bg-transparent hover:underline text-primary-600 dark:text-primary-400 focus:ring-primary-500',
];

$sizes = [
    'xs' => 'px-2.5 py-1.5 text-xs',
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-5 py-2.5 text-base',
    'xl' => 'px-6 py-3 text-lg',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];

if ($disabled || $loading) {
    $classes .= ' cursor-not-allowed';
}
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($disabled) disabled @endif
    >
        @if ($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif ($icon && $iconPosition === 'left')
            <x-icon :name="$icon" class="h-4 w-4" />
        @endif

        {{ $slot }}

        @if (!$loading && $icon && $iconPosition === 'right')
            <x-icon :name="$icon" class="h-4 w-4" />
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($disabled) disabled @endif
        @if ($loading) disabled @endif
    >
        @if ($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif ($icon && $iconPosition === 'left')
            <x-icon :name="$icon" class="h-4 w-4" />
        @endif

        {{ $slot }}

        @if (!$loading && $icon && $iconPosition === 'right')
            <x-icon :name="$icon" class="h-4 w-4" />
        @endif
    </button>
@endif
