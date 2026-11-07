<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">checklist_rtl</span>
                Stok Opname & Penyesuaian Fisik
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Rekonsiliasi berkala stok fisik gudang terhadap saldo sistem dan penyesuaian selisih otomatis.
            </p>
        </div>
        <div>
            <button wire:click="openCreateModal" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm transition">
                <span class="material-symbols-outlined text-sm">add</span>
                Mulai Sesi Opname Baru
            </button>
        </div>
    </div>

    <!-- Feedback Message -->
    @if(session()->has('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        {{ session('success') }}
    </div>
    @endif
    @if(session()->has('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm flex items-center gap-2">
        <span class="material-symbols-outlined text-rose-600">error</span>
        {{ session('error') }}
    </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/80 shadow-xs">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <div class="relative flex-1 min-w-[260px]">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor opname..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 placeholder:text-slate-400">
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[220px] flex-1 sm:flex-initial">
                    <select wire:model.live="warehouseFilter" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[220px] flex-1 sm:flex-initial">
                    <select wire:model.live="statusFilter" class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="in_progress">Sedang Dihitung</option>
                        <option value="completed">Selesai (Adjusted)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3.5">No. Opname</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Gudang</th>
                        <th class="px-5 py-3.5">Item Terdata</th>
                        <th class="px-5 py-3.5">Dibuat Oleh</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($opnames as $opn)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-750 transition">
                        <td class="px-5 py-3.5 font-medium text-blue-600 dark:text-blue-400 font-mono">
                            {{ $opn->opname_number }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">
                            {{ $opn->opname_date?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-white">
                            {{ $opn->warehouse?->name ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300">
                            {{ $opn->items->count() }} Item
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs">
                            {{ $opn->creator?->name ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($opn->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 flex items-center gap-1 w-max">
                                    <span class="material-symbols-outlined text-xs">check_circle</span> Selesai
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 flex items-center gap-1 w-max">
                                    <span class="material-symbols-outlined text-xs">schedule</span> Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="viewDetail({{ $opn->id }})" class="p-1.5 text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700" title="Lihat Hasil Opname">
                                    <span class="material-symbols-outlined text-base">visibility</span>
                                </button>
                                @if($opn->status !== 'completed')
                                <button wire:click="approveAndAdjust({{ $opn->id }})"
                                    wire:confirm="Yakin ingin menyetujui sesi opname ini? Sistem akan otomatis melakukan adjustment stock mutation pada item yang memiliki selisih."
                                    class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium flex items-center gap-1 shadow-sm">
                                    <span class="material-symbols-outlined text-xs">done_all</span> Setujui & Sesuaikan
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">inventory</span>
                            Belum ada sesi stok opname tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700">
            {{ $opnames->links() }}
        </div>
    </div>

    <!-- Modal Create Opname -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-4xl w-full p-6 space-y-6 shadow-xl border border-slate-200 dark:border-slate-700 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-700">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">playlist_add_check</span>
                    Mulai Sesi Stok Opname
                </h3>
                <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Gudang Target *</label>
                    <select wire:model="selectedWarehouseId" wire:change="loadWarehouseItems" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                    @error('selectedWarehouseId') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Opname *</label>
                    <input type="date" wire:model="opnameDate" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    @error('opnameDate') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Catatan Opname</label>
                <input type="text" wire:model="notes" placeholder="Contoh: Stok opname akhir bulan cabang pusat" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>

            <!-- List Item Opname Table -->
            <div class="flex-1 overflow-y-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-slate-600 sticky top-0 border-b dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">Nama Barang</th>
                            <th class="px-4 py-3 text-right">Stok Sistem</th>
                            <th class="px-4 py-3 text-right w-32">Stok Fisik Aktual</th>
                            <th class="px-4 py-3 text-right">Selisih</th>
                            <th class="px-4 py-3 w-40">Alasan Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($itemsData as $idx => $row)
                        <tr>
                            <td class="px-4 py-2 font-medium text-slate-800 dark:text-white">
                                {{ $row['name'] }}
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $row['sku'] }}</span>
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-slate-600 dark:text-slate-300">
                                {{ $row['system_quantity'] }} {{ $row['unit'] }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                <input type="number" step="any" min="0" wire:model="itemsData.{{ $idx }}.physical_quantity" wire:change="updatePhysicalQuantity({{ $idx }})"
                                    class="w-24 text-right text-xs py-1 px-2 rounded border-slate-300 dark:border-slate-700 font-mono font-bold dark:bg-slate-900 dark:text-white">
                            </td>
                            <td class="px-4 py-2 text-right font-mono font-bold {{ $row['difference_quantity'] > 0 ? 'text-emerald-600' : ($row['difference_quantity'] < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                {{ $row['difference_quantity'] > 0 ? '+'.$row['difference_quantity'] : $row['difference_quantity'] }}
                            </td>
                            <td class="px-4 py-2">
                                @if($row['difference_quantity'] != 0)
                                <select wire:model="itemsData.{{ $idx }}.adjustment_reason" class="w-full text-[11px] py-1 px-2 rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                    <option value="selisih_hitung">Selisih Hitung</option>
                                    <option value="barang_rusak">Barang Rusak</option>
                                    <option value="hilang">Barang Hilang</option>
                                    <option value="ditemukan">Barang Ditemukan</option>
                                </select>
                                @else
                                <span class="text-slate-400 italic text-[11px]">Sesuai</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Pilih gudang di atas untuk memuat daftar stok barang.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t dark:border-slate-700">
                <button wire:click="$set('showCreateModal', false)" type="button" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-lg">Batal</button>
                <button wire:click="saveOpname" type="button" class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm" {{ empty($itemsData) ? 'disabled' : '' }}>
                    Simpan Sesi Opname
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Detail Opname -->
    @if($showDetailModal && $selectedOpname)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-3xl w-full p-6 space-y-5 shadow-xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-700">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">info</span>
                        Rincian Hasil Opname: {{ $selectedOpname->opname_number }}
                    </h3>
                    <p class="text-xs text-slate-500">Gudang: {{ $selectedOpname->warehouse?->name }} | Tanggal: {{ $selectedOpname->opname_date?->format('d M Y') }}</p>
                </div>
                <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="overflow-x-auto max-h-80 border rounded-xl dark:border-slate-700">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-slate-600 border-b dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-2.5">Barang</th>
                            <th class="px-4 py-2.5 text-right">Stok Sistem</th>
                            <th class="px-4 py-2.5 text-right">Stok Fisik</th>
                            <th class="px-4 py-2.5 text-right">Selisih</th>
                            <th class="px-4 py-2.5">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($selectedOpname->items as $it)
                        <tr>
                            <td class="px-4 py-2 font-medium text-slate-800 dark:text-white">{{ $it->item?->name }}</td>
                            <td class="px-4 py-2 text-right font-mono">{{ (float)$it->system_quantity }}</td>
                            <td class="px-4 py-2 text-right font-mono font-bold">{{ (float)$it->physical_quantity }}</td>
                            <td class="px-4 py-2 text-right font-mono font-bold {{ $it->difference_quantity > 0 ? 'text-emerald-600' : ($it->difference_quantity < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                                {{ $it->difference_quantity > 0 ? '+'.(float)$it->difference_quantity : (float)$it->difference_quantity }}
                            </td>
                            <td class="px-4 py-2 text-slate-500 capitalize">{{ $it->adjustment_reason ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-3">
                <button wire:click="$set('showDetailModal', false)" class="px-4 py-2 text-sm bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg">Tutup</button>
            </div>
        </div>
    </div>
    @endif
</div>
