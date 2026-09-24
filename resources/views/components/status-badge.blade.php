@props(['status' => null])

@php
    $label = trim((string) $status);
    $normalized = strtolower($label);
    $variants = [
        'active' => 'success', 'online' => 'success', 'up' => 'success', 'connected' => 'success', 'available' => 'success', 'success' => 'success',
        'pending' => 'warning', 'warning' => 'warning', 'maintenance' => 'warning', 'degraded' => 'warning',
        'inactive' => 'danger', 'offline' => 'danger', 'down' => 'danger', 'disconnected' => 'danger', 'failed' => 'danger', 'error' => 'danger',
    ];
    $variant = $variants[$normalized] ?? 'default';
@endphp

<x-base.badge :variant="$variant" size="sm" :dot="$variant !== 'default'">
    {{ $label !== '' ? ucfirst(str_replace('_', ' ', $label)) : '-' }}
</x-base.badge>
