<div {{ $attributes->merge(['class' => 'rounded border flex flex-col']) }} style="background-color: var(--noc-panel); border-color: var(--noc-border);">
    @if(isset($header))
        <div class="px-4 py-3 border-b flex items-center justify-between flex-none" style="border-color: var(--noc-border);">
            {{ $header }}
        </div>
    @endif
    
    <div class="flex-1 overflow-auto {{ $noPadding ?? false ? '' : 'p-4' }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-4 py-3 border-t flex-none" style="border-color: var(--noc-border); background-color: var(--noc-subpanel);">
            {{ $footer }}
        </div>
    @endif
</div>






