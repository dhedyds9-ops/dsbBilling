<?php

namespace App\Volt\Components;

use Livewire\Volt\Component;

new class extends Component {
    public array $filters = [];
    public array $activeFilters = [];

    public function mount(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function toggleFilter(string $key, $value)
    {
        if (!isset($this->activeFilters[$key])) {
            $this->activeFilters[$key] = [];
        }

        $index = array_search($value, $this->activeFilters[$key]);
        if ($index !== false) {
            unset($this->activeFilters[$key][$index]);
            $this->activeFilters[$key] = array_values($this->activeFilters[$key]);
        } else {
            $this->activeFilters[$key][] = $value;
        }

        $this->dispatch('filters-changed', $this->activeFilters)->to(GlobalFilter::class);
    }

    public function clearFilter(string $key)
    {
        if (isset($this->activeFilters[$key])) {
            unset($this->activeFilters[$key]);
            $this->dispatch('filters-changed', $this->activeFilters)->to(GlobalFilter::class);
        }
    }

    public function clearAllFilters()
    {
        $this->activeFilters = [];
        $this->dispatch('filters-changed', [])->to(GlobalFilter::class);
    }

    public function isFilterActive(string $key, $value): bool
    {
        return isset($this->activeFilters[$key]) && in_array($value, $this->activeFilters[$key]);
    }

    public function getActiveFilterCount(): int
    {
        return count($this->activeFilters, COUNT_RECURSIVE) - count($this->activeFilters);
    }
};

?>

<div class="space-y-3" x-data="{ open: false }">
    @if(count($filters) > 0)
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters
                    @if($this->getActiveFilterCount() > 0)
                        <span class="px-2 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs rounded-full">
                            {{ $this->getActiveFilterCount() }}
                        </span>
                    @endif
                </button>

                @if(count($activeFilters) > 0)
                    <button
                        type="button"
                        wire:click="clearAllFilters"
                        class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300"
                    >
                        Clear all
                    </button>
                @endif
            </div>
        </div>
    @endif

    <div
        x-show="open"
        x-transition
        class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-soft-lg p-4"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($filters as $filterKey => $filter)
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        {{ $filter['label'] ?? $filterKey }}
                    </label>

                    @if(($filter['type'] ?? 'select') === 'select')
                        <select
                            wire:change="toggleFilter('{{ $filterKey }}', $event.target.value)"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-sm"
                        >
                            <option value="">All</option>
                            @foreach($filter['options'] ?? [] as $option)
                                <option value="{{ is_array($option) ? $option['value'] : $option }}">
                                    {{ is_array($option) ? $option['label'] : $option }}
                                </option>
                            @endforeach
                        </select>
                    @elseif(($filter['type'] ?? '') === 'multiselect')
                        <div class="space-y-1">
                            @foreach($filter['options'] ?? [] as $option)
                                <?php $value = is_array($option) ? $option['value'] : $option; ?>
                                <?php $label = is_array($option) ? $option['label'] : $option; ?>
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        wire:change="toggleFilter('{{ $filterKey }}', '{{ $value }}')"
                                        :checked="{{ json_encode($this->isFilterActive($filterKey, $value)) }}"
                                        class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if(count($activeFilters) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($activeFilters as $filterKey => $values)
                @foreach($values as $value)
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs rounded-full">
                        {{ $filterKey }}: {{ $value }}
                        <button
                            type="button"
                            wire:click="toggleFilter('{{ $filterKey }}', '{{ $value }}')"
                            class="ml-1 hover:text-primary-900 dark:hover:text-primary-100"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </span>
                @endforeach
            @endforeach
        </div>
    @endif
</div>
