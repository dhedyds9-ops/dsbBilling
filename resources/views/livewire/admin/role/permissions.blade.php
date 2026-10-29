@section('page_title')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.roles.index') }}" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">arrow_back</span>
        </a>
        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">rule</span>
        </div>
        <span class="text-lg">Matriks Hak Akses: <strong class="text-indigo-600 dark:text-indigo-400">{{ $role->display_name }}</strong></span>
    </div>
@endsection

<div class="space-y-6 pb-20">
    {{-- Flash Notifications --}}
    @if(session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-emerald-600" translate="no" style="font-size:20px">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">close</span>
            </button>
        </div>
    @endif

    {{-- Role Overview & Action Toolbar --}}
    @if($role->name === 'reseller')
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-sm flex items-start gap-3 shadow-sm">
            <span class="material-symbols-outlined notranslate text-emerald-600 dark:text-emerald-400 mt-0.5" translate="no" style="font-size:22px">storefront</span>
            <div>
                <strong class="font-bold block">Matriks Hak Akses Khusus Portal Reseller</strong>
                <span>Menampilkan <strong>27 Hak Akses Terpilih</strong> yang berlaku langsung pada menu dan aksi <strong>Portal Reseller</strong> (Pelanggan, Layanan PPPoE & Hotspot, Cetak Voucher, Data Tagihan, Perangkat OLT/ODC/ODP, Peta Satelit GIS, dan Saldo Deposit). Izin internal ISP kantor pusat disaring demi keamanan sistem.</span>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $role->is_system ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' }} shadow-inner">
                    <span class="material-symbols-outlined notranslate text-[28px]" translate="no">shield_person</span>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $role->display_name }}</h2>
                        <code class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300">{{ $role->name }}</code>
                        @if($role->is_system)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">Sistem (Default)</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Kustom</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ $role->description ?: 'Atur izin operasional dan batasan akses modul untuk pemegang role ini.' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-end lg:self-center">
                <a href="{{ route('admin.roles.index') }}" 
                    class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Kembali
                </a>
                <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                    <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                    <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                    <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>

        {{-- Administrator Notice --}}
        @if($role->name === 'administrator')
            <div class="mt-4 p-3.5 rounded-xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800 text-xs text-purple-800 dark:text-purple-300 flex items-start gap-2.5">
                <span class="material-symbols-outlined notranslate text-purple-600 mt-0.5" translate="no" style="font-size:18px">info</span>
                <div>
                    <strong>Peran Administrator memiliki hak akses penuh otomatis (Super-Admin Bypass).</strong>
                    Semua modul dan fungsi sistem otomatis diizinkan untuk akun Administrator. Centang di bawah ini merefleksikan daftar izin standar sistem.
                </div>
            </div>
        @endif
    </div>

    {{-- Fast Control Bar: Search & Global Toggles --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3.5 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
            </div>
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Cari izin / modul spesifik..." 
                class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400">
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-2.5">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                Terpilih: <strong class="text-indigo-600 dark:text-indigo-400">{{ $totalSelected }}</strong> dari {{ $totalSystemPermissions }} izin
            </span>
            <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
            <button type="button" wire:click="selectAll" 
                class="px-3 py-1.5 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-lg transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">check_box</span>
                <span>Pilih Semua</span>
            </button>
            <button type="button" wire:click="deselectAll" 
                class="px-3 py-1.5 text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">check_box_outline_blank</span>
                <span>Kosongkan</span>
            </button>
        </div>
    </div>

    {{-- Permission Categories & Matrix --}}
    <div class="space-y-6">
        @forelse($structuredCategories as $catKey => $category)
            @php
                $catIds = array_map('strval', $category['permission_ids']);
                $selectedInCat = count(array_intersect($catIds, $selectedPermissions));
                $totalInCat = count($catIds);
                $isAllSelectedInCat = ($totalInCat > 0 && $selectedInCat === $totalInCat);
            @endphp
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                {{-- Category Header --}}
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined notranslate text-[20px]" translate="no">{{ $category['icon'] }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $category['title'] }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $isAllSelectedInCat ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : ($selectedInCat > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300') }}">
                                    {{ $selectedInCat }} / {{ $totalInCat }} aktif
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $category['description'] }}</p>
                        </div>
                    </div>

                    {{-- Quick Category Toggle --}}
                    <div>
                        <button type="button" wire:click="toggleCategory(@js($category['permission_ids']))" 
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors flex items-center gap-1.5 {{ $isAllSelectedInCat ? 'bg-slate-100 dark:bg-slate-700/60 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200' : 'bg-white dark:bg-slate-800 border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/30' }}">
                            <span class="material-symbols-outlined notranslate text-[16px]" translate="no">
                                {{ $isAllSelectedInCat ? 'deselect' : 'select_all' }}
                            </span>
                            <span>{{ $isAllSelectedInCat ? 'Hapus Centang Modul Ini' : 'Pilih Semua Modul Ini' }}</span>
                        </button>
                    </div>
                </div>

                {{-- Modules Inside Category --}}
                <div class="p-5 divide-y divide-slate-100 dark:divide-slate-700/60 space-y-5">
                    @foreach($category['modules'] as $moduleKey => $module)
                        <div class="{{ !$loop->first ? 'pt-5' : '' }}">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    <span>{{ $module['title'] }}</span>
                                </h4>
                            </div>

                            {{-- Permission Checkboxes Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5">
                                @foreach($module['permissions'] as $perm)
                                    @php
                                        $isChecked = in_array((string)$perm->id, $selectedPermissions);
                                    @endphp
                                    <label class="relative flex items-start gap-2.5 p-2.5 rounded-xl border transition-all cursor-pointer {{ $isChecked ? 'bg-indigo-50/60 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-800' : 'bg-slate-50/50 dark:bg-slate-900/20 border-slate-200 dark:border-slate-700/60 hover:bg-slate-100/60 dark:hover:bg-slate-700/30' }}">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ (string)$perm->id }}" 
                                            class="w-4 h-4 mt-0.5 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-900">
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-tight">
                                                {{ $perm->display_name ?: $perm->name }}
                                            </span>
                                            <code class="text-[10px] text-slate-400 dark:text-slate-500 truncate mt-0.5">
                                                {{ $perm->name }}
                                            </code>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-xl p-12 text-center text-slate-400 border border-slate-200 dark:border-slate-700">
                <span class="material-symbols-outlined notranslate text-[40px] block mb-2" translate="no">search_off</span>
                Tidak ada izin yang cocok dengan kata kunci pencarian.
            </div>
        @endforelse
    </div>

    {{-- Bottom Sticky Floating Save Bar --}}
    <div class="fixed bottom-4 right-4 z-40 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md px-5 py-3 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 flex items-center gap-3">
        <span class="text-xs text-slate-600 dark:text-slate-300 hidden sm:inline">
            <strong class="text-indigo-600 dark:text-indigo-400">{{ count($selectedPermissions) }}</strong> izin terpilih
        </span>
        <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-sm font-semibold shadow-md transition-all flex items-center gap-2">
            <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
            <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
            <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </div>
</div>
