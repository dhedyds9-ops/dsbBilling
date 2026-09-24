{{--
/**
 * Sparkline Chart Component
 *
 * Small inline chart for stat cards
 */
--}}

@props([
    'data' => [],
    'color' => 'primary',
    'height' => 40,
    'width' => 100,
])

@php
$colorMap = [
    'primary' => '#0ea5e9',
    'success' => '#22c55e',
    'warning' => '#f59e0b',
    'danger' => '#ef4444',
    'info' => '#3b82f6',
];

$strokeColor = $colorMap[$color] ?? $colorMap['primary'];
$fillColor = $strokeColor . '20';
@endphp

<div class="inline-block" style="height: {{ $height }}px; width: {{ $width }}px;" {{ $attributes }}>
    <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-full" preserveAspectRatio="none">
        <defs>
            <linearGradient id="sparkline-gradient-{{ $color }}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="{{ $strokeColor }}" stop-opacity="0.3"/>
                <stop offset="100%" stop-color="{{ $strokeColor }}" stop-opacity="0"/>
            </linearGradient>
        </defs>

        @if (count($data) > 1)
            @php
                $max = max($data);
                $min = min($data);
                $range = $max - $range > 0 ? $max - $min : 1;
                $points = collect($data)->map(fn($v, $i) => [
                    'x' => ($i / (count($data) - 1)) * $width,
                    'y' => $height - (($v - $min) / $range) * $height * 0.9 - $height * 0.05
                ]);
                $linePath = $points->map(fn($p, $i) => ($i === 0 ? 'M' : 'L') . $p['x'] . ',' . $p['y'])->implode(' ');
                $areaPath = $linePath . ' L' . $width . ',' . $height . ' L0,' . $height . ' Z';
            @endphp

            <path d="{{ $areaPath }}" fill="url(#sparkline-gradient-{{ $color }})" />
            <path d="{{ $linePath }}" fill="none" stroke="{{ $strokeColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        @endif
    </svg>
</div>
