{{--
/**
 * Checkbox Component - Enterprise Design System
 */
--}}

@props([
    'checked' => false,
    'disabled' => false,
    'label' => null,
    'hint' => null,
    'error' => null,
])

@php
$baseClasses = 'h-5 w-5 rounded border-slate-300 text-primary-600 focus:ring-2 focus:ring-primary-500 focus:ring-offset-0';

$stateClasses = $error
    ? 'border-danger-500 focus:ring-danger-500/20'
    : 'focus:ring-primary-500/20';

$disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed bg-slate-100' : '';

$classes = $baseClasses . ' ' . $stateClasses . ' ' . $disabledClasses;
@endphp

<div class="flex items-start gap-3" {{ $attributes->only(['wire:model', 'wire:model.lazy', 'x-model']) }}>
    <input
        type="checkbox"
        {{ $attributes->except(['required'])->merge(['class' => $classes]) }}
        @if ($checked) checked @endif
        @if ($disabled) disabled @endif
    />

    <div class="space-y-1">
        @if ($label)
            <label class="block text-sm font-medium text-slate-700 {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                {{ $label }}
                @if ($attributes->has('required'))
                    <span class="text-danger-500">*</span>
                @endif
            </label>
        @endif

        @if ($hint)
            <p class="text-sm text-slate-500">{{ $hint }}</p>
        @endif

        @if ($error)
            <p class="text-sm text-danger-600">{{ $error }}</p>
        @endif
    </div>
</div>
