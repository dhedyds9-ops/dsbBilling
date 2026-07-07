{{--
/**
 * Tabs Component - Enterprise Design System
 *
 * Variants:
 * - default, pills, underline, boxed
 * - With icons
 * - Lazy loading content
 */
--}}

@props([
    'variant' => 'default',
    'selected' => null,
])

@php
$variants = [
    'default' => [
        'container' => 'border-b border-slate-200',
        'tab' => 'text-slate-600 hover:text-slate-900 border-b-2 border-transparent',
        'tabActive' => 'text-primary-600 border-primary-600',
        'tabDisabled' => 'opacity-50 cursor-not-allowed',
    ],
    'pills' => [
        'container' => 'gap-2',
        'tab' => 'px-4 py-2 rounded-lg text-slate-600 hover:bg-slate-100',
        'tabActive' => 'bg-primary-600 text-white',
        'tabDisabled' => 'opacity-50 cursor-not-allowed',
    ],
    'underline' => [
        'container' => 'border-b border-slate-200 gap-8',
        'tab' => 'pb-3 text-slate-600 border-b-2 border-transparent hover:text-slate-900 hover:border-slate-300',
        'tabActive' => 'text-primary-600 border-primary-600',
        'tabDisabled' => 'opacity-50 cursor-not-allowed',
    ],
    'boxed' => [
        'container' => 'p-1 bg-slate-100 rounded-xl gap-1',
        'tab' => 'px-4 py-2 rounded-lg text-slate-600 hover:text-slate-900',
        'tabActive' => 'bg-white text-slate-900 shadow-sm',
        'tabDisabled' => 'opacity-50 cursor-not-allowed',
    ],
];

$currentVariant = $variants[$variant];
@endphp

<div x-data="{ selected: @entangle($attributes->get('wire:model')) || '{{ $selected }}' }" {{ $attributes->except(['wire:model']) }}>
    {{-- Tab List --}}
    <div class="{{ $variant !== 'pills' ? 'flex' : 'flex flex-wrap' }} {{ $currentVariant['container'] }}">
        {{ $tabs }}
    </div>

    {{-- Tab Panels --}}
    <div class="mt-4">
        {{ $panels }}
    </div>
</div>
