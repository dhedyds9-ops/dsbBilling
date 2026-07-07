{{--
/**
 * Dropdown Item Component
 *
 * Use inside dropdown component
 */
--}}

@props([
    'href' => null,
    'active' => false,
    'disabled' => false,
    'danger' => false,
])

@php
$baseClasses = 'flex items-center gap-2 w-full px-4 py-2 text-sm text-left transition-colors';

$stateClasses = $disabled
    ? 'text-slate-400 cursor-not-allowed'
    : ($active
        ? 'bg-primary-50 text-primary-700'
        : ($danger
            ? 'text-danger-600 hover:bg-danger-50'
            : 'text-slate-700 hover:bg-slate-100'));

$classes = $baseClasses . ' ' . $stateClasses;
@endphp

@if ($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="button"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($disabled) disabled @endif
    >
        {{ $slot }}
    </button>
@endif
