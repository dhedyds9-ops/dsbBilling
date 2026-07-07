<?php

namespace App\Volt\Components;

use Livewire\Volt\Component;

new class extends Component {
    public array $searchHistory = [];
    public array $suggestions = [];
    public string $query = '';
    public bool $isOpen = false;

    public function updatedQuery()
    {
        if (strlen($this->query) >= 2) {
            $this->suggestions = $this->performSearch($this->query);
            $this->isOpen = true;
        } else {
            $this->suggestions = [];
            $this->isOpen = false;
        }
    }

    public function performSearch(string $query): array
    {
        return [];
    }

    public function selectSuggestion(array $suggestion)
    {
        $this->query = $suggestion['text'] ?? '';
        $this->isOpen = false;

        if (!in_array($this->query, $this->searchHistory)) {
            $this->searchHistory[] = $this->query;
        }

        $this->dispatch('search', query: $this->query)->to(GlobalSearch::class);
    }

    public function clearHistory()
    {
        $this->searchHistory = [];
    }
};

?>

<div class="relative" x-data="{ open: @entangle('isOpen') }">
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>

        <input
            type="text"
            wire:model.live="query"
            placeholder="Search..."
            class="w-full pl-10 pr-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all"
            @focus="open = true"
            @keydown.escape="open = false"
            @click.away="open = false"
        />
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 w-full bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-soft-lg overflow-hidden"
        style="display: none;"
    >
        @if(!empty($suggestions))
            <div class="py-2">
                @foreach($suggestions as $suggestion)
                    <button
                        type="button"
                        wire:click="selectSuggestion({{ json_encode($suggestion) }})"
                        class="w-full flex items-center gap-3 px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        @if(isset($suggestion['icon']))
                            <x-icon :name="$suggestion['icon']" class="w-4 h-4 text-slate-400" />
                        @else
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        @endif
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $suggestion['text'] ?? '' }}</span>
                        @if(isset($suggestion['type']))
                            <x-badge size="sm" variant="default">{{ $suggestion['type'] }}</x-badge>
                        @endif
                    </button>
                @endforeach
            </div>
        @elseif(strlen($query) >= 2)
            <div class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                <p class="text-sm">No results found for "{{ $query }}"</p>
            </div>
        @else
            @if(!empty($searchHistory))
                <div class="py-2">
                    <div class="flex items-center justify-between px-4 py-1">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Recent Searches</span>
                        <button type="button" wire:click="clearHistory" class="text-xs text-primary-600 hover:text-primary-700">
                            Clear
                        </button>
                    </div>
                    @foreach($searchHistory as $history)
                        <button
                            type="button"
                            wire:click="$set('query', '{{ $history }}')"
                            class="w-full flex items-center gap-3 px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                        >
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $history }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
