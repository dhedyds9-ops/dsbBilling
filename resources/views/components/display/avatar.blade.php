{{--
/**
 * Avatar Component - Enterprise Design System
 *
 * Variants:
 * - Sizes: xs, sm, md, lg, xl, 2xl
 * - With image, initials, or icon
 * - Status indicator
 * - Group (multiple avatars stacked)
 */
--}}

@props([
    'src' => null,
    'alt' => null,
    'name' => null,
    'size' => 'md',
    'status' => null,
    'rounded' => true,
])

@php
$sizes = [
    'xs' => ['avatar' => 'w-6 h-6 text-xs', 'icon' => 'w-3 h-3'],
    'sm' => ['avatar' => 'w-8 h-8 text-xs', 'icon' => 'w-4 h-4'],
    'md' => ['avatar' => 'w-10 h-10 text-sm', 'icon' => 'w-5 h-5'],
    'lg' => ['avatar' => 'w-12 h-12 text-base', 'icon' => 'w-6 h-6'],
    'xl' => ['avatar' => 'w-16 h-16 text-lg', 'icon' => 'w-8 h-8'],
    '2xl' => ['avatar' => 'w-24 h-24 text-xl', 'icon' => 'w-12 h-12'],
];

$statusColors = [
    'online' => 'bg-success-500',
    'offline' => 'bg-slate-400',
    'away' => 'bg-warning-500',
    'busy' => 'bg-danger-500',
];

$initials = $name ? collect(explode(' ', $name))->take(2)->map(fn($n) => strtoupper($n[0]))->implode('') : '';

$bgColors = [
    'bg-primary-500',
    'bg-secondary-500',
    'bg-success-500',
    'bg-warning-500',
    'bg-danger-500',
    'bg-info-500',
];

$colorIndex = $name ? (ord(strtolower($name[0])) % 5) : 0;
$avatarClasses = $sizes[$size]['avatar'];
$roundedClass = $rounded ? 'rounded-full' : 'rounded-lg';
@endphp

<div class="relative inline-block shrink-0" {{ $attributes }}>
    @if ($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt ?? $name }}"
            class="{{ $avatarClasses }} {{ $roundedClass }} object-cover border-2 border-white dark:border-slate-800"
        />
    @elseif ($name)
        <img
            src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=random&color=fff&bold=true"
            alt="{{ $alt ?? $name }}"
            class="{{ $avatarClasses }} {{ $roundedClass }} object-cover border-2 border-white dark:border-slate-800"
        />
    @else
        <div class="{{ $avatarClasses }} {{ $roundedClass }} bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 border-2 border-white dark:border-slate-800">
            <svg class="{{ $sizes[$size]['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
    @endif

    @if ($status)
        <span class="absolute bottom-0 right-0 block {{ $rounded ? 'rounded-full' : 'rounded' }} border-2 border-white dark:border-slate-800 {{ $statusColors[$status] }} w-3 h-3"></span>
    @endif
</div>
