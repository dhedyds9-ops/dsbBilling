{{--
/**
 * Input Component - Enterprise Design System
 *
 * Variants:
 * - text, email, password, number, tel, url, search
 * - States: error, success, disabled
 * - Sizes: sm, md, lg
 * - With icon, prefix, suffix
 */
--}}

@props([
    'type' => 'text',
    'size' => 'md',
    'error' => null,
    'success' => false,
    'disabled' => false,
    'readonly' => false,
    'placeholder' => '',
    'label' => null,
    'hint' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'prefix' => null,
    'suffix' => null,
])

@php
$baseClasses = 'w-full border rounded-lg transition-all duration-200 placeholder-slate-400 focus:outline-none focus:ring-2';

$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-5 py-3 text-base',
];

$stateClasses = $error
    ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500/20'
    : ($success
        ? 'border-success-500 focus:border-success-500 focus:ring-success-500/20'
        : 'border-slate-300 focus:border-primary-500 focus:ring-primary-500/20 bg-white');

$disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed bg-slate-100' : '';

$classes = $baseClasses . ' ' . $sizes[$size] . ' ' . $stateClasses . ' ' . $disabledClasses;

$hasIcon = $icon !== null;
$inputPadding = $hasIcon
    ? ($iconPosition === 'left'
        ? 'ps-10'
        : 'pe-10')
    : '';
@endphp

<div class="space-y-1.5" {{ $attributes->only(['wire:model', 'wire:model.lazy', 'x-model']) }}>
    {{-- Label --}}
    @if ($label)
        <label class="block text-sm font-medium text-slate-700">
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    {{-- Input Wrapper --}}
    <div class="relative">
        {{-- Icon Left --}}
        @if ($hasIcon && $iconPosition === 'left')
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <x-icon :name="$icon" class="w-5 h-5" />
            </div>
        @endif

        {{-- Prefix --}}
        @if ($prefix)
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                {{ $prefix }}
            </span>
        @endif

        {{-- Input --}}
        <input
            type="{{ $type }}"
            {{ $attributes->except(['required'])->merge(['class' => $classes . ' ' . $inputPadding]) }}
            placeholder="{{ $placeholder }}"
            @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif
        />

        {{-- Icon Right --}}
        @if ($hasIcon && $iconPosition === 'right')
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                <x-icon :name="$icon" class="w-5 h-5" />
            </div>
        @endif

        {{-- Suffix --}}
        @if ($suffix)
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                {{ $suffix }}
            </span>
        @endif
    </div>

    {{-- Error / Hint --}}
    @if ($error)
        <p class="text-sm text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
