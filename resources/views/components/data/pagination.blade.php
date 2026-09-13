{{--
/**
 * Pagination Component - Enterprise Design System
 *
 * Features:
 * - Page numbers with ellipsis
 * - Previous/Next buttons
 * - First/Last page buttons
 * - Items per page selector
 * - Simple mode for few pages
 */
--}}

@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'totalItems' => 0,
    'perPage' => 15,
    'from' => null,
    'to' => null,
    'showInfo' => true,
    'simple' => false,
])

@php
$from = $from ?? (($currentPage - 1) * $perPage + 1);
$to = $to ?? min($currentPage * $perPage, $totalItems);

$hasPages = $totalPages > 1;
$showEllipsisStart = $currentPage > 4;
$showEllipsisEnd = $currentPage < $totalPages - 3;
@endphp

@if ($hasPages || $showInfo)
    <nav class="flex items-center justify-between">
        {{-- Info --}}
        @if ($showInfo)
            <div class="text-sm text-slate-600 dark:text-slate-400">
                Showing
                <span class="font-medium">{{ $from }}</span>
                to
                <span class="font-medium">{{ $to }}</span>
                of
                <span class="font-medium">{{ $totalItems }}</span>
                results
            </div>
        @endif

        {{-- Pagination Buttons --}}
        @if (!$simple && $hasPages)
            <div class="flex items-center gap-1">
                {{-- First Page --}}
                @if ($currentPage > 1)
                    <button
                        wire:click="gotoPage(1)"
                        class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:bg-slate-800 rounded-lg transition-colors"
                        aria-label="First page"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>
                @endif

                {{-- Previous Page --}}
                @if ($currentPage > 1)
                    <button
                        wire:click="gotoPage({{ $currentPage - 1 }})"
                        class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:bg-slate-800 rounded-lg transition-colors"
                        aria-label="Previous page"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                @endif

                {{-- Page Numbers --}}
                @if (!$simple)
                    @php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);

                        if ($showEllipsisStart) $start = max(2, $currentPage - 1);
                        if ($showEllipsisEnd) $end = min($totalPages - 1, $currentPage + 1);
                    @endphp

                    {{-- First page + ellipsis --}}
                    @if ($showEllipsisStart)
                        <button
                            wire:click="gotoPage(1)"
                            class="min-w-[2.5rem] h-10 px-3 text-sm font-medium {{ $currentPage === 1 ? 'bg-primary-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-800' }} rounded-lg transition-colors"
                        >
                            1
                        </button>
                        <span class="px-2 text-slate-400">...</span>
                    @endif

                    {{-- Page range --}}
                    @for ($i = $start; $i <= $end; $i++)
                        <button
                            wire:click="gotoPage({{ $i }})"
                            class="min-w-[2.5rem] h-10 px-3 text-sm font-medium {{ $currentPage === $i ? 'bg-primary-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-800' }} rounded-lg transition-colors"
                        >
                            {{ $i }}
                        </button>
                    @endfor

                    {{-- Ellipsis + last page --}}
                    @if ($showEllipsisEnd)
                        <span class="px-2 text-slate-400">...</span>
                        <button
                            wire:click="gotoPage({{ $totalPages }})"
                            class="min-w-[2.5rem] h-10 px-3 text-sm font-medium {{ $currentPage === $totalPages ? 'bg-primary-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-800' }} rounded-lg transition-colors"
                        >
                            {{ $totalPages }}
                        </button>
                    @endif
                @endif

                {{-- Next Page --}}
                @if ($currentPage < $totalPages)
                    <button
                        wire:click="gotoPage({{ $currentPage + 1 }})"
                        class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:bg-slate-800 rounded-lg transition-colors"
                        aria-label="Next page"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                @endif

                {{-- Last Page --}}
                @if ($currentPage < $totalPages)
                    <button
                        wire:click="gotoPage({{ $totalPages }})"
                        class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:bg-slate-800 rounded-lg transition-colors"
                        aria-label="Last page"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                        </svg>
                    </button>
                @endif
            </div>
        @elseif ($simple)
            {{-- Simple Pagination --}}
            <div class="flex items-center gap-2">
                <button
                    wire:click="gotoPage({{ $currentPage - 1 }})"
                    @if ($currentPage === 1) disabled @endif
                    class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Previous
                </button>

                <span class="text-sm text-slate-600 dark:text-slate-400">
                    Page {{ $currentPage }} of {{ $totalPages }}
                </span>

                <button
                    wire:click="gotoPage({{ $currentPage + 1 }})"
                    @if ($currentPage === $totalPages) disabled @endif
                    class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Next
                </button>
            </div>
        @endif
    </nav>
@endif
