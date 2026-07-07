<?php

namespace App\Volt\Components\Admin;

use Livewire\Volt\Component;

new class extends Component {
    public bool $open = false;
    public string $query = '';
    public array $results = [];
    public int $selectedIndex = 0;
    public array $recentSearches = [];
    public array $quickActions = [];
    public array $navigationItems = [];

    public function mount()
    {
        $this->quickActions = $this->getQuickActions();
        $this->navigationItems = $this->getNavigationItems();
        $this->recentSearches = $this->getRecentSearches();

        $this->listeners = [
            'open-command-palette' => 'open',
            'close-command-palette' => 'close',
        ];

        $this->listeners = array_merge($this->listeners, [
            'keydown.window.cmd.k' => 'toggle',
            'keydown.window.ctrl.k' => 'toggle',
        ]);
    }

    public function toggle()
    {
        $this->open = !$this->open;
        if ($this->open) {
            $this->query = '';
            $this->results = [];
            $this->selectedIndex = 0;
        }
    }

    public function open()
    {
        $this->open = true;
        $this->query = '';
        $this->results = [];
        $this->selectedIndex = 0;
    }

    public function close()
    {
        $this->open = false;
    }

    public function updatedQuery()
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $this->results = $this->search($this->query);
        $this->selectedIndex = 0;
    }

    public function search(string $query): array
    {
        $query = strtolower($query);
        $results = [];

        // Search navigation items
        foreach ($this->navigationItems as $item) {
            if (str_contains(strtolower($item['label']), $query)) {
                $results[] = [
                    'type' => 'navigation',
                    'label' => $item['label'],
                    'url' => $item['url'],
                    'icon' => $item['icon'] ?? 'link',
                ];
            }
        }

        // Search quick actions
        foreach ($this->quickActions as $action) {
            if (str_contains(strtolower($action['label']), $query)) {
                $results[] = [
                    'type' => 'action',
                    'label' => $action['label'],
                    'action' => $action['action'] ?? null,
                    'icon' => $action['icon'] ?? 'lightning-bolt',
                ];
            }
        }

        return array_slice($results, 0, 10);
    }

    public function selectResult($result)
    {
        if ($result['type'] === 'navigation' && isset($result['url'])) {
            $this->addToRecent($result['label'], $result['url']);
            $this->close();
            redirect($result['url']);
        } elseif ($result['type'] === 'action' && isset($result['action'])) {
            $this->close();
            $this->dispatch($result['action']);
        }
    }

    public function addToRecent(string $label, string $url)
    {
        $recent = ['label' => $label, 'url' => $url];
        $this->recentSearches = array_filter($this->recentSearches, fn($r) => $r['url'] !== $url);
        array_unshift($this->recentSearches, $recent);
        $this->recentSearches = array_slice($this->recentSearches, 0, 5);
    }

    public function goToRecent($recent)
    {
        $this->close();
        redirect($recent['url']);
    }

    public function getQuickActions(): array
    {
        return [
            ['label' => 'Create Customer', 'action' => 'create-customer', 'icon' => 'user-plus'],
            ['label' => 'Create Ticket', 'action' => 'create-ticket', 'icon' => 'ticket'],
            ['label' => 'View Reports', 'action' => 'view-reports', 'icon' => 'chart-bar'],
            ['label' => 'System Settings', 'action' => 'open-settings', 'icon' => 'cog'],
            ['label' => 'Toggle Dark Mode', 'action' => 'toggle-dark-mode', 'icon' => 'moon'],
        ];
    }

    public function getNavigationItems(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'home'],
            ['label' => 'CRM Customers', 'url' => route('crm.customers.index'), 'icon' => 'users'],
            ['label' => 'CRM Leads', 'url' => route('crm.leads.index'), 'icon' => 'user-plus'],
            ['label' => 'Billing Invoices', 'url' => route('billing.invoices.index'), 'icon' => 'file-invoice'],
            ['label' => 'Network POPs', 'url' => route('isp.pops.index'), 'icon' => 'server'],
            ['label' => 'GIS Platform', 'url' => route('gis.index'), 'icon' => 'map'],
            ['label' => 'Users', 'url' => route('users.index'), 'icon' => 'users'],
        ];
    }

    public function getRecentSearches(): array
    {
        return [
            ['label' => 'CRM Customers', 'url' => route('crm.customers.index')],
            ['label' => 'GIS Platform', 'url' => route('gis.index')],
        ];
    }

    public function moveSelectionUp()
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
        }
    }

    public function moveSelectionDown()
    {
        if ($this->selectedIndex < count($this->results) - 1) {
            $this->selectedIndex++;
        }
    }

    public function executeSelection()
    {
        if (isset($this->results[$this->selectedIndex])) {
            $this->selectResult($this->results[$this->selectedIndex]);
        }
    }
};

