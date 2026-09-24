@props([
    'field',
    'current' => null,
    'dir' => 'asc',
])

@if ($current === $field)
    <span class="ml-1 inline-block text-slate-700 dark:text-slate-300" aria-label="Diurutkan {{ $dir === 'asc' ? 'menaik' : 'menurun' }}">
        {{ $dir === 'asc' ? '↑' : '↓' }}
    </span>
@else
    <span class="ml-1 inline-block text-slate-400" aria-hidden="true">↕</span>
@endif
