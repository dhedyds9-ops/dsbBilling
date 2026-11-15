@props([
    'availableItems' => [],
    'selectedId' => null,
    'wireModel' => '',
    'placeholder' => '-- Cari / Pilih Barang dari Katalog --',
    'required' => false,
])

@php
    $selectedItem = null;
    if ($selectedId) {
        $selectedItem = collect($availableItems)->firstWhere('id', (int)$selectedId);
    }
    $itemsList = collect($availableItems)->map(function($it) {
        return [
            'id' => (int) $it->id,
            'sku' => (string) ($it->sku ?? ''),
            'name' => (string) ($it->name ?? ''),
            'category' => (string) ($it->category ?? ''),
            'unit' => (string) ($it->unit ?? 'unit'),
            'is_serialized' => (bool) $it->is_serialized,
            'search_text' => strtolower(trim(($it->sku ?? '') . ' ' . ($it->name ?? '') . ' ' . ($it->category ?? ''))),
        ];
    })->values()->toArray();
@endphp

<div class="relative w-full"
     x-data="{
         open: false,
         search: '',
         selectedId: @js($selectedId),
         items: @js($itemsList),
         get filteredItems() {
             if (!this.search.trim()) return this.items;
             const q = this.search.toLowerCase().trim();
             return this.items.filter(i => i.search_text.includes(q));
         },
         get currentItem() {
             return this.items.find(i => String(i.id) === String(this.selectedId)) || null;
         },
         select(item) {
             this.selectedId = item.id;
             this.open = false;
             this.search = '';
             $wire.set('{{ $wireModel }}', item.id);
         },
         clear() {
             this.selectedId = null;
             this.open = false;
             this.search = '';
             $wire.set('{{ $wireModel }}', null);
         }
     }"
     @click.outside="open = false"
     @keydown.escape.window="open = false">

    {{-- Trigger Button --}}
    <div @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()); }"
         class="w-full min-h-[38px] py-1.5 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 shadow-xs cursor-pointer flex items-center justify-between gap-2 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
        
        <div class="flex items-center gap-1.5 truncate flex-1 min-w-0">
            <template x-if="currentItem">
                <div class="flex items-center gap-1.5 truncate">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 shrink-0" x-text="currentItem.sku"></span>
                    <span class="truncate font-semibold text-slate-900 dark:text-white" x-text="currentItem.name"></span>
                    <span class="text-[11px] text-slate-400 shrink-0" x-text="'(' + currentItem.category + ' • ' + currentItem.unit + ')'"></span>
                    <template x-if="currentItem.is_serialized">
                        <span class="px-1 py-0.2 rounded text-[9px] font-bold bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shrink-0">Wajib SN</span>
                    </template>
                </div>
            </template>
            <template x-if="!currentItem">
                <span class="text-slate-400 dark:text-slate-500 text-xs flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">search</span>
                    <span>{{ $placeholder }}</span>
                </span>
            </template>
        </div>

        <div class="flex items-center gap-1 shrink-0">
            <button type="button" x-show="currentItem" @click.stop="clear()" title="Hapus pilihan"
                    class="p-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-rose-500">
                <span class="material-symbols-outlined text-sm leading-none">close</span>
            </button>
            <span class="material-symbols-outlined text-sm text-slate-400 transition-transform duration-200"
                  :class="{ 'rotate-180': open }">expand_more</span>
        </div>
    </div>

    {{-- Dropdown Floating Menu --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl overflow-hidden"
         style="display: none;">
        
        {{-- Search Input inside Dropdown --}}
        <div class="p-2 border-b border-slate-100 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-900/50">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text"
                       x-ref="searchInput"
                       x-model="search"
                       @keydown.enter.prevent="if(filteredItems.length > 0) select(filteredItems[0])"
                       placeholder="Cari SKU, Nama Barang, atau Kategori..."
                       class="w-full pl-8 pr-7 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-xs">close</span>
                </button>
            </div>
        </div>

        {{-- Scrollable Items List --}}
        <div class="max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
            <template x-for="item in filteredItems" :key="item.id">
                <div @click="select(item)"
                     class="p-2.5 hover:bg-blue-50 dark:hover:bg-blue-950/40 cursor-pointer transition-colors flex items-center justify-between gap-2"
                     :class="{ 'bg-blue-50/70 dark:bg-blue-950/30': String(item.id) === String(selectedId) }">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300" x-text="item.sku"></span>
                            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate" x-text="item.name"></span>
                            <template x-if="item.is_serialized">
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">Wajib SN</span>
                            </template>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                            <span x-text="'Kategori: ' + item.category"></span>
                            <span>•</span>
                            <span x-text="'Satuan: ' + item.unit"></span>
                        </div>
                    </div>
                    <span x-show="String(item.id) === String(selectedId)" class="material-symbols-outlined text-base text-blue-600 dark:text-blue-400 shrink-0">check_circle</span>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="filteredItems.length === 0" class="p-4 text-center text-xs text-slate-400 dark:text-slate-500">
                <span class="material-symbols-outlined text-2xl block mb-1 opacity-60">search_off</span>
                <span>Tidak ada barang yang cocok dengan "<span x-text="search" class="font-bold"></span>"</span>
            </div>
        </div>
    </div>
</div>