?>

<div
    x-data="{
        open: @entangle('open'),
        query: @entangle('query'),
        results: @entangle('results'),
        selectedIndex: @entangle('selectedIndex'),
    }"
    x-init="
        $watch('open', value => {
            if (value) {
                document.body.classList.add('overflow-hidden');
                $nextTick(() => $refs.searchInput?.focus());
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        });
    "
    @open-command-palette.window="open = true"
    @close-command-palette.window="open = false"
    @keydown.window.cmd.k.prevent="open = !open"
    @keydown.window.ctrl.k.prevent="open = !open"
    @keydown.escape.window="open = false"
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] flex items-start justify-center pt-[15vh]"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
    ></div>

    {{-- Command Palette --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
        class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl shadow-soft-xl border border-slate-200 dark:border-slate-800 overflow-hidden"
        @click.stop
    >
        {{-- Search Input --}}
        <div class="flex items-center gap-4 px-6 py-4 border-b border-slate-200 dark:border-slate-800">
            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>

            <input
                type="text"
                x-model="query"
                x-ref="searchInput"
                @keydown.arrow-up.prevent="window.livewire.find('command-palette').moveSelectionUp()"
                @keydown.arrow-down.prevent="window.livewire.find('command-palette').moveSelectionDown()"
                @keydown.enter.prevent="window.livewire.find('command-palette').executeSelection()"
                placeholder="Search or type a command..."
                class="flex-1 bg-transparent text-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none"
            />

            <kbd class="hidden sm:flex items-center px-2 py-1 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 rounded border border-slate-200 dark:border-slate-700">
                ESC
            </kbd>
        </div>

        {{-- Results --}}
        <div class="max-h-96 overflow-y-auto">
            @if(strlen($query) < 2)
                {{-- Recent Searches --}}
                @if(!empty($recentSearches))
                    <div class="px-4 py-2">
                        <p class="px-2 py-1 text-xs font-semibold text-slate-400 uppercase">Recent</p>
                        @foreach($recentSearches as $recent)
                            <button
                                type="button"
                                wire:click="goToRecent({{ json_encode($recent) }})"
                                class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors rounded-lg"
                            >
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-slate-700 dark:text-slate-300">{{ $recent['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Quick Actions --}}
                @if(!empty($quickActions))
                    <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-800">
                        <p class="px-2 py-1 text-xs font-semibold text-slate-400 uppercase">Quick Actions</p>
                        @foreach($quickActions as $action)
                            <button
                                type="button"
                                wire:click="selectResult({{ json_encode(['type' => 'action', 'action' => $action['action'] ?? null, 'icon' => $action['icon'] ?? 'lightning-bolt', 'label' => $action['label']]) }})"
                                class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors rounded-lg"
                            >
                                <x-icon :name="$action['icon']" class="w-5 h-5 text-slate-400" />
                                <span class="text-slate-700 dark:text-slate-300">{{ $action['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            @else
                {{-- Search Results --}}
                @if(empty($results))
                    <div class="px-6 py-12 text-center">
                        <svg class="w-12 h-12 mx-auto mb-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-slate-500 dark:text-slate-400">No results found for "{{ $query }}"</p>
                    </div>
                @else
                    <div class="py-2">
                        @foreach($results as $index => $result)
                            <button
                                type="button"
                                wire:click="selectResult({{ json_encode($result) }})"
                                :class="{{ $index }} === selectedIndex ? 'bg-primary-50 dark:bg-primary-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800'"
                                class="w-full flex items-center gap-3 px-6 py-3 text-left transition-colors"
                            >
                                <x-icon :name="$result['icon'] ?? 'link'" class="w-5 h-5 text-slate-400" />
                                <span class="text-slate-700 dark:text-slate-300">{{ $result['label'] }}</span>
                                <span class="ml-auto text-xs text-slate-400 capitalize">{{ $result['type'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700">↑</kbd>
                    <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700">↓</kbd>
                    to navigate
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700">↵</kbd>
                    to select
                </span>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span>WiFinan</span>
                <span>•</span>
                <span>v1.0</span>
            </div>
        </div>
    </div>
</div>
