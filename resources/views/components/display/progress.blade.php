{{--
/**
 * Progress Bar Component - Enterprise Design System
 *
 * Variants:
 * - Sizes: sm, md, lg
 * - With/without label
 * - Striped, animated
 */
--}}

@props([
    'value' => 0,
    'max' => 100,
    'size' => 'md',
    'label' => null,
    'showValue' => false,
    'variant' => 'primary',
    'striped' => false,
    'animated' => false,
])

@php
$percentage = min(100, max(0, ($value / $max) * 100));

$sizes = [
    'sm' => 'h-1',
    'md' => 'h-2',
    'lg' => 'h-3',
];

$variants = [
    'primary' => 'bg-primary-600',
    'success' => 'bg-success-600',
    'warning' => 'bg-warning-500',
    'danger' => 'bg-danger-600',
    'info' => 'bg-info-600',
];

$barClasses = $variants[$variant];

if ($striped) {
    $barClasses .= ' bg-[url(&apos;data:image/svg+xml,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200h20v20H0z%22%20fill%3D%22rgba%28255%2C255%2C255%2C.1%29%22%2F%3E%3Cpath%20d%3D%22M0%2020l10-10m0%200l10-10m-10%2010l10%2010m0%200l10-10%22%20stroke%3D%22rgba%28255%2C255%2C255%2C.05%29%22%20stroke-width%3D%221%22%2F%3E%3C%2Fsvg%3E&apos;)]';
}

if ($animated) {
    $barClasses .= ' animate-pulse';
}
@endphp

<div class="w-full" {{ $attributes }}>
    @if ($label || $showValue)
        <div class="flex items-center justify-between mb-1.5">
            @if ($label)
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</span>
            @endif

            @if ($showValue)
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ round($percentage) }}%</span>
            @endif
        </div>
    @endif

    <div class="w-full bg-slate-200 rounded-full overflow-hidden {{ $sizes[$size] }}">
        <div
            class="{{ $barClasses }} {{ $sizes[$size] }} rounded-full transition-all duration-500 ease-out"
            style="width: {{ $percentage }}%"
            role="progressbar"
            :aria-valuenow="{{ $value }}"
            aria-valuemin="0"
            aria-valuemax="{{ $max }}"
        ></div>
    </div>
</div>
