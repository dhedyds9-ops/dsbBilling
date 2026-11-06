@section('page_title')
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">alt_route</span>
        <span class="text-lg">Network Profile & VLAN</span>
    </div>
@endsection

<div class="space-y-6 pb-12">
    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300 rounded-2xl text-sm flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-emerald-600" translate="no" style="font-size:20px">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 rounded-2xl text-sm flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-red-600" translate="no" style="font-size:20px">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Network Profiles</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manajemen profil jaringan (VLAN, Mode Layanan, Target Router) untuk dsBilling Auto-Provisioning.</p>
        </div>
        <div>
            <a href="{{ route('isp.network-profiles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all text-sm font-semibold shadow-sm shadow-indigo-600/20 active:scale-95">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                Tambah Profile
            </a>
        </div>
    </div>

    {{-- Router Hint Banner --}}
    <div class="p-4 bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-indigo-800 dark:text-indigo-300">
        <div class="flex items-center gap-2.5">
            <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400 text-[20px]" translate="no">info</span>
            <span>
                <strong>Info Jaringan:</strong> Network Profile (VLAN) kini dapat dikelola langsung per Router di menu <strong>Router / NAS &gt; Detail Router &gt; Network Profile (VLAN)</strong>.
            </span>
        </div>
        <a href="{{ route('isp.routers.index') }}" class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors">
            <span>Buka Router / NAS</span>
            <span class="material-symbols-outlined notranslate text-[14px]" translate="no">arrow_forward</span>
        </a>
    </div>

    {{-- Toolbar / Search --}}
    <div class="bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="relative w-full max-w-md">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" style="font-size:20px">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama profil atau deskripsi..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/75 dark:bg-slate-800/80 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-3.5 text-center w-28">Aksi</th>
                        <th class="px-4 py-3.5">Nama Profile</th>
                        <th class="px-4 py-3.5">Tipe Layanan</th>
                        <th class="px-4 py-3.5">VLAN Tag</th>
                        <th class="px-4 py-3.5">Target Router</th>
                        <th class="px-4 py-3.5">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($profiles as $profile)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            {{-- Action Buttons --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('isp.network-profiles.edit', $profile->id) }}" 
                                       title="Edit Profil"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 dark:text-emerald-400 transition-colors">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $profile->id }})" 
                                            wire:confirm="Yakin ingin menghapus profile ini?" 
                                            title="Hapus"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>

                            {{-- Nama Profile --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $profile->name }}</div>
                                @if($profile->uuid)
                                    <div class="text-[10px] font-mono text-slate-400">{{ substr($profile->uuid, 0, 8) }}...</div>
                                @endif
                            </td>

                            {{-- Tipe Layanan --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $t = strtolower($profile->type ?? '');
                                    $badge = match($t) {
                                        'pppoe'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                                        'hotspot' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
                                        default   => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full uppercase tracking-wider {{ $badge }}">
                                    {{ $profile->type }}
                                </span>
                            </td>

                            {{-- VLAN Tag --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($profile->vlan_id)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        <span class="material-symbols-outlined" style="font-size:14px">tag</span>
                                        VLAN {{ $profile->vlan_id }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-italic">Default / Untagged</span>
                                @endif
                            </td>

                            {{-- Target Router --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($profile->router)
                                    <a href="{{ route('isp.routers.show', ['id' => $profile->router_id, 'tab' => 'vlan']) }}" class="group flex flex-col hover:opacity-85 transition-opacity" title="Buka Network Profile VLAN di Router ini">
                                        <div class="font-semibold text-indigo-600 dark:text-indigo-400 group-hover:underline flex items-center gap-1">
                                            <span>{{ $profile->router->name }}</span>
                                            <span class="material-symbols-outlined notranslate text-[14px]" translate="no">open_in_new</span>
                                        </div>
                                        <div class="text-xs font-mono text-slate-400">{{ $profile->router->ip_address }}</div>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Deskripsi --}}
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                {{ $profile->description ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="w-16 h-16 mx-auto bg-slate-100 dark:bg-slate-700/50 text-slate-400 rounded-full flex items-center justify-center mb-3">
                                    <span class="material-symbols-outlined" style="font-size:32px">alt_route</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Belum ada Network Profile</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                                    Network Profile digunakan untuk memetakan VLAN ID (misal VLAN 200, 300, 901) ke OLT dan MikroTik Router.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('isp.network-profiles.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                                        <span class="material-symbols-outlined" style="font-size:16px">add</span> Tambah Profile Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($profiles->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $profiles->links() }}
            </div>
        @endif
    </div>
</div>
