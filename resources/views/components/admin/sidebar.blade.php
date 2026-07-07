@php
    $navigation = \App\Navigation\MenuRegistry::getNavigation();

    $searchIndex = [];
    $initialOpenGroupId = null;

    $resolveUrl = function (?string $routeName): ?string {
        if (! $routeName) {
            return null;
        }

        try {
            return route($routeName);
        } catch (\Throwable $e) {
            return null;
        }
    };

    foreach ($navigation as $category) {
        foreach (($category['groups'] ?? []) as $group) {
            foreach (($group['items'] ?? []) as $item) {
                $url = $resolveUrl($item['route'] ?? null);
                if (! $url) {
                    continue;
                }

                $searchIndex[] = [
                    'route' => $item['route'] ?? null,
                    'url' => $url,
                    'label' => $item['label'] ?? '',
                    'icon' => $item['icon'] ?? ($category['icon'] ?? 'home'),
                    'category' => $category['label'] ?? '',
                    'group' => $group['label'] ?? '',
                ];
            }
        }
    }

    foreach ($navigation as $category) {
        foreach (($category['groups'] ?? []) as $group) {
            $groupHasActive = false;

            foreach (($group['items'] ?? []) as $item) {
                $pattern = $item['active'] ?? ($item['route'] ?? null);
                if ($pattern && request()->routeIs($pattern)) {
                    $groupHasActive = true;
                    break;
                }
            }

            if ($groupHasActive) {
                $initialOpenGroupId = substr(md5(($category['label'] ?? '') . '|' . ($group['label'] ?? '')), 0, 12);
                break 2;
            }
        }
    }
@endphp

<aside
    x-bind:class="[
        sidebarMobileOpen ? 'fixed inset-y-0 left-0 z-50' : 'hidden lg:flex lg:flex-col fixed inset-y-0 left-0 z-40',
        sidebarCollapsed ? 'w-20' : 'w-72',
        'bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-all duration-300'
    ]"
