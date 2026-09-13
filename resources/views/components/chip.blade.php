@props([
    'label' => '',
    'color' => 'slate',
])

@php
    $classes = [
        'slate' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
        'blue' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-700',
        'emerald' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700',
        'amber' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700',
        'red' => 'bg-red-100 dark:bg-red-900/50 text-red-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2 py-0.5 text-xs font-medium ' . ($classes[$color] ?? $classes['slate'])]) }}>
    {{ $label }}
</span>
