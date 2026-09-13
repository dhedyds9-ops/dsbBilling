<div
    {{ $attributes->class([
        'relative bg-ds-surface border border-slate-200 dark:border-slate-700 rounded-2xl shadow-soft-sm overflow-hidden',
    ]) }}
>
    @isset($header)
        <div
            class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3
                   border-b border-slate-200 dark:border-slate-700 bg-ds-surface-container-low/40"
        >
            {{ $header }}
        </div>
    @endisset

    <div class="overflow-x-auto">
        {{ $slot }}
    </div>

    @isset($footer)
        <div
            class="px-4 sm:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-ds-surface-container-low/40"
        >
            {{ $footer }}
        </div>
    @endisset
</div>






