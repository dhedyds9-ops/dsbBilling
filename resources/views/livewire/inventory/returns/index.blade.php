<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-3xl">assignment_return</span>
                <span>Retur & Pengembalian Material (Returns & QC)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Pengembalian material sisa, batal pasang, atau rusak dari Teknisi/Reseller dengan alur inspeksi Quality Control (QC) gudang.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Ajukan Retur Barang</span>
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-500 text-white rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-500 text-white rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-4 shadow-xs">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <div class="relative flex-1 min-w-0">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Cari no retur, nama pengembali, catatan..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="w-full sm:w-auto sm:min-w-[210px] flex-1 sm:flex-initial">
                    <select wire:model.live="statusFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                        <option value="">Semua Status Retur</option>
                        <option value="pending_qc">Menunggu QC</option>
                        <option value="accepted">Diterima / Selesai QC</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>

                <div class="w-full sm:w-auto sm:min-w-[210px] flex-1 sm:flex-initial">
                    <select wire:model.live="warehouseFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                        <option value="">Semua Gudang Tujuan</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Returns Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">No. Retur</th>
                        <th class="py-3 px-4">Tanggal Pengajuan</th>
                        <th class="py-3 px-4">Pihak Pengembali</th>
                        <th class="py-3 px-4">Gudang Tujuan</th>
                        <th class="py-3 px-4 text-center">Jml Macam</th>
                        <th class="py-3 px-4">Status QC</th>
                        <th class="py-3 px-4">Pemeriksa QC</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs sm:text-sm">
                    @forelse($returns as $ret)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-teal-700 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/40 px-2 py-0.5 rounded-md border border-teal-200 dark:border-teal-800 text-xs">
                                    {{ $ret->return_number }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $ret->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $ret->returner?->name ?? '-' }}</div>
                                <span class="text-[10px] uppercase font-bold text-slate-400">
                                    {{ $ret->returner_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-semibold">
                                {{ $ret->warehouse?->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs">
                                    {{ $ret->items->count() }} item
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($ret->status === 'accepted')
                                    <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-md border text-[11px] bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                        <span class="material-symbols-outlined text-xs">check_circle</span>
                                        Diterima / Restocked
                                    </span>
                                @elseif($ret->status === 'pending_qc')
                                    <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-md border text-[11px] bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                        <span class="material-symbols-outlined text-xs">schedule</span>
                                        Menunggu QC Gudang
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-md border text-[11px] bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $ret->inspector?->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button wire:click="showDetail({{ $ret->id }})"
                                            class="p-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg transition-colors"
                                            title="Lihat Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </button>

                                    @if($ret->status === 'pending_qc')
                                        <button wire:click="openQCModal({{ $ret->id }})"
                                                class="px-3 py-1 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs">fact_check</span>
                                            <span>Inspeksi QC</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">assignment_return</span>
                                    <p class="text-sm">Belum ada data retur material.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL AJUKAN RETUR --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-3xl w-full p-5 sm:p-6 space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-950/50 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <span class="material-symbols-outlined">assignment_return</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">Ajukan Pengembalian Material (Retur)</h3>
                            <p class="text-xs text-slate-500">Material sisa atau rusak akan dikembalikan dari pegangan fisik ke gudang.</p>
                        </div>
                    </div>
                    <button wire:click="$set('showCreateModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit="saveReturn" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Pihak Pengembali *</label>
                            <select wire:model.live="returner_id" required
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200 font-bold">
                                <option value="">-- Pilih Pengembali --</option>
                                @foreach($returners as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roles->pluck('name')->first() ?? $u->role }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Gudang Tujuan Penerima *</label>
                            <select wire:model="warehouse_id" required
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200">
                                <option value="">-- Pilih Gudang --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Catatan Pengembalian</label>
                            <input wire:model="notes" type="text" placeholder="Contoh: Sisa dropcore & ONU batal pasang..."
                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    {{-- Items Input Section --}}
                    <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-black text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-teal-600">inventory_2</span>
                                <span>Pilih Material yang Dikembalikan</span>
                            </h4>
                            <button wire:click="addItemRow" type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-teal-700 dark:text-teal-300 bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 rounded-xl border border-teal-200 dark:border-teal-800 transition-colors">
                                <span class="material-symbols-outlined text-sm">add</span>
                                <span>Tambah Baris</span>
                            </button>
                        </div>

                        @if(empty($returner_id))
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-slate-400">info</span>
                                <span>Silakan pilih pihak pengembali terlebih dahulu untuk memuat daftar stok barang di tangan.</span>
                            </div>
                        @elseif($availableCustodies->isEmpty())
                            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">warning</span>
                                <span>Pengembali ini tidak memiliki stok barang yang sedang dipegang (Custody kosong).</span>
                            </div>
                        @endif

                        <div class="space-y-3">
                            @foreach($returnItems as $idx => $row)
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs space-y-3">
                                    {{-- Header Row: Badge & Delete Button --}}
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700/60">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 text-xs font-black">
                                                <span class="material-symbols-outlined text-xs notranslate" translate="no">keyboard_return</span>
                                                <span>Material Retur #{{ $idx + 1 }}</span>
                                            </span>
                                            @if(!empty($row['unit']))
                                                <span class="text-[11px] text-slate-400 font-medium">Satuan: <strong>{{ $row['unit'] }}</strong></span>
                                            @endif
                                        </div>
                                        @if(count($returnItems) > 1)
                                            <button wire:click="removeItemRow({{ $idx }})" type="button"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors"
                                                    title="Hapus baris ini">
                                                <span class="material-symbols-outlined text-sm notranslate" translate="no">delete</span>
                                                <span>Hapus</span>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Inputs Grid --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                        <div class="sm:col-span-5">
                                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Pilih Material di Tangan *</label>
                                            <select wire:model.live="returnItems.{{ $idx }}.custody_id" required
                                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200">
                                                <option value="">-- Pilih Material di Tangan --</option>
                                                @foreach($availableCustodies as $c)
                                                    <option value="{{ $c->id }}">
                                                        {{ $c->item?->name }}
                                                        @if($c->itemSerial) [SN: {{ $c->itemSerial->serial_number }}] @endif
                                                        (Ada: {{ number_format($c->available_quantity, 0) }} {{ $c->item?->unit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah *</label>
                                            <input wire:model="returnItems.{{ $idx }}.quantity" type="number" step="any" min="0.01" max="{{ $row['max_available'] ?? 9999 }}" required placeholder="1"
                                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200 font-bold">
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Kondisi *</label>
                                            <select wire:model="returnItems.{{ $idx }}.condition"
                                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200 font-bold">
                                                <option value="good">Baik / Layak Pakai</option>
                                                <option value="needs_check">Perlu Dicek Lab</option>
                                                <option value="damaged">Rusak / Mati Total</option>
                                            </select>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan (Opsional)</label>
                                            <input wire:model="returnItems.{{ $idx }}.qc_notes" type="text" placeholder="opsional"
                                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700/80">
                        <button wire:click="$set('showCreateModal', false)" type="button"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">send</span>
                            <span>Kirim Pengajuan Retur</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL QC INSPECTION (PENGURUS GUDANG) --}}
    @if($showQCModal && $selectedReturnId)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-3xl w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-950/50 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <span class="material-symbols-outlined">fact_check</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Verifikasi & Inspeksi QC Material</h3>
                            <p class="text-xs text-slate-500">Barang dengan kondisi 'Baik' akan langsung masuk kembali ke stok fisik gudang.</p>
                        </div>
                    </div>
                    <button wire:click="$set('showQCModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit="processQC" class="space-y-4">
                    <div class="space-y-3">
                        @foreach($qcInspectionItems as $idx => $row)
                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700/80 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200">
                                        {{ $row['item_name'] }}
                                        @if(!empty($row['serial_number']))
                                            <span class="font-mono text-xs px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 ml-1">
                                                SN: {{ $row['serial_number'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="font-black text-teal-600 dark:text-teal-400 text-xs">
                                        Jumlah: {{ number_format($row['quantity'], 0) }} {{ $row['unit'] }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Hasil Pemeriksaan Kondisi QC *</label>
                                        <select wire:model="qcInspectionItems.{{ $idx }}.condition" required
                                                class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200 font-bold">
                                            <option value="good">Baik / Layak Pakai (Restock)</option>
                                            <option value="needs_check">Perlu Dicek Lab / Karantina</option>
                                            <option value="damaged">Rusak / RMA Garansi Vendor</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Tindakan Gudang *</label>
                                        <select wire:model="qcInspectionItems.{{ $idx }}.action_taken" required
                                                class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 text-slate-800 dark:text-slate-200">
                                            <option value="restock">Masukkan ke Saldo Stok Siap Pakai</option>
                                            <option value="quarantine">Karantina / Pisahkan</option>
                                            <option value="rma">Klaim RMA Vendor</option>
                                            <option value="scrap">Musnahkan (Scrap)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/80">
                        <button wire:click="$set('showQCModal', false)" type="button"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            <span>Konfirmasi QC & Terima Barang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL RETUR --}}
    @if($showDetailModal && $selectedReturn)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-2xl w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-950/50 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <span class="material-symbols-outlined">receipt</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">{{ $selectedReturn->return_number }}</h3>
                            <p class="text-xs text-slate-500">Detail Pengembalian Material</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Pengembali</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedReturn->returner?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Gudang Penerima</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedReturn->warehouse?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Status QC</span>
                        <span class="font-bold uppercase text-slate-800 dark:text-slate-200">{{ $selectedReturn->status }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Petugas Pemeriksa</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedReturn->inspector?->name ?? '-' }}</span>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 font-bold uppercase text-[10px] text-slate-500">
                            <tr>
                                <th class="py-2.5 px-3">Material</th>
                                <th class="py-2.5 px-3 text-right">Jumlah</th>
                                <th class="py-2.5 px-3">Kondisi QC</th>
                                <th class="py-2.5 px-3">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach($selectedReturn->items as $it)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $it->item?->name }}
                                        @if($it->itemSerial)
                                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 ml-1">
                                                SN: {{ $it->itemSerial->serial_number }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-black">{{ number_format($it->quantity, 0) }} {{ $it->item?->unit }}</td>
                                    <td class="py-2.5 px-3">
                                        <span class="uppercase text-[10px] font-bold px-1.5 py-0.5 rounded {{ $it->condition === 'good' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $it->condition }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400 uppercase text-[10px] font-semibold">
                                        {{ $it->action_taken }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-2">
                    <button wire:click="closeDetailModal" type="button"
                            class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
