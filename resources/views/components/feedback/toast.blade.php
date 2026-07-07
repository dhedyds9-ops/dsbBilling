{{--
/**
 * Toast Component - Enterprise Design System
 *
 * Features:
 * - Auto-dismiss
 * - Variants: info, success, warning, danger
 * - Position: top-right, top-center, bottom-right, etc.
 * - Stacking multiple toasts
 */
--}}

@props([
    'type' => 'info',
    'title' => null,
    'message' => null,
    'duration' => 5000,
    'dismissible' => true,
])

@php
$variants = [
    'info' => [
        'bg' => 'bg-info-50',
        'border' => 'border-info-200',
        'icon' => 'text-info-500',
        'title' => 'text-info-800',
        'text' => 'text-info-700',
    ],
    'success' => [
        'bg' => 'bg-success-50',
        'border' => 'border-success-200',
        'icon' => 'text-success-500',
        'title' => 'text-success-800',
        'text' => 'text-success-700',
    ],
    'warning' => [
        'bg' => 'bg-warning-50',
        'border' => 'border-warning-200',
        'icon' => 'text-warning-500',
        'title' => 'text-warning-800',
        'text' => 'text-warning-700',
    ],
    'danger' => [
        'bg' => 'bg-danger-50',
        'border' => 'border-danger-200',
        'icon' => 'text-danger-500',
        'title' => 'text-danger-800',
        'text' => 'text-danger-700',
    ],
];

$icons = [
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];

$toastId = 'toast-' . uniqid();
@endphp

<div
    x-data="{
        show: true,
        duration: {{ $duration }},
        init() {
            if (this.duration > 0) {
                setTimeout(() => this.show = false, this.duration);
            }
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
    x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="flex items-start gap-3 w-full max-w-sm p-4 rounded-xl border shadow-soft-lg {{ $variants[$type]['bg'] }} {{ $variants[$type]['border'] }}"
    role="alert"
    {{ $attributes }}
>
    {{-- Icon --}}
    <svg class="w-5 h-5 flex-shrink-0 {{ $variants[$type]['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $icons[$type] !!}
    </svg>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        @if ($title)
            <p class="font-semibold {{ $variants[$type]['title'] }}">{{ $title }}</p>
        @endif

        <p class="{{ $title ? 'mt-1' : '' }} text-sm {{ $variants[$type]['text'] }}">
            {{ $message ?? $slot }}
        </p>
    </div>

    {{-- Dismiss --}}
    @if ($dismissible)
        <button
            type="button"
            @click="show = false"
            class="flex-shrink-0 p-1 rounded hover:bg-black/5 transition-colors {{ $variants[$type]['text'] }}"
            aria-label="Dismiss"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
