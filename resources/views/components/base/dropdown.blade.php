{{--
/**
 * Dropdown Component - Enterprise Design System
 *
 * Features:
 * - Trigger slots
 * - Align: left, right
 * - Width: auto, full
 * - Animated entrance
 */
--}}

@props([
    'align' => 'left',
    'width' => 'auto',
    'contentClasses' => '',
])

@php
$alignClasses = $align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left';

$widthClasses = $width === 'full' ? 'w-full' : 'w-56';
@endphp

<div
    x-data="{ open: false }"
    @click.away="open = false"
    @keydown.escape.window="open = false"
    class="relative inline-block"
    {{ $attributes }}
>
    {{-- Trigger --}}
    <div @click="open = !open">
        {{ $trigger ?? $slot }}
    </div>

    {{-- Dropdown Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $alignClasses }} {{ $widthClasses }} bg-white rounded-xl shadow-soft-lg border border-slate-200 py-2 focus:outline-none"
        style="display: none;"
    >
        <div class="{{ $contentClasses }}">
            {{ $content ?? '' }}
        </div>
    </div>
</div>