>
    <!-- Logo & Brand -->
    <div class="flex items-center h-16 px-4 border-b border-slate-200 dark:border-slate-700">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span x-show="!sidebarCollapsed" class="text-xl font-bold text-slate-900 dark:text-slate-100">
                WiFinan
            </span>
        </a>

        <button
            @click="sidebarCollapsed = !sidebarCollapsed"
            class="ml-auto hidden lg:flex p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
        >
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <!-- Mobile Close Button -->
    <button
        @click="sidebarMobileOpen = false"
        class="lg:hidden absolute top-4 right-4 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Navigation -->
    <nav
        class="flex-1 overflow-y-auto py-4 px-3 space-y-4"
        x-data="{
            searchQuery: '',
            openGroup: @js($initialOpenGroupId),
            searchIndex: @js($searchIndex),
            toggleGroup(id) {
                this.openGroup = this.openGroup === id ? null : id;
            },
            isOpen(id) {
                return this.openGroup === id;
            }
        }"
    >
        <div x-show="!sidebarCollapsed" class="px-2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icon name="search" class="w-4 h-4 text-slate-400" />
                </div>
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Search menu..."
                    class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500"
                />
            </div>
        </div>

        {{-- <template x-if="!sidebarCollapsed && favoriteItems.length">
            <div class="px-1">
                <p class="px-2 mb-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    Favorites
                </p>

                <div class="space-y-1">
                    <template x-for="item in favoriteItems" :key="'fav-' + item.route">
                        <a
                            :href="item.url"
                            @click="recordRecent(item.route)"
                            class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"
                        >
                            <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-[10px] text-slate-500 dark:text-slate-300" x-text="(item.label || '?').charAt(0)"></div>
                            <span class="flex-1" x-text="item.label"></span>
                            <button
                                type="button"
                                @click.prevent="toggleFavorite(item.route)"
                                class="text-amber-500 hover:text-amber-600 transition-colors"
                            >
                                ★
                            </button>
                        </a>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="!sidebarCollapsed && recentItems.length">
            <div class="px-1">
                <p class="px-2 mb-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    Recently Opened
                </p>

                <div class="space-y-1">
                    <template x-for="item in recentItems" :key="'recent-' + item.route">
                        <a
                            :href="item.url"
                            @click="recordRecent(item.route)"
                            class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"
                        >
                            <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-[10px] text-slate-500 dark:text-slate-300" x-text="(item.label || '?').charAt(0)"></div>
                            <span class="flex-1" x-text="item.label"></span>
                        </a>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="!sidebarCollapsed && searchQuery.trim().length">
            <div class="px-1">
                <p class="px-2 mb-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    Results
                </p>

                <div class="space-y-1">
                    <template x-for="item in filteredResults" :key="'search-' + item.route">
                        <a
                            :href="item.url"
                            @click="recordRecent(item.route); searchQuery = ''"
                            class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"
                        >
                            <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-[10px] text-slate-500 dark:text-slate-300" x-text="(item.label || '?').charAt(0)"></div>
                            <div class="flex-1">
                                <div class="leading-tight" x-text="item.label"></div>
                                <div class="text-xs text-slate-400 leading-tight" x-text="item.category + ' • ' + item.group"></div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </template> --}}

        <div x-show="!searchQuery.trim().length" class="space-y-4">
            @foreach($navigation as $category)
                @php
                    $categoryLabel = $category['label'] ?? '';
                    $categoryIcon = $category['icon'] ?? 'home';
                @endphp

                <div>
                    <div class="px-2 mb-2 flex items-center gap-2">
                        <x-icon :name="$categoryIcon" class="w-4 h-4 text-slate-400" />
                        <p x-show="!sidebarCollapsed" class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                            {{ $categoryLabel }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        @foreach(($category['groups'] ?? []) as $group)
                            @php
                                $groupLabel = $group['label'] ?? '';
                                $groupId = substr(md5($categoryLabel . '|' . $groupLabel), 0, 12);

                                $groupIcon = $group['icon'] ?? (($group['items'][0]['icon'] ?? null) ?: $categoryIcon);

                                $groupHasActive = false;
                                foreach (($group['items'] ?? []) as $item) {
                                    $pattern = $item['active'] ?? ($item['route'] ?? null);
                                    if ($pattern && request()->routeIs($pattern)) {
                                        $groupHasActive = true;
                                        break;
                                    }
                                }
                            @endphp

                            <div class="rounded-lg">
                                <button
                                    type="button"
                                    @click="toggleGroup('{{ $groupId }}')"
                                    class="w-full flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ $groupHasActive ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}"
                                >
                                    <x-icon :name="$groupIcon" class="w-5 h-5" />
                                    <span x-show="!sidebarCollapsed" class="flex-1 text-left">{{ $groupLabel }}</span>
                                    <svg
                                        x-show="!sidebarCollapsed"
                                        x-bind:class="isOpen('{{ $groupId }}') ? 'rotate-90' : ''"
                                        class="w-4 h-4 text-slate-400 transition-transform"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>

                                <div x-show="isOpen('{{ $groupId }}')" x-transition class="mt-1 space-y-1 pl-2">
                                    @foreach(($group['items'] ?? []) as $item)
                                        @php
                                            $itemLabel = $item['label'] ?? '';
                                            $itemIcon = $item['icon'] ?? $groupIcon;
                                            $itemRoute = $item['route'] ?? null;
                                            $itemActivePattern = $item['active'] ?? $itemRoute;
                                            $itemIsActive = $itemActivePattern ? request()->routeIs($itemActivePattern) : false;

                                            $itemUrl = $resolveUrl($itemRoute);
                                            $itemIsPlaceholder = ! $itemUrl;

                                            $badgeValue = $item['badge'] ?? null;
                                            $badgeValue = is_numeric($badgeValue) ? (int) $badgeValue : null;
                                        @endphp

                                        @if($itemIsPlaceholder)
                                            <div class="flex items-center gap-3 px-3 py-2 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed opacity-60">
                                                <x-icon :name="$itemIcon" class="w-5 h-5" />
                                                <span x-show="!sidebarCollapsed" class="flex-1">{{ $itemLabel }}</span>
                                                <span x-show="!sidebarCollapsed" class="ml-auto text-xs bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">Soon</span>
                                            </div>
                                        @else
                                            <a
                                                href="{{ $itemUrl }}"
                                                {{-- @click="recordRecent('{{ $itemRoute }}')" --}}
                                                class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-colors {{ $itemIsActive ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}"
                                            >
                                                <x-icon :name="$itemIcon" class="w-5 h-5" />
                                                <span x-show="!sidebarCollapsed" class="flex-1">{{ $itemLabel }}</span>

                                                @if($badgeValue && $badgeValue > 0)
                                                    <span x-show="!sidebarCollapsed" class="ml-auto">
                                                        <x-base.badge size="sm" variant="primary">{{ $badgeValue }}</x-base.badge>
                                                    </span>
                                                @endif

                                                {{-- <button
                                                    x-show="!sidebarCollapsed"
                                                    type="button"
                                                    @click.prevent="toggleFavorite('{{ $itemRoute }}')"
                                                    class="ml-2 text-slate-300 hover:text-amber-500 transition-colors"
                                                    x-bind:class="isFavorite('{{ $itemRoute }}') ? 'text-amber-500' : ''"
                                                >
                                                    ★
                                                </button> --}}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </nav>
</aside>
