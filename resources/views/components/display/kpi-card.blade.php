{{--
/**
 * KPI Card Component - Enterprise Design System
 *
 * Advanced stat card with chart sparkline
 */
--}}

@props([
    'title' => null,
    'value' => 0,
    'previousValue' => null,
    'change' => null,
    'changeType' => 'percentage',
    'chartData' => [],
    'chartColor' => 'primary',
    'icon' => null,
    'subtitle' => null,
])

@php
$changeValue = $change;
$isPositive = $changeValue >= 0;
$changeColor = $isPositive ? 'text-success-600' : 'text-danger-600';

$chartColorMap = [
    'primary' => '#0ea5e9',
    'success' => '#22c55e',
    'warning' => '#f59e0b',
    'danger' => '#ef4444',
    'info' => '#3b82f6',
];
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft" {{ $attributes }}>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            @if ($title)
                <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
            @endif

            @if ($subtitle)
                <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($icon)
            <div class="p-2 rounded-lg bg-slate-100">
                <x-icon :name="$icon" class="w-5 h-5 text-slate-600" />
            </div>
        @endif
    </div>

    {{-- Value --}}
    <div class="flex items-end justify-between">
        <div>
            <p class="text-3xl font-bold text-slate-900">{{ $value }}</p>

            @if ($changeValue !== null)
                <div class="flex items-center gap-1 mt-1.5">
                    <span class="{{ $changeColor }}">
                        @if ($isPositive)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        @endif
                    </span>
                    <span class="text-sm font-medium {{ $changeColor }}">
                        {{ abs($changeValue) }}{{ $changeType === 'percentage' ? '%' : '' }}
                    </span>
                    <span class="text-xs text-slate-500">vs last period</span>
                </div>
            @endif
        </div>

        {{-- Sparkline Chart --}}
        @if (!empty($chartData))
            <div class="w-24 h-12">
                <svg viewBox="0 0 100 40" class="w-full h-full">
                    <polyline
                        fill="none"
                        stroke="{{ $chartColorMap[$chartColor] }}"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        points="{{ collect($chartData)->map(fn($v, $i) => ($i * (100 / (count($chartData) - 1))) . ',' . (40 - ($v / max($chartData) * 40)))->implode(' ') }}"
                    />
                </svg>
            </div>
        @endif
    </div>
</div>
