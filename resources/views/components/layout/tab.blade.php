{{--
/**
 * Tab Component
 *
 * Use inside x-tabs component
 */
--}}

@props([
    'value' => null,
    'disabled' => false,
    'icon' => null,
])

@php
$variant = 'default';
@endphp

<div
    x-data="{ tab: null }"
    {{ $attributes->merge(['class' => '']) }}
    role="tab"
    :class="$parent.selected === '{{ $value }}' ? '{{ $variant === 'pills' ? 'bg-primary-600 text-white' : ($variant === 'underline' ? 'text-primary-600 border-primary-600' : 'text-primary-600 border-b-2 border-primary-600') }}' : 'text-slate-600 hover:text-slate-900'"
    :selected="$parent.selected === '{{ $value }}'"
    @click="if (!{{ $disabled ? 'true' : 'false' }}) $parent.selected = '{{ $value }}'"
    @if ($disabled) disabled @endif
>
    <div class="flex items-center gap-2">
        @if ($icon)
            <x-icon :name="$icon" class="w-4 h-4" />
        @endif
        {{ $slot }}
    </div>
</div>
