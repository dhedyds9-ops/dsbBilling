{{--
/**
 * Data Table Component - Enterprise Design System
 *
 * Features:
 * - Column headers with sorting
 * - Row hover effects
 * - Loading state with skeleton
 * - Empty state
 * - Striped rows
 * - Compact mode
 */
--}}

@props([
    'columns' => [],
    'data' => [],
    'sortable' => false,
    'striped' => false,
    'hover' => true,
    'compact' => false,
    'loading' => false,
    'emptyMessage' => 'No data available',
    'emptyIcon' => 'table-cells',
])

<div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
    <table class="w-full {{ $compact ? 'text-sm' : 'text-base' }}">
        {{-- Head --}}
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-800/50">
                @if (isset($head))
                    {{ $head }}
                @else
                    @foreach ($columns as $column)
                        <th
                            class="{{ $compact ? 'px-4 py-2' : 'px-6 py-3' }} text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider {{ ($sortable && ($column['sortable'] ?? true)) ? 'cursor-pointer hover:text-slate-900 dark:text-slate-100' : '' }}"
                            @if ($sortable && ($column['sortable'] ?? true))
                                x-data="{ sortDir: null }"
                                @click="$dispatch('sort', { field: '{{ $column['field'] ?? $column['key'] ?? '' }}', dir: sortDir === 'asc' ? 'desc' : 'asc' })"
                            @endif
                        >
                            <div class="flex items-center gap-2">
                                {{ $column['label'] ?? $column['key'] ?? '' }}
                                @if ($sortable && ($column['sortable'] ?? true))
                                    <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                    @endforeach
                @endif
            </tr>
        </thead>

        {{-- Body --}}
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            @if (trim($slot))
                {{ $slot }}
            @elseif ($loading)
                @for ($i = 0; $i < 5; $i++)
                    <tr class="{{ $striped ? ($i % 2 === 0 ? 'bg-white dark:bg-slate-800' : 'bg-slate-50 dark:bg-slate-800/50/50') : ($hover ? 'hover:bg-slate-50 dark:hover:bg-slate-800/80 dark:bg-slate-800/50' : '') }}">
                        @foreach ($columns as $column)
                            <td class="{{ $compact ? 'px-4 py-2' : 'px-6 py-4' }}">
                                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
                            </td>
                        @endforeach
                    </tr>
                @endfor
            @elseif (count($data) === 0)
                <tr>
                    <td colspan="{{ count($columns) > 0 ? count($columns) : 1 }}" class="{{ $compact ? 'px-4 py-8' : 'px-6 py-12' }}">
                        <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                            <svg class="w-12 h-12 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                            <p>{{ $emptyMessage }}</p>
                        </div>
                    </td>
                </tr>
            @else
                @foreach ($data as $index => $row)
                    <tr class="{{ $striped ? ($index % 2 === 0 ? 'bg-white dark:bg-slate-800' : 'bg-slate-50 dark:bg-slate-800/50/50') : ($hover ? 'hover:bg-slate-50 dark:hover:bg-slate-800/80 dark:bg-slate-800/50' : '') }}">
                        @foreach ($columns as $column)
                            <td class="{{ $compact ? 'px-4 py-2' : 'px-6 py-4' }} text-sm text-slate-700 dark:text-slate-300">
                                @if (isset($column['slot']))
                                    {{ $column['slot']($row) }}
                                @else
                                    {{ data_get($row, $column['field'] ?? $column['key'] ?? '', '') }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>







