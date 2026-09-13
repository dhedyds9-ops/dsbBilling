@props([
    'variant' => 'underline',
    'selected' => null,
    'wireModel' => null,
    'size' => 'md',
])

@php
$variants = [
    'underline' => [
        'container' => 'border-b border-[var(--ds-outline-variant)] gap-1',
        'tabBase' => 'pb-3 px-4 -mb-px border-b-2 border-transparent text-sm font-medium transition-colors',
        'tabInactive' => 'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:border-[var(--ds-outline)]',
        'tabActive' => 'text-[var(--ds-primary)] border-[var(--ds-primary)]',
    ],
    'pills' => [
        'container' => 'gap-1 p-1 bg-[var(--ds-surface-container-low)] rounded-2xl',
        'tabBase' => 'px-4 h-9 rounded-xl text-sm font-medium transition-all inline-flex items-center',
        'tabInactive' => 'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container)]',
        'tabActive' => 'bg-[var(--ds-primary)] text-[var(--ds-on-primary)] shadow-soft-md',
    ],
    'segmented' => [
        'container' => 'gap-0.5 p-0.5 bg-[var(--ds-surface-container-low)] rounded-2xl border border-[var(--ds-outline-variant)]',
        'tabBase' => 'px-4 h-9 rounded-xl text-sm font-medium transition-all inline-flex items-center',
        'tabInactive' => 'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)]',
        'tabActive' => 'bg-[var(--ds-surface)] text-[var(--ds-on-surface)] shadow-soft-sm',
    ],
    'boxed' => [
        'container' => 'gap-1 p-1 bg-[var(--ds-surface-container-low)] rounded-2xl',
        'tabBase' => 'px-4 h-9 rounded-xl text-sm font-medium transition-all inline-flex items-center',
        'tabInactive' => 'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)]',
        'tabActive' => 'bg-[var(--ds-surface)] text-[var(--ds-on-surface)] shadow-soft-md border border-[var(--ds-outline-variant)]',
    ],
];

$v = $variants[$variant] ?? $variants['underline'];
@endphp

<div
    x-data="{
        selectedTab: @if($wireModel) @entangle($wireModel) @else '{{ $selected ?? '' }}' @endif
    }"
    {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.blur']) }}
>
    {{-- Tab List --}}
    <div role="tablist" class="inline-flex w-full flex-wrap items-center {{ $v['container'] }}">
        {{ $tabs }}
    </div>

    {{-- Tab Panels --}}
    <div class="mt-5">
        {{ $panels ?? '' }}
    </div>
</div>






