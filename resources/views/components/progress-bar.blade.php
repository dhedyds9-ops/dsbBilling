@props([
    'val' => 0,
    'color' => 'blue',
])

@php
    $variantMap = [
        'emerald' => 'success',
        'amber' => 'warning',
        'red' => 'danger',
        'blue' => 'info',
        'slate' => 'primary',
    ];
@endphp

<x-display.progress :value="$val" :variant="$variantMap[$color] ?? 'primary'" size="sm" />
