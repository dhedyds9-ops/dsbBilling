{{--
/**
 * DsSearchableSelect Component — dsBilling Standardized Searchable Dropdown
 *
 * Strategies:
 * - 'local'  : Filters predefined options in-memory via Alpine.js.
 * - 'remote' : Debounced server-side search via Livewire action ($wire.call(searchAction, query)).
 *
 * Supports:
 * - Full keyboard navigation (Arrows, Enter, Escape, Tab)
 * - Auto-focus on open
 * - Debounced remote requests (300ms) with stale-request discard (requestId counter)
 * - Clean loading, error, and empty states
 * - Two-way Livewire model entanglement (wire:model / wire:model.live)
 * - Dark mode and mobile responsive styling
 */
--}}

@props([
    'strategy' => 'local',           // 'local' | 'remote'
    'options' => [],                 // Array of options or key-value pairs
    'optionValue' => 'value',        // Key for option value
    'optionLabel' => 'label',        // Key for option label
    'optionSublabel' => 'sublabel',  // Key for option sublabel
    'searchAction' => '',            // Livewire method name for remote search
    'searchable' => true,            // Show search input inside dropdown
    'clearable' => true,             // Allow clearing selection
    'disabled' => false,             // Disabled state
    'placeholder' => '-- Pilih --',  // Placeholder text
    'searchPlaceholder' => 'Cari...',// Search input placeholder
    'selectedLabel' => null,         // Explicit selected label (useful for remote mode)
    'selectedSublabel' => null,      // Explicit selected sublabel
    'label' => null,                 // Optional form label above dropdown
    'hint' => null,                  // Optional helper text below
    'error' => null,                 // Error message string
    'required' => false,             // Required indicator
    'maxResults' => 20,              // Limit results displayed
    'debounce' => 300,               // Debounce in ms for remote strategy
    'id' => null,
])

@php
    $componentId = $id ?? 'ds-select-' . \Illuminate\Support\Str::random(8);
    $wireModel = $attributes->wire('model')->value();
    $isLivewire = !empty($wireModel);

    // Normalize options for local strategy
    $normalizedOptions = [];
    if (!empty($options)) {
        foreach ($options as $key => $opt) {
            if (is_array($opt) || is_object($opt)) {
                $opt = (array) $opt;
                $val = (string) ($opt[$optionValue] ?? $opt['value'] ?? $opt['id'] ?? $key);
                $lbl = (string) ($opt[$optionLabel] ?? $opt['label'] ?? $opt['name'] ?? $opt['title'] ?? '');
                $sub = (string) ($opt[$optionSublabel] ?? $opt['sublabel'] ?? $opt['description'] ?? $opt['phone'] ?? $opt['code'] ?? '');
                $normalizedOptions[] = ['value' => $val, 'label' => $lbl, 'sublabel' => $sub];
            } else {
                $normalizedOptions[] = ['value' => (string) $key, 'label' => (string) $opt, 'sublabel' => ''];
            }
        }
    }
@endphp

<div
    class="w-full space-y-1.5"
    x-data="dsSearchableSelect({
        strategy: '{{ $strategy }}',
        @if($isLivewire)
            value: @entangle($attributes->wire('model')),
        @else
            value: '{{ addslashes($attributes->get('value', '')) }}',
        @endif
        options: {{ Js::from($normalizedOptions) }},
        searchAction: '{{ $searchAction }}',
        searchable: {{ $searchable ? 'true' : 'false' }},
        clearable: {{ $clearable ? 'true' : 'false' }},
        disabled: {{ $disabled ? 'true' : 'false' }},
        debounce: {{ (int) $debounce }},
        maxResults: {{ (int) $maxResults }},
        placeholder: '{{ addslashes($placeholder) }}',
        searchPlaceholder: '{{ addslashes($searchPlaceholder) }}',
        selectedLabel: '{{ addslashes($selectedLabel ?? '') }}',
        selectedSublabel: '{{ addslashes($selectedSublabel ?? '') }}',
    })"
    @keydown.escape.stop="close()"
    @click.outside="close()"
    id="{{ $componentId }}"
