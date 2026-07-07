{{--
/**
 * Breadcrumbs Component
 */
--}}

@props([
    'breadcrumbs' => [],
])

@if(!empty($breadcrumbs))
    <nav class="hidden md:flex items-center gap-2 text-sm" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2">
            <li>
                <a href="/" class="text-slate-500 hover:text-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
            </li>

            @foreach($breadcrumbs as $index => $crumb)
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>

                    @if(isset($crumb['url']) && $index < count($breadcrumbs) - 1)
                        <a
                            href="{{ $crumb['url'] }}"
                            class="text-slate-500 hover:text-slate-700 transition-colors"
                        >
                            {{ $crumb['label'] }}
                        </a>
                    @else
                        <span class="text-slate-900 font-medium">
                            {{ $crumb['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
