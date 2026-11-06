<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-3xl">build_circle</span>
                <span>Pemakaian Material Lapangan (Actual Usage)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan pemakaian aktual material oleh teknisi pada pemasangan baru (PSB) atau tiket maintenance pelanggan.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Catat Pemakaian Material</span>
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
            <div class="relative flex-1 min-w-[260px]">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Cari no pemakaian, nama pelanggan, keterangan..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[190px] flex-1 sm:flex-initial">
                    <select wire:model.live="workOrderFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                        <option value="">Semua Jenis Pekerjaan</option>
                        <option value="psb">Pemasangan Baru (PSB)</option>
                        <option value="maintenance">Gangguan / Maintenance</option>
                        <option value="relocation">Relokasi Pelanggan</option>
                    </select>
                </div>

                <div class="min-w-[190px] flex-1 sm:flex-initial">
                    <select wire:model.live="technicianFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                        <option value="">Semua Teknisi</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Usages Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">No. Pemakaian</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Teknisi</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Jenis Pekerjaan</th>
                        <th class="py-3 px-4">Redaman Optik</th>
                        <th class="py-3 px-4">Material Terpasang</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs sm:text-sm">
                    @forelse($usages as $usg)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-800 text-xs">
                                    {{ $usg->usage_number }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $usg->usage_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-bold">
                                {{ $usg->technician?->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200">
                                <div class="font-semibold">{{ $usg->customer?->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $usg->customer?->phone ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="uppercase font-bold text-[10px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $usg->work_order_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700 dark:text-slate-300 text-xs">
                                {{ $usg->optical_power ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    @foreach($usg->items as $it)
                                        <div class="text-xs">
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $it->item?->name }}:</span>
                                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($it->quantity, 0) }} {{ $it->item?->unit }}</span>
                                            @if($it->itemSerial)
                                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 ml-1">
                                                    SN: {{ $it->itemSerial->serial_number }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button wire:click="showDetail({{ $usg->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                    <span>Detail</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">build_circle</span>
                                    <p class="text-sm">Belum ada laporan pemakaian material aktual.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usages->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80">
                {{ $usages->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL CATAT PEMAKAIAN --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-4xl w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <span class="material-symbols-outlined">build_circle</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">Laporan Pemakaian Material Aktual</h3>
                            <p class="text-xs text-slate-500">Material yang dipegang akan berkurang dan status Serial Number menjadi 'installed'.</p>
                        </div>
                    </div>
                    <button wire:click="$set('showCreateModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit="saveUsage" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Teknisi Pelaksana *</label>
                            <select wire:model.live="technician_id" required
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200 font-bold">
                                <option value="">-- Pilih Teknisi --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            @error('technician_id') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Pelanggan Tujuan</label>
                            <select wire:model="customer_id"
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200">
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Jenis Pekerjaan *</label>
                            <select wire:model="work_order_type" required
                                    class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200">
                                <option value="psb">Pemasangan Baru (PSB)</option>
                                <option value="maintenance">Gangguan / Maintenance</option>
                                <option value="relocation">Relokasi Pelanggan</option>
                                <option value="other">Lain-lain</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Tanggal Pemakaian *</label>
                            <input wire:model="usage_date" type="date" required
                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Redaman Optik (OPM)</label>
                            <input wire:model="optical_power" type="text" placeholder="Contoh: -19.5 dBm"
                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Catatan</label>
                            <input wire:model="notes" type="text" placeholder="Keterangan tambahan..."
                                   class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    {{-- Items Input Section --}}
                    <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-black text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-indigo-600">inventory_2</span>
                                <span>Pilih Material dari Stok Pegangan Teknisi</span>
                            </h4>
                            <button wire:click="addItemRow" type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 rounded-xl border border-indigo-200 dark:border-indigo-800 transition-colors">
                                <span class="material-symbols-outlined text-sm">add</span>
                                <span>Tambah Baris</span>
                            </button>
                        </div>

                        @if($availableCustodies->isEmpty())
                            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">warning</span>
                                <span>Teknisi ini belum memiliki stok material di tangan (Custody kosong). Silakan lakukan serah terima atau dispatch dari gudang terlebih dahulu.</span>
                            </div>
                        @endif

                        <div class="space-y-3">
                            @foreach($usageItems as $idx => $row)
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                    <div class="sm:col-span-6">
                                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Pilih Material Pegangan #{{ $idx + 1 }} *</label>
                                        <select wire:model.live="usageItems.{{ $idx }}.custody_id" required
                                                class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200">
                                            <option value="">-- Pilih dari Stok Custody --</option>
                                            @foreach($availableCustodies as $custody)
                                                <option value="{{ $custody->id }}">
                                                    {{ $custody->item?->name }}
                                                    @if($custody->itemSerial)
                                                        [SN: {{ $custody->itemSerial->serial_number }}]
                                                    @endif
                                                    (Tersedia: {{ number_format($custody->available_quantity, 0) }} {{ $custody->item?->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Jumlah Terpakai ({{ $row['unit'] ?? 'unit' }}) *</label>
                                        <input wire:model="usageItems.{{ $idx }}.quantity" type="number" step="any" min="0.01" max="{{ $row['max_available'] ?? 9999 }}" required
                                               class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200">
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Keterangan</label>
                                        <input wire:model="usageItems.{{ $idx }}.notes" type="text" placeholder="opsional"
                                               class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-200">
                                    </div>

                                    <div class="sm:col-span-1 flex justify-end pt-4">
                                        <button wire:click="removeItemRow({{ $idx }})" type="button"
                                                class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700/80">
                        <button wire:click="$set('showCreateModal', false)" type="button"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Simpan Pemakaian Aktual</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL USAGE --}}
    @if($showDetailModal && $selectedUsage)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-2xl w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <span class="material-symbols-outlined">receipt</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">{{ $selectedUsage->usage_number }}</h3>
                            <p class="text-xs text-slate-500">Detail Pemakaian Material Lapangan</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Teknisi</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedUsage->technician?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Pelanggan</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedUsage->customer?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Redaman Optik</span>
                        <span class="font-bold font-mono text-indigo-600 dark:text-indigo-400">{{ $selectedUsage->optical_power ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tanggal</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedUsage->usage_date->format('d/m/Y') }}</span>
                    </div>
                </div>

                {{-- Detail Items --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Material Terpasang</h4>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                        @foreach($selectedUsage->items as $it)
                            <div class="p-3 bg-white dark:bg-slate-800 flex items-center justify-between text-xs sm:text-sm">
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $it->item?->name }}</span>
                                    @if($it->itemSerial)
                                        <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 ml-2">
                                            SN: {{ $it->itemSerial->serial_number }}
                                        </span>
                                    @endif
                                </div>
                                <div class="font-black text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($it->quantity, 0) }} {{ $it->item?->unit }}
                                </div>
                            </div>
                        @endforeach
                    </div>
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
