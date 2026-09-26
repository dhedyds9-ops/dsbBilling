@php
    $navigation = \App\Navigation\MenuRegistry::getNavigation();
    $companyName = \App\Models\Setting::getValue('company.name', config('app.name', 'dsBilling'));
    $companyLogo = \App\Models\Setting::getValue('company.logo_url', null);

    $searchIndex = [];
    $initialOpenGroupId = null;

    $resolveUrl = function (?string $routeName): ?string {
        if (! $routeName) return null;
        try { return route($routeName); } catch (\Throwable $e) { return null; }
    };

    foreach ($navigation as $category) {
        foreach (($category['groups'] ?? []) as $group) {
            foreach (($group['items'] ?? []) as $item) {
                $url = $resolveUrl($item['route'] ?? null);
                if (! $url) continue;
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
        sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-20' : 'w-64',
        'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-[#111c36] border-r border-slate-200 dark:border-slate-700/50 transition-all duration-300 shadow-sm'
    ]"
>
    <!-- Logo & Brand -->
    <div class="flex items-center h-16 px-4 border-b border-slate-200 dark:border-slate-700/50 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-full">
            <x-application-logo class="h-10 w-auto" />
        </a>
    </div>

    <!-- Navigation -->
    <nav
        class="flex-1 overflow-y-auto py-4 px-3 space-y-6"
        x-data="{
            openGroup: @js($initialOpenGroupId),
            toggleGroup(id) {
                this.openGroup = this.openGroup === id ? null : id;
            },
            isOpen(id) {
                return this.openGroup === id;
            }
        }"
    >
        <div class="space-y-6">
            @foreach($navigation as $category)
                @php
                    $categoryLabel = $category['label'] ?? '';
                    $categoryIcon = $category['icon'] ?? 'home';
                @endphp

                <div>
                    @if($categoryLabel !== 'Dashboard' && $categoryLabel !== 'Dashboard Reseller')
                    <div class="px-3 mb-2 flex items-center gap-2">
                        <p x-show="!sidebarCollapsed" class="text-[11px] font-medium text-blue-500/80 dark:text-blue-400 uppercase tracking-wider">
                            {{ $categoryLabel }}
                        </p>
                    </div>
                    @endif

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
                                
                                // Jika tidak ada group label (seperti dashboard), langsung tampilkan itemnya
                                $isSingleItem = empty($groupLabel);
                            @endphp

                            @if($isSingleItem)
                                @foreach(($group['items'] ?? []) as $item)
                                    @php
                                        $itemLabel = $item['label'] ?? '';
                                        $itemIcon = $item['icon'] ?? $groupIcon;
                                        $itemRoute = $item['route'] ?? null;
                                        $itemActivePattern = $item['active'] ?? $itemRoute;
                                        $itemIsActive = $itemActivePattern ? request()->routeIs($itemActivePattern) : false;
                                        $itemUrl = $resolveUrl($itemRoute) ?? '#';
                                    @endphp
                                    <a
                                        href="{{ $itemUrl }}"
                                        class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl transition-colors {{ $itemIsActive ? 'bg-blue-50/80 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100' }}"
                                    >
                                        <span class="material-symbols-outlined w-5 h-5 flex items-center justify-center {{ $itemIsActive ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }}">{{ $itemIcon }}</span>
                                        <span x-show="!sidebarCollapsed" class="flex-1">{{ $itemLabel }}</span>
                                    </a>
                                @endforeach
                            @else
                                <div>
                                    <button
                                        type="button"
                                        @click="toggleGroup('{{ $groupId }}')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl transition-colors {{ $groupHasActive ? 'bg-blue-50/80 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100' }}"
                                    >
                                        <span class="material-symbols-outlined w-5 h-5 flex items-center justify-center {{ $groupHasActive ? 'text-blue-600 dark:text-blue-400' : ($category['icon_color'] ?? 'text-slate-700 dark:text-slate-300') }}">{{ $groupIcon }}</span>
                                        <span x-show="!sidebarCollapsed" class="flex-1 text-left">{{ $groupLabel }}</span>
                                        <svg
                                            x-show="!sidebarCollapsed"
                                            x-bind:class="isOpen('{{ $groupId }}') ? 'rotate-180' : ''"
                                            class="w-4 h-4 text-slate-400 transition-transform"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Submenu Tree -->
                                    <div x-show="isOpen('{{ $groupId }}')" x-collapse class="mt-1 relative">
                                        @foreach(($group['items'] ?? []) as $item)
                                            @php
                                                $itemLabel = $item['label'] ?? '';
                                                $itemRoute = $item['route'] ?? null;
                                                $itemActivePattern = $item['active'] ?? $itemRoute;
                                                $itemIsActive = $itemActivePattern ? request()->routeIs($itemActivePattern) : false;
                                                $itemUrl = $resolveUrl($itemRoute) ?? '#';
                                                $isLast = $loop->last;
                                            @endphp

                                            <div class="relative pl-11 pr-3 py-1">
                                                <!-- Vertical Line -->
                                                <div class="absolute left-[21px] top-0 {{ $isLast ? 'h-1/2' : 'h-full' }} w-px bg-slate-200 dark:bg-slate-700"></div>
                                                
                                                <!-- Horizontal Line -->
                                                <div class="absolute left-[21px] top-1/2 -translate-y-1/2 w-3 h-px bg-slate-200 dark:bg-slate-700"></div>

                                                <!-- Active Blue Dot -->
                                                @if($itemIsActive)
                                                    <div class="absolute left-[19px] top-1/2 -translate-y-1/2 w-[5px] h-[5px] rounded-full bg-blue-600 ring-4 ring-white dark:ring-[#111c36] z-10"></div>
                                                @endif

                                                <a
                                                    href="{{ $itemUrl }}"
                                                    class="block px-3 py-2 text-sm rounded-lg transition-colors {{ $itemIsActive ? 'bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50' }}"
                                                >
                                                    {{ $itemLabel }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </nav>
</aside>
