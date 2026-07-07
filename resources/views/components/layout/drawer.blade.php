{{--
/**
 * Drawer Component - Enterprise Design System
 *
 * Positions: left, right, top, bottom
 */
--}}

@props([
    'name' => 'drawer',
    'title' => null,
    'position' => 'right',
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
])

@php
$sizeMap = [
    'sm' => 'w-64 max-w-full',
    'md' => 'w-80 max-w-full',
    'lg' => 'w-96 max-w-full',
    'xl' => 'w-[32rem] max-w-full',
    '2xl' => 'w-[40rem] max-w-full',
    'full' => 'w-full h-full max-w-full max-h-full',
];

$positionClasses = match ($position) {
    'left' => 'inset-y-0 left-0 rounded-none',
    'right' => 'inset-y-0 right-0 rounded-none',
    'top' => 'inset-x-0 top-0 rounded-none',
    'bottom' => 'inset-x-0 bottom-0 rounded-none',
};

$sizeClasses = $sizeMap[$size] ?? $sizeMap['md'];

$translateClasses = match ($position) {
    'left', 'top' => '-translate-x-full',
    'right', 'bottom' => 'translate-x-full',
    default => '',
};
@endphp

<div
    x-data="{ open: false }"
    x-init="
        $watch('open', value => {
            if (value) {
                document.body.classList.add('overflow-hidden');
                $dispatch('{{ $name }}-open');
            } else {
                document.body.classList.remove('overflow-hidden');
                $dispatch('{{ $name }}-close');
            }
        });
        @if ($closeOnBackdrop)
            $el.addEventListener('click', (e) => {
                if (e.target === $el) open = false;
            });
        @endif
        @if ($closeOnEscape)
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && open) open = false;
            });
        @endif
    "
    @{{ $name }}.window="open = true"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    {{ $attributes }}
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="{{ $position === 'left' || $position === 'top' ? '-translate-x-full' : 'translate-x-full' }}"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="{{ $position === 'left' || $position === 'top' ? '-translate-x-full' : 'translate-x-full' }}"
        class="fixed {{ $positionClasses }} {{ $sizeClasses }} bg-white shadow-soft-xl flex flex-col max-h-full"
        @click.stop
    >
        {{-- Header --}}
        @if (isset($header) || $title)
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <div>
                    @if ($title)
                        <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
                    @endif
                    {{ $header ?? '' }}
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                    aria-label="Close drawer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-4">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if (isset($footer))
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
