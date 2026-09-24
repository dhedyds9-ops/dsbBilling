{{--
/**
 * Textarea Component - Enterprise Design System
 */
--}}

@props([
    'rows' => 4,
    'error' => null,
    'success' => false,
    'disabled' => false,
    'readonly' => false,
    'placeholder' => '',
    'label' => null,
    'hint' => null,
])

@php
$baseClasses = 'w-full border rounded-lg transition-all duration-200 placeholder-slate-400 focus:outline-none focus:ring-2 resize-y';

$stateClasses = $error
    ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500/20'
    : ($success
        ? 'border-success-500 focus:border-success-500 focus:ring-success-500/20'
        : 'border-slate-300 dark:border-slate-600 focus:border-primary-500 focus:ring-primary-500/20 bg-white dark:bg-slate-800');

$disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : '';

$classes = $baseClasses . ' ' . $stateClasses . ' ' . $disabledClasses;
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    <textarea
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => $classes]) }}
        placeholder="{{ $placeholder }}"
        @if ($disabled) disabled @endif
        @if ($readonly) readonly @endif
    >{{ $slot }}</textarea>

    @if ($error)
        <p class="text-sm text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
