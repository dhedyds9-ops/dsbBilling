{{--
/**
 * Toggle Switch Component - Enterprise Design System
 */
--}}

@props([
    'checked' => false,
    'disabled' => false,
    'label' => null,
    'size' => 'md',
])

@php
$sizes = [
    'sm' => ['toggle' => 'w-8 h-4', 'dot' => 'h-3 w-3', 'translate' => 'translate-x-4', 'base' => ''],
    'md' => ['toggle' => 'w-11 h-6', 'dot' => 'h-5 w-5', 'translate' => 'translate-x-5', 'base' => ''],
    'lg' => ['toggle' => 'w-14 h-7', 'dot' => 'h-6 w-6', 'translate' => 'translate-x-7', 'base' => ''],
];

$toggleClasses = $sizes[$size]['toggle'];
$dotClasses = $sizes[$size]['dot'];
$translateClasses = $sizes[$size]['translate'];
@endphp

<div class="flex items-center gap-3 {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}" {{ $attributes->only(['wire:model', 'wire:model.lazy', 'x-model']) }}>
    <button
        type="button"
        role="switch"
        :aria-checked="{{ $checked }}"
        @click="!{{ $disabled }} && $refs.toggle.click()"
        class="{{ $toggleClasses }} relative inline-flex items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $checked ? 'bg-primary-600' : 'bg-slate-200' }}"
        x-ref="toggle"
        @if ($disabled) disabled @endif
    >
        <span
            class="inline-block {{ $dotClasses }} transform rounded-full bg-white dark:bg-slate-800 shadow-sm transition-transform duration-200 ease-in-out {{ $checked ? $translateClasses : 'translate-x-0.5' }}"
        ></span>
    </button>

    @if ($label)
        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</span>
    @endif

    <input
        type="checkbox"
        class="sr-only dark:bg-slate-900 dark:text-slate-100"
        {{ $attributes->merge(['class' => '']) }}
        @if ($checked) checked @endif
        @if ($disabled) disabled @endif
    />
</div>
