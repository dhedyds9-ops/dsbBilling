{{--
/**
 * Card Component - Enterprise Design System
 *
 * Variants:
 * - default, bordered, shadow, soft
 * - Header, body, footer slots
 * - Hover effects
 */
--}}

@props([
    'variant' => 'default',
    'padding' => true,
    'hover' => false,
    'flush' => false,
])

@php
$baseClasses = 'bg-white dark:bg-slate-800 rounded-xl overflow-hidden';

$variants = [
    'default' => 'border border-slate-200 dark:border-slate-700',
    'bordered' => 'border-2 border-slate-300 dark:border-slate-600',
    'shadow' => 'shadow-soft border border-slate-200 dark:border-slate-700',
    'soft' => 'shadow-soft-lg border border-slate-100 dark:border-slate-700',
];

$classes = $baseClasses . ' ' . $variants[$variant];

if ($hover) {
    $classes .= ' transition-all duration-200 hover:shadow-soft-md hover:border-slate-300 dark:hover:border-slate-600';
}

if ($flush) {
    $classes .= ' rounded-none';
}
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if (isset($header))
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 {{ $padding ? '' : 'px-0 py-0' }}">
            {{ $header }}
        </div>
    @endif

    <div class="{{ $padding ? 'px-6 py-4' : '' }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 {{ $padding ? '' : 'px-0 py-0' }}">
            {{ $footer }}
        </div>
    @endif
</div>
