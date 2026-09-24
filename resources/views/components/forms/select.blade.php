{{--
/**
 * Select Component - Enterprise Design System
 */
--}}

@props([
    'options' => [],
    'optionLabel' => 'label',
    'optionValue' => 'value',
    'placeholder' => 'Select an option',
    'error' => null,
    'success' => false,
    'disabled' => false,
    'readonly' => false,
    'label' => null,
    'hint' => null,
    'empty' => true,
])

@php
$baseClasses = 'w-full border rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 appearance-none bg-no-repeat bg-right pr-10';

$stateClasses = $error
    ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500/20'
    : ($success
        ? 'border-success-500 focus:border-success-500 focus:ring-success-500/20'
        : 'border-slate-300 dark:border-slate-600 focus:border-primary-500 focus:ring-primary-500/20 bg-white dark:bg-slate-800');

$disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : '';

$classes = $baseClasses . ' ' . $stateClasses . ' ' . $disabledClasses . ' px-4 py-2 text-sm';
@endphp

<div class="space-y-1.5" {{ $attributes->only(['wire:model', 'wire:model.lazy', 'x-model']) }}>
    @if ($label)
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select
            {{ $attributes->except(['required'])->merge(['class' => $classes]) }}
            style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-size: 1.5em 1.5em;"
            @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif
        >
            @if ($empty)
                <option value="" disabled>{{ $placeholder }}</option>
            @endif

            @foreach ($options as $option)
                <option value="{{ is_array($option) ? $option[$optionValue] : $option }}">
                    {{ is_array($option) ? $option[$optionLabel] : $option }}
                </option>
            @endforeach

            {{ $optionsSlot ?? '' }}
        </select>
    </div>

    @if ($error)
        <p class="text-sm text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
