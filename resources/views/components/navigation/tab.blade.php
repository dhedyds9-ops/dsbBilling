@props([
    'value' => null,
    'disabled' => false,
    'icon' => null,
    'badge' => null,
])

@php
$variant = 'underline';
$container = $attributes->get('parent-container') ?? '';
@endphp

<button
    type="button"
    role="tab"
    x-bind:aria-selected="$parent.selectedTab === '{{ $value }}'"
    x-bind:tabindex="$parent.selectedTab === '{{ $value }}' ? 0 : -1"
    :class="[
        ($parent.selectedTab === '{{ $value }}')
            ? ($el.closest('[role=\"tablist\"]').classList.contains('gap-1') && $el.closest('[role=\"tablist\"]').classList.contains('p-1')
                ? 'bg-[var(--ds-primary)] text-[var(--ds-on-primary)] shadow-soft-md h-9 px-4 rounded-xl inline-flex items-center gap-2 text-sm font-medium transition-all'
                : ($el.closest('[role=\"tablist\"]').classList.contains('-mb-px')
                    ? 'pb-3 px-4 -mb-px border-b-2 border-[var(--ds-primary)] text-[var(--ds-primary)] text-sm font-medium transition-colors inline-flex items-center gap-2'
                    : 'bg-[var(--ds-surface)] text-[var(--ds-on-surface)] shadow-soft-md h-9 px-4 rounded-xl inline-flex items-center gap-2 text-sm font-medium transition-all border border-[var(--ds-outline-variant)]'
                  )
              )
            : ($el.closest('[role=\"tablist\"]').classList.contains('-mb-px')
                ? 'pb-3 px-4 -mb-px border-b-2 border-transparent text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:border-[var(--ds-outline)] text-sm font-medium transition-colors inline-flex items-center gap-2'
                : 'h-9 px-4 rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container)] text-sm font-medium transition-all inline-flex items-center gap-2'
              )
    , '{{ $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}']"
    @click="if (!{{ $disabled ? 'true' : 'false' }}) $parent.selectedTab = '{{ $value }}'"
    @if ($disabled) disabled aria-disabled="true" @endif
    {{ $attributes->except(['parent-container', 'class']) }}
>
    @if ($icon)
        <span class="material-symbols-outlined ms-18">{{ $icon }}</span>
    @endif
    <span>{{ $slot }}</span>
    @if ($badge !== null)
        <span @class([
            'inline-flex items-center justify-center h-5 px-2 rounded-full text-xs font-semibold',
            'bg-[var(--ds-primary-container)] text-[var(--ds-on-primary-container)]' => true,
        ])>
            {{ $badge }}
        </span>
    @endif
</button>






