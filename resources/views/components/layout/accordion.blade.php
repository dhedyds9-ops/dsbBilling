{{--
/**
 * Accordion Component - Enterprise Design System
 *
 * Features:
 * - Multiple items open at once
 * - Animated expand/collapse
 * - With icons
 */
--}}

@props([
    'type' => 'single',
    'selected' => null,
])

<div x-data="{
    selected: @entangle($attributes->get('wire:model')) || {{ json_encode($selected) }},
    {{ $type === 'multiple' ? 'multiple: true' : 'multiple: false' }}
}" {{ $attributes->except(['wire:model']) }}>
    {{ $slot }}
</div>
