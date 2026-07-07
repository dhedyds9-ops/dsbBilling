{{--
/**
 * Modal Component - Enterprise Design System
 *
 * Features:
 * - Sizes: sm, md, lg, xl, 2xl, full
 * - Close on escape, backdrop click
 * - Animated entrance/exit
 * - Header, body, footer slots
 */
--}}

@props([
    'name' => 'modal',
    'title' => null,
    'size' => 'md',
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
    'showClose' => true,
    'static' => false,
])

@php
$sizes = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
    '4xl' => 'max-w-4xl',
    'full' => 'max-w-full mx-4',
];

$sizeClasses = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    x-data="{ show: false, {{ $name }}: null }"
    x-init="
        $watch('show', value => {
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
                if (e.target === $el) show = false;
            });
        @endif
        @if ($closeOnEscape)
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && show) show = false;
            });
        @endif
    "
    @{{ $name }}.window="show = true; {{ $name }} = $event.detail;"
    @{{ $name }}-close.window="show = false"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    {{ $attributes }}
>
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="{{ $sizeClasses }} w-full bg-white rounded-2xl shadow-soft-xl border border-slate-200 max-h-[90vh] flex flex-col"
        @click.stop
    >
        {{-- Header --}}
        @if (isset($header) || $showClose || $title)
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <div>
                    @if ($title)
                        <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
                    @endif
                    {{ $header ?? '' }}
                </div>

                @if ($showClose)
                    <button
                        type="button"
                        @click="show = false"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                        aria-label="Close modal"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
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