>
    {{-- Form Label --}}
    @if ($label)
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Trigger & Hidden Value Container --}}
    <div class="relative">
        {{-- Hidden Input for Standard Form Submits --}}
        @if(!$isLivewire && $attributes->has('name'))
            <input type="hidden" name="{{ $attributes->get('name') }}" :value="value">
        @endif

        {{-- Dropdown Trigger (div role=combobox to avoid nested button parser issues) --}}
        <div
            role="combobox"
            :tabindex="disabled ? -1 : 0"
            @click="toggle()"
            @keydown.enter.prevent="onEnter()"
            @keydown.space.prevent="openDropdown()"
            @keydown.arrow-down.prevent="onArrowDown()"
            @keydown.arrow-up.prevent="onArrowUp()"
            :aria-expanded="open"
            aria-haspopup="listbox"
            :class="{
                'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-slate-800': disabled,
                'border-red-500 ring-2 ring-red-500/20': {{ $error ? 'true' : 'false' }},
                'border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500': !{{ $error ? 'true' : 'false' }},
                'ring-2 ring-indigo-500 border-indigo-500': open
            }"
            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 text-left border rounded-xl text-sm transition-all flex items-center justify-between gap-2 shadow-sm cursor-pointer select-none outline-none"
        >
            <div class="flex items-center gap-2 truncate min-w-0 flex-1">
                <div x-show="value && selectedLabel" class="flex flex-col text-left truncate">
                    <span class="font-medium text-slate-900 dark:text-slate-100 truncate" x-text="selectedLabel"></span>
                    <span x-show="selectedSublabel" class="text-xs text-slate-400 dark:text-slate-500 truncate" x-text="selectedSublabel"></span>
                </div>
                <span x-show="!value || !selectedLabel" class="text-slate-400 dark:text-slate-500 truncate" x-text="placeholder"></span>
            </div>

            <div class="flex items-center gap-1 shrink-0 text-slate-400">
                {{-- Clear Button --}}
                <span
                    x-show="clearable && value && !disabled"
                    @click.stop="clear()"
                    role="button"
                    tabindex="0"
                    class="p-0.5 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors inline-flex items-center justify-center cursor-pointer"
                    title="Hapus pilihan"
                >
                    <span class="material-symbols-outlined notranslate text-base block" translate="no">close</span>
                </span>

                {{-- Chevron --}}
                <span
                    class="material-symbols-outlined notranslate text-xl transition-transform duration-200"
                    :class="{ 'rotate-180 text-indigo-500': open }"
                    translate="no"
                >expand_more</span>
            </div>
        </div>

        {{-- Dropdown Floating Panel --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1 scale-98"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-98"
            class="absolute left-0 right-0 z-50 mt-1.5 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
            style="display: none;"
        >
            {{-- Search Box --}}
            <div x-show="searchable" class="p-2 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-slate-400 pointer-events-none flex items-center">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                    </span>
                    <input
                        x-ref="searchInput"
                        type="text"
                        x-model="search"
                        @keydown.arrow-down.prevent="onArrowDown()"
                        @keydown.arrow-up.prevent="onArrowUp()"
                        @keydown.enter.prevent="onEnter()"
                        @keydown.escape.prevent="close()"
                        :placeholder="searchPlaceholder"
                        class="w-full pl-9 pr-9 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                    >
                    <div x-show="loading && search !== ''" class="absolute flex items-center pointer-events-none" style="right: 12px;">
                        <div class="animate-spin rounded-full h-4 w-4 border-2 border-indigo-500 border-t-transparent"></div>
                    </div>
                </div>
            </div>

            {{-- Options List --}}
            <div
                x-ref="optionsList"
                role="listbox"
                class="max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60"
            >
                {{-- Options template --}}
                <template x-for="(opt, index) in options" :key="opt.value">
                    <div
                        @click="select(opt)"
                        @mouseenter="highlightedIndex = index"
                        role="option"
                        :aria-selected="String(value) === String(opt.value)"
                        :class="{
                            'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300': highlightedIndex === index,
                            'text-slate-700 dark:text-slate-200': highlightedIndex !== index,
                            'font-semibold': String(value) === String(opt.value)
                        }"
                        class="px-4 py-2.5 flex items-center justify-between text-sm cursor-pointer transition-colors"
                    >
                        <div class="flex flex-col truncate min-w-0 pr-2">
                            <span class="truncate" x-text="opt.label"></span>
                            <span x-show="opt.sublabel" class="text-xs text-slate-400 dark:text-slate-500 truncate mt-0.5" x-text="opt.sublabel"></span>
                        </div>

                        {{-- Checkmark if selected --}}
                        <span
                            x-show="String(value) === String(opt.value)"
                            class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400 text-lg shrink-0"
                            translate="no"
                        >check</span>
                    </div>
                </template>

                {{-- Empty State --}}
                <div x-show="!loading && options.length === 0 && !errorMessage" class="px-4 py-6 text-center text-slate-400 dark:text-slate-500 text-xs">
                    <span class="material-symbols-outlined notranslate text-2xl mb-1 opacity-60" translate="no">search_off</span>
                    <p>Tidak ada hasil ditemukan.</p>
                </div>

                {{-- Error State --}}
                <div x-show="errorMessage" class="px-4 py-4 text-center text-red-500 text-xs">
                    <p x-text="errorMessage"></p>
                </div>

                {{-- Loading Placeholder when initial load with 0 items --}}
                <div x-show="loading && options.length === 0" class="px-4 py-6 text-center text-slate-400 text-xs">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-indigo-500 border-t-transparent mx-auto mb-2"></div>
                    <p>Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Error / Hint text --}}
    @if ($error)
        <p class="text-xs text-red-500 mt-1">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $hint }}</p>
    @endif
