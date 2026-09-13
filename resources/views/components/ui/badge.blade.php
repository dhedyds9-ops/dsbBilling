@props([
    'variant' => 'default', // default, primary, success, warning, danger, info
    'size' => 'md', // sm, md
    'rounded' => 'full' // full, md, lg
])

@php
    $baseClasses = 'inline-flex items-center font-semibold';
    
    $sizeClasses = [
        'sm' => 'px-1.5 py-0.5 text-[10px]',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm'
    ][$size] ?? 'px-2.5 py-0.5 text-xs';

    $roundedClasses = [
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
        'full' => 'rounded-full'
    ][$rounded] ?? 'rounded-full';

    $variantClasses = [
        'default' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'primary' => 'bg-blue-100 text-primary-700 dark:bg-blue-900/40 dark:text-blue-300',
        'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'danger' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'info' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
    ][$variant] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';

    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $roundedClasses . ' ' . $variantClasses;
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>






