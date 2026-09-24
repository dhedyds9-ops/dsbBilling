{{--
/**
 * Status Badge Component
 * Unified status mapping for common domain statuses
 */
--}}
@props(['status'])

@php
    $status = strtolower($status);
    $variant = 'default';
    
    if (in_array($status, ['active', 'online', 'paid', 'success', 'completed', 'verified'])) {
        $variant = 'success';
    } elseif (in_array($status, ['inactive', 'offline', 'unpaid', 'failed', 'suspended', 'error'])) {
        $variant = 'danger';
    } elseif (in_array($status, ['pending', 'processing', 'warning', 'waiting', 'hold'])) {
        $variant = 'warning';
    } elseif (in_array($status, ['info', 'draft', 'new'])) {
        $variant = 'info';
    } elseif (in_array($status, ['expired', 'cancelled'])) {
        $variant = 'default';
    }

    $label = strtoupper($status);
@endphp

<x-base.badge variant="{{ $variant }}" dot="true" {{ $attributes }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</x-base.badge>