</div>

{{-- Shared Component Script --}}
@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dsSearchableSelect', (config) => ({
        open: false,
        search: '',
        value: config.value,
        selectedLabel: config.selectedLabel || '',
        selectedSublabel: config.selectedSublabel || '',
        options: [],
        allLocalOptions: [],
        highlightedIndex: -1,
        loading: false,
        errorMessage: null,
        searchTimeout: null,
        requestId: 0,
        strategy: config.strategy || 'local',
        searchAction: config.searchAction || '',
        searchable: config.searchable ?? true,
        clearable: config.clearable ?? true,
        disabled: config.disabled ?? false,
        debounce: config.debounce || 300,
        maxResults: config.maxResults || 20,
        placeholder: config.placeholder || '-- Pilih --',
        searchPlaceholder: config.searchPlaceholder || 'Cari...',

        init() {
            this.allLocalOptions = Array.isArray(config.options) ? JSON.parse(JSON.stringify(config.options)) : [];
            this.options = this.allLocalOptions;

            // Sync initial label
            this.syncLabelFromValue();

            // Watch for external value changes (Livewire entangle)
            this.$watch('value', (newVal) => {
                this.syncLabelFromValue();
            });

            // Watch search query
            this.$watch('search', (query) => {
                if (!this.open) return;
                if (this.strategy === 'local') {
                    this.filterLocal(query);
                } else if (this.strategy === 'remote') {
                    this.filterRemoteDebounced(query);
                }
            });
        },

        syncLabelFromValue() {
            if (this.value === null || this.value === '' || this.value === undefined) {
                this.selectedLabel = '';
                this.selectedSublabel = '';
                return;
            }

            // Find in current options or local options
            const match = this.options.find(o => String(o.value) === String(this.value))
                       || this.allLocalOptions.find(o => String(o.value) === String(this.value));

            if (match) {
                this.selectedLabel = match.label;
                this.selectedSublabel = match.sublabel || '';
            }
        },

        toggle() {
            if (this.disabled) return;
            if (this.open) {
                this.close();
            } else {
                this.openDropdown();
            }
        },

        openDropdown() {
            if (this.disabled) return;
            this.open = true;
            this.search = '';
            this.highlightedIndex = -1;
            this.errorMessage = null;

            if (this.strategy === 'local') {
                this.options = this.allLocalOptions.slice(0, this.maxResults);
            } else if (this.strategy === 'remote') {
                if (this.options.length === 0) {
                    this.fetchRemote('');
                }
            }

            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        close() {
            this.open = false;
            this.search = '';
            this.highlightedIndex = -1;
        },

        filterLocal(query) {
            if (!query || query.trim() === '') {
                this.options = this.allLocalOptions.slice(0, this.maxResults);
                this.highlightedIndex = -1;
                return;
            }

            const q = query.toLowerCase().trim();
            this.options = this.allLocalOptions
                .filter(o =>
                    (o.label && String(o.label).toLowerCase().includes(q)) ||
                    (o.sublabel && String(o.sublabel).toLowerCase().includes(q)) ||
                    (o.value && String(o.value).toLowerCase().includes(q))
                )
                .slice(0, this.maxResults);

            this.highlightedIndex = this.options.length > 0 ? 0 : -1;
        },

        filterRemoteDebounced(query) {
            clearTimeout(this.searchTimeout);
            this.loading = true;
            this.searchTimeout = setTimeout(() => {
                this.fetchRemote(query);
            }, this.debounce);
        },

        async fetchRemote(query) {
            const thisRequestId = ++this.requestId;
            this.loading = true;
            this.errorMessage = null;

            try {
                if (!this.searchAction) {
                    console.error('[DsSearchableSelect] Remote strategy requires searchAction');
                    this.loading = false;
                    return;
                }

                // Call Livewire server method
                const result = await this.$wire.call(this.searchAction, query);

                // Discard stale responses
                if (thisRequestId !== this.requestId) return;

                if (Array.isArray(result)) {
                    this.options = result.slice(0, this.maxResults);
                } else {
                    this.options = [];
                }
                this.highlightedIndex = this.options.length > 0 ? 0 : -1;
            } catch (err) {
                if (thisRequestId === this.requestId) {
                    this.errorMessage = 'Gagal memuat data dari server.';
                    console.error('[DsSearchableSelect] Remote search error:', err);
                }
            } finally {
                if (thisRequestId === this.requestId) {
                    this.loading = false;
                }
            }
        },

        select(opt) {
            this.value = opt.value;
            this.selectedLabel = opt.label;
            this.selectedSublabel = opt.sublabel || '';
            this.$dispatch('change', opt.value);
            this.close();
        },

        clear() {
            this.value = '';
            this.selectedLabel = '';
            this.selectedSublabel = '';
            this.$dispatch('change', '');
            this.close();
        },

        onArrowDown() {
            if (!this.open) {
                this.openDropdown();
                return;
            }
            if (this.options.length === 0) return;
            this.highlightedIndex = (this.highlightedIndex + 1) % this.options.length;
            this.scrollHighlightedIntoView();
        },

        onArrowUp() {
            if (!this.open) {
                this.openDropdown();
                return;
            }
            if (this.options.length === 0) return;
            this.highlightedIndex = (this.highlightedIndex - 1 + this.options.length) % this.options.length;
            this.scrollHighlightedIntoView();
        },

        onEnter() {
            if (!this.open) {
                this.openDropdown();
                return;
            }
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.options.length) {
                this.select(this.options[this.highlightedIndex]);
            }
        },

        scrollHighlightedIntoView() {
            this.$nextTick(() => {
                const el = this.$refs.optionsList?.children[this.highlightedIndex];
                if (el) {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
        }
    }));
});
</script>
@endonce
