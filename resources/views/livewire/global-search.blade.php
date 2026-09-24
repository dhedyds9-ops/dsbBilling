<div class="relative" x-data="{ open: @entangle('isOpen') }" @keydown.escape.window="open = false" @click.away="open = false">
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input 
            wire:model.live.debounce.300ms="query"
            @focus="open = true"
            type="text" 
            placeholder="Search invoices- tickets..." 
            class="pl-10 pr-4 py-2 w-full lg:w-64 bg-slate-100 dark:bg-slate-700 border-transparent focus:bg-white dark:bg-slate-800 dark:focus:bg-slate-800 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors placeholder-slate-400 dark:placeholder-slate-500 outline-none dark:bg-slate-900 dark:text-slate-100"
        >
        
        <div wire:loading wire:target="query" class="absolute right-3 top-1/2 -translate-y-1/2">
            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Search Results Dropdown -->
    <div 
        x-show="open && $wire.query.length >= 2"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 lg:left-0 mt-2 w-screen max-w-sm lg:w-96 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-soft-lg overflow-hidden z-50"
        style="display: none;"
    >
        @if(count($results) > 0)
            <div class="max-h-96 overflow-y-auto py-2">
                @foreach(collect($results)->groupBy('type') as $type => $groupResults)
                    <div class="px-4 py-1 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-750">
                        {{ ucfirst($type) }}
                    </div>
                    
                    @foreach($groupResults as $result)
                        <button 
                            wire:click="goToResult('{{ $result['url'] }}')"
                            class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/50 flex items-start gap-3 transition-colors border-b border-slate-100 dark:border-slate-700/50 last:border-0"
                        >
                            <div class="mt-0.5 p-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-500 dark:text-blue-400 rounded-lg">
                                <x-icon :name="$result['icon'] ?? 'document'" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $result['title'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $result['subtitle'] }}</p>
                            </div>
                        </button>
                    @endforeach
                @endforeach
            </div>
        @else
            <div class="px-4 py-8 text-center" wire:loading.remove wire:target="query">
                <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">Pencarian tidak ditemukan</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tidak ada hasil untuk "{{ $query }}".</p>
            </div>
        @endif
        
        <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-750/50 flex justify-between items-center text-xs text-slate-500 dark:text-slate-400">
            <span>Tekan <kbd class="px-1 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600 font-sans">Esc</kbd> untuk menutup</span>
        </div>
    </div>
</div>






