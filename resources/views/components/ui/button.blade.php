@props([
    'variant' => 'primary', // primary, secondary, danger, warning, success, outline, ghost
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'href' => null,
    'type' => 'button',
    'loading' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-900 disabled:opacity-60 disabled:cursor-not-allowed';
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg gap-1.5',
        'md' => 'px-4 py-2 text-sm rounded-lg gap-2',
        'lg' => 'px-6 py-2.5 text-base rounded-xl gap-2',
    ][$size] ?? 'px-4 py-2 text-sm rounded-lg gap-2';

    $variantClasses = [
        'primary' => 'bg-primary-600 hover:bg-primary-700 text-white shadow-sm border border-transparent focus:ring-primary-500',
        'secondary' => 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 shadow-sm focus:ring-primary-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-sm border border-transparent focus:ring-red-500',
        'warning' => 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm border border-transparent focus:ring-amber-500',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm border border-transparent focus:ring-emerald-500',
        'soft' => 'bg-slate-100 dark:bg-slate-700 hover:bg-primary-50 dark:hover:bg-primary-900/30 text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 border border-transparent focus:ring-primary-500',
        'outline' => 'bg-transparent text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 focus:ring-primary-500',
        'ghost' => 'bg-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 focus:ring-slate-500 border border-transparent',
    ][$variant] ?? 'bg-primary-600 hover:bg-primary-700 text-white shadow-sm border border-transparent focus:ring-primary-500';

    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $variantClasses;
    
    // Default loading state logic for Livewire if loading prop is passed
    if ($loading) {
        $attributes = $attributes->merge(['wire:loading.attr' => 'disabled', 'wire:loading.class' => 'opacity-75 cursor-wait']);
    }
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <span class="material-symbols-outlined text-[1.2em]">{{ $icon }}</span>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <span class="material-symbols-outlined text-[1.2em]">{{ $icon }}</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <span class="material-symbols-outlined text-[1.2em]">{{ $icon }}</span>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <span class="material-symbols-outlined text-[1.2em]">{{ $icon }}</span>
        @endif
    </button>
@endif






