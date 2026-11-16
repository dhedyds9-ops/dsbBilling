@props([
    'ip' => null,
    'fallback' => '-',
    'icon' => false,
    'prefix' => null,
])

@php
    $raw = is_string($ip) || is_numeric($ip) ? trim((string) $ip) : '';
    $display = $raw !== '' ? $raw : $fallback;

    $hostCandidate = $raw;
    $urlTarget = $raw;
    if (str_contains($hostCandidate, '/')) {
        $hostCandidate = explode('/', $hostCandidate)[0];
        $urlTarget = $hostCandidate;
    }
    if (str_contains($hostCandidate, ':') && substr_count($hostCandidate, ':') === 1) {
        $parts = explode(':', $hostCandidate);
        $hostCandidate = $parts[0];
    }

    $isValidIp = $hostCandidate !== ''
        && $hostCandidate !== '0.0.0.0'
        && filter_var($hostCandidate, FILTER_VALIDATE_IP) !== false;
@endphp

@if($isValidIp)
    <a href="http://{{ $urlTarget }}"
       target="_blank"
       rel="noopener noreferrer"
       onclick="event.stopPropagation();"
       style="text-decoration: none !important;"
       title="Direct IP Access: http://{{ $urlTarget }}"
       {{ $attributes->merge(['class' => 'ip-direct-link no-underline hover:no-underline hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors inline-flex items-center gap-1']) }}>
        @if($prefix)<span>{{ $prefix }}</span>@endif<span>{{ $display }}</span>@if($icon)<span class="material-symbols-outlined notranslate" translate="no" style="font-size:12px;text-decoration:none !important;">open_in_new</span>@endif
    </a>
@else
    <span style="text-decoration: none !important;" {{ $attributes->merge(['class' => 'ip-direct-link no-underline']) }}>@if($prefix){{ $prefix }}@endif{{ $display }}</span>
@endif
