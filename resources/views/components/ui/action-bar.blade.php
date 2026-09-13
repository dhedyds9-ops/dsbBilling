<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-4 p-3 mb-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm']) }}>
    <div class="flex flex-wrap items-center gap-2">
        @if(isset($label))
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider ml-2 mr-2">{{ $label }}</span>
        @endif
        {{ $slot }}
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>






