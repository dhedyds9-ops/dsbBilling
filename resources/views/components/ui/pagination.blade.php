@props([
    'paginator' => null,
    'currentPage' => null,
    'totalPages' => null,
    'totalItems' => null,
    'perPage' => 15,
    'from' => null,
    'to' => null,
    'showInfo' => true,
    'simple' => false,
    'livewireTarget' => null,
])

@php
if ($paginator && $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
    $currentPage = $currentPage ?? $paginator->currentPage();
    $totalPages = $totalPages ?? $paginator->lastPage();
    $totalItems = $totalItems ?? $paginator->total();
    $perPage = $perPage ?? $paginator->perPage();
    $from = $from ?? $paginator->firstItem();
    $to = $to ?? $paginator->lastItem();
}

$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalItems = $totalItems ?? 0;
$from = $from ?? (($totalItems > 0) ? (($currentPage - 1) * $perPage + 1) : 0);
$to = $to ?? min($currentPage * $perPage, $totalItems);

$hasPages = $totalPages > 1;
$showEllipsisStart = $currentPage > 4;
$showEllipsisEnd = $currentPage < $totalPages - 3;

$wireClick = function (string $method, int $page = 1) use ($livewireTarget): string {
    if ($livewireTarget) {
        return "wire:click=\"\$set('{$livewireTarget}', {$page})\"";
    }
    if ($paginator && method_exists($paginator, 'hasMorePages')) {
        return "wire:click=\"{$method}({$page})\"";
    }
    return "wire:click=\"{$method}({$page})\"";
};
@endphp

@if ($hasPages || $showInfo)
    <nav class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
        {{-- Info --}}
        @if ($showInfo)
            <div class="text-sm text-[var(--ds-on-surface-variant)] whitespace-nowrap">
                Menampilkan
                <span class="font-semibold text-[var(--ds-on-surface)]">{{ $from ?: 0 }}</span>
                <span>—</span>
                <span class="font-semibold text-[var(--ds-on-surface)]">{{ $to ?: 0 }}</span>
                <span>dari</span>
                <span class="font-semibold text-[var(--ds-on-surface)]">{{ $totalItems }}</span>
                <span>entri</span>
            </div>
        @endif

        {{-- Pagination Buttons --}}
        @if ($hasPages)
            <div class="flex items-center gap-1">
                {{-- First Page --}}
                @if (!$simple && $currentPage > 2)
                    <button
                        type="button"
                        {!! $wireClick('gotoPage', 1) !!}
                        class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] transition-colors"
                        aria-label="Halaman pertama"
                    >
                        <span class="material-symbols-outlined ms-20">first_page</span>
                    </button>
                @endif

                {{-- Previous Page --}}
                <button
                    type="button"
                    @if ($currentPage > 1) {!! $wireClick('gotoPage', $currentPage - 1) !!} @endif
                    @if ($currentPage === 1) disabled @endif
                    class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent transition-colors"
                    aria-label="Halaman sebelumnya"
                >
                    <span class="material-symbols-outlined ms-20">chevron_left</span>
                </button>

                {{-- Page Numbers (non simple) --}}
                @if (!$simple)
                    @php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        if ($showEllipsisStart) $start = max(2, $currentPage - 1);
                        if ($showEllipsisEnd) $end = min($totalPages - 1, $currentPage + 1);
                    @endphp

                    @if ($showEllipsisStart)
                        <button type="button" {!! $wireClick('gotoPage', 1) !!} class="h-9 min-w-[2.25rem] px-2.5 inline-flex items-center justify-center rounded-xl text-sm font-medium text-[var(--ds-on-surface-variant)] hover:bg-[var(--ds-surface-container-high)] transition-colors">
                            1
                        </button>
                        <span class="px-1 text-[var(--ds-outline)] text-sm select-none">…</span>
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        @php
                            $isActive = $currentPage === $i;
                        @endphp
                        <button
                            type="button"
                            {!! $wireClick('gotoPage', $i) !!}
                            @class([
                                'h-9 min-w-[2.25rem] px-2.5 inline-flex items-center justify-center rounded-xl text-sm font-medium transition-all',
                                'bg-[var(--ds-primary)] text-[var(--ds-on-primary)] shadow-soft-md' => $isActive,
                                'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)]' => !$isActive,
                            ])
                            @if ($isActive) aria-current="page" @endif
                        >
                            {{ $i }}
                        </button>
                    @endfor

                    @if ($showEllipsisEnd)
                        <span class="px-1 text-[var(--ds-outline)] text-sm select-none">…</span>
                        <button type="button" {!! $wireClick('gotoPage', $totalPages) !!} class="h-9 min-w-[2.25rem] px-2.5 inline-flex items-center justify-center rounded-xl text-sm font-medium text-[var(--ds-on-surface-variant)] hover:bg-[var(--ds-surface-container-high)] transition-colors">
                            {{ $totalPages }}
                        </button>
                    @endif
                @else
                    <span class="px-3 text-sm font-medium text-[var(--ds-on-surface)]">
                        Hal. {{ $currentPage }} / {{ $totalPages }}
                    </span>
                @endif

                {{-- Next Page --}}
                <button
                    type="button"
                    @if ($currentPage < $totalPages) {!! $wireClick('gotoPage', $currentPage + 1) !!} @endif
                    @if ($currentPage === $totalPages) disabled @endif
                    class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent transition-colors"
                    aria-label="Halaman berikutnya"
                >
                    <span class="material-symbols-outlined ms-20">chevron_right</span>
                </button>

                {{-- Last Page --}}
                @if (!$simple && $currentPage < $totalPages - 1)
                    <button
                        type="button"
                        {!! $wireClick('gotoPage', $totalPages) !!}
                        class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] transition-colors"
                        aria-label="Halaman terakhir"
                    >
                        <span class="material-symbols-outlined ms-20">last_page</span>
                    </button>
                @endif
            </div>
        @endif
    </nav>
@endif






