@props(['severity' => 'normal'])

@php
    $severity = strtolower((string) $severity);
    $variants = [
        'critical' => 'danger',
        'high' => 'danger',
        'medium' => 'warning',
        'low' => 'info',
        'normal' => 'success',
    ];
@endphp

<x-base.badge :variant="$variants[$severity] ?? 'default'" size="sm" :dot="true">
    {{ ucfirst($severity) }}
</x-base.badge>
