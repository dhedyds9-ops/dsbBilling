@props(['status'])

@php
    $class = match (strtoupper($status)) {
        'ONLINE', 'ACTIVE', 'SUCCESS', 'OK' => 'noc-badge-online',
        'WARNING'                           => 'noc-badge-warning',
        'OFFLINE', 'CRITICAL', 'DOWN'       => 'noc-badge-offline',
        'LOS', 'LOW_RX'                     => 'noc-badge-los',
        'INFO', 'RESOLVED'                  => 'noc-badge-info',
        default                             => 'noc-badge-unknown',
    };
@endphp

<span {{ $attributes->merge(['class' => "px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap $class"]) }}>
    {{ $slot->isEmpty() ? $status : $slot }}
</span>






