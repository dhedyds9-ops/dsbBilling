@props([
    'name' => 'drawer',
    'title' => null,
    'subtitle' => null,
    'position' => 'right',
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
    'persistBody' => false,
])

@php
$sizeMap = [
    'sm'  => 'w-64 max-w-full',
    'md'  => 'w-80 max-w-full',
    'lg'  => 'w-96 max-w-full',
    'xl'  => 'w-[32rem] max-w-full',
    '2xl' => 'w-[40rem] max-w-full',
    'full' => 'w-full h-full max-w-full max-h-full',
];

$positionAxis = match ($position) {
    'left', 'right' => 'x',
    'top', 'bottom'  => 'y',
    default => 'x',
};

$positionClasses = match ($position) {
    'left'   => 'inset-y-0 left-0 rounded-none rounded-r-3xl',
    'right'  => 'inset-y-0 right-0 rounded-none rounded-l-3xl',
    'top'    => 'inset-x-0 top-0 rounded-none rounded-b-3xl h-auto max-h-[80vh]',
    'bottom' => 'inset-x-0 bottom-0 rounded-none rounded-t-3xl h-auto max-h-[85vh]',
    default => 'inset-y-0 right-0 rounded-none rounded-l-3xl',
};

$translateStart = match ($position) {
    'left'   => '-translate-x-full',
    'right'  => 'translate-x-full',
    'top'    => '-translate-y-full',
    'bottom' => 'translate-y-full',
    default => 'translate-x-full',
};

$sizeClasses = $sizeMap[$size] ?? $sizeMap['md'];
@endphp

<div
    x-data="{ open: false }"
    x-init="
        const syncBodyLock = (val) => {
            if (val) document.body.classList.add('overflow-hidden');
            else document.body.classList.remove('overflow-hidden');
        };
        $watch('open', value => {
            syncBodyLock(value);
            $dispatch(value ? '{{ $name }}-open' : '{{ $name }}-close');
        });
        @if ($closeOnBackdrop)
        $el.addEventListener('mousedown', (e) => { if (e.target === $el) open = false; });
        @endif
        @if ($closeOnEscape)
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && open) open = false; });
        @endif
        syncBodyLock(open);
    "
    @{{ $name }}.window="open = true"
    @{{ $name }}-close.window="open = false"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[70] bg-slate-950/50 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $name }}"
    {{ $attributes }}
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="{{ $translateStart }} opacity-60"
        x-transition:enter-end="translate-x-0 translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 translate-y-0 opacity-100"
        x-transition:leave-end="{{ $translateStart }} opacity-0"
        class="fixed {{ $positionClasses }} {{ $sizeClasses }} bg-[var(--ds-surface)] border-[var(--ds-outline-variant)] {{ in_array($position, ['left','right']) ? 'border-l' : 'border-t' }} shadow-soft-2xl flex flex-col max-h-full"
        @click.stop
        @if ($persistBody) x-ignore @endif
    >
        {{-- Header --}}
        @if (isset($header) || $title)
            <div class="flex items-start justify-between gap-3 px-6 py-4 border-b border-[var(--ds-outline-variant)]">
                <div class="flex-1 min-w-0">
                    @if (isset($header))
                        {{ $header }}
                    @else
                        @if ($title)
                            <h3 class="text-base font-semibold text-[var(--ds-on-surface)] leading-tight">{{ $title }}</h3>
                        @endif
                        @if ($subtitle)
                            <p class="text-xs text-[var(--ds-on-surface-variant)] mt-1 leading-relaxed">{{ $subtitle }}</p>
                        @endif
                    @endif
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="flex-shrink-0 h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] transition-colors"
                    aria-label="Tutup panel"
                >
                    <span class="material-symbols-outlined ms-22">close</span>
                </button>
            </div>
        @endif

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto overflow-x-hidden ds-scrollbar">
            @if (isset($headerExtra))
                <div class="px-6 pt-4">{{ $headerExtra }}</div>
            @endif
            <div class="px-6 py-5">
                {{ $slot }}
            </div>
        </div>

        {{-- Footer --}}
        @if (isset($footer))
            <div class="px-6 py-4 border-t border-[var(--ds-outline-variant)] bg-[var(--ds-surface-container-low)] flex flex-wrap items-center justify-end gap-2">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>






