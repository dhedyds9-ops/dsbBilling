@props([
    'title' => null,
    'description' => null,
    'padding' => 'p-5' // can be customized, e.g., 'p-0' for tables
])

<div {{ $attributes->merge(['class' => "bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm $padding"]) }}>
    @if(isset($header))
        <div class="mb-4">
            {{ $header }}
        </div>
    @elseif($title || $description)
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                @if($title)
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $title }}</h2>
                @endif
                @if($description)
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $description }}</p>
                @endif
            </div>
            
            @if(isset($actions))
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    
    {{ $slot }}
    
    @if(isset($footer))
        <div class="mt-5 pt-4 border-t border-slate-200 dark:border-slate-700">
            {{ $footer }}
        </div>
    @endif
</div>






