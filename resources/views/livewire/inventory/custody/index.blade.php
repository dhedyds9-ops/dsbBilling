<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 text-3xl">handshake</span>
                <span>Distribusi & Serah Terima Material</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Pelacakan kepemilikan sementara barang di tangan Reseller dan serah terima ke Teknisi pelaksana.
            </p>
        </div>
    </div>

    {{-- Banner Edukasi Custody Tracking --}}
    <div class="p-4 rounded-2xl bg-gradient-to-r from-purple-500/10 via-indigo-500/10 to-blue-500/10 border border-purple-200 dark:border-purple-800/80 flex items-start gap-3">
        <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 mt-0.5 text-xl">verified_user</span>
        <div class="text-xs space-y-0.5">
            <span class="font-bold text-purple-900 dark:text-purple-300 block">Company-Owned Inventory with Custody Tracking</span>
            <p class="text-slate-600 dark:text-slate-400">
                Material yang telah dikeluarkan dari gudang tetap merupakan aset perusahaan. Barang dicatat sebagai titipan sementara dan baru diakui sebagai biaya terpakai setelah teknisi memverifikasi instalasi aktual di lokasi pelanggan.
            </p>
        </div>
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

    {{-- Tabs Navigation --}}
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 overflow-x-auto whitespace-nowrap">
        <button wire:click="setTab('custody')" type="button"
                class="px-4 py-2.5 font-bold text-xs sm:text-sm border-b-2 transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'custody' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-sm">inventory_2</span>
            <span>Barang di Tangan (Custody)</span>
        </button>

        <button wire:click="setTab('transfers')" type="button"
                class="px-4 py-2.5 font-bold text-xs sm:text-sm border-b-2 transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'transfers' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <span class="material-symbols-outlined text-sm">swap_horiz</span>
            <span>Riwayat & Konfirmasi Serah Terima</span>
        </button>
    </div>

    {{-- TAB 1: CUSTODY (BARANG DI TANGAN) --}}
    @if($activeTab === 'custody')
        <div class="space-y-4">
            {{-- Filter --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-4 shadow-xs">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="relative flex-1 min-w-0">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Cari barang, serial number, atau nama pemegang..."
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-purple-500 text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
                    </div>

                    <div class="w-full sm:w-auto sm:min-w-[220px]">
                        <select wire:model.live="holderTypeFilter"
                                class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-purple-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                            <option value="">Semua Pemegang (Reseller & Teknisi)</option>
                            <option value="reseller">Reseller Saja</option>
                            <option value="technician">Teknisi Saja</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Custody Table --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="py-3 px-4">Pemegang Sementara</th>
                                <th class="py-3 px-4">Barang / Material</th>
                                <th class="py-3 px-4">Serial Number</th>
                                <th class="py-3 px-4 text-right">Jumlah Dipegang</th>
                                <th class="py-3 px-4">Pelanggan Tujuan</th>
                                <th class="py-3 px-4">Status Aset</th>
                                <th class="py-3 px-4 text-right">Aksi Serah Terima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs sm:text-sm">
                            @forelse($custodies as $c)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 dark:text-slate-100">{{ $c->holder?->name ?? '-' }}</div>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase px-1.5 py-0.5 rounded {{ $c->holder_type === 'reseller' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' }}">
                                            {{ $c->holder_type }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $c->item?->name }}
                                        <span class="text-slate-400 font-mono text-[11px] ml-1">[{{ $c->item?->sku }}]</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs">
                                        @if($c->itemSerial)
                                            <span class="font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                                {{ $c->itemSerial->serial_number }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">Non-Serial</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-slate-100">
                                        {{ number_format($c->available_quantity, 0) }} {{ $c->item?->unit ?? 'unit' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                                        {{ $c->customer?->name ?? 'Stok Lapangan' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded text-[11px] bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">
                                            Titipan Sementara
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        @if($c->holder_type === 'reseller' && $c->available_quantity > 0)
                                            <button wire:click="openHandoverModal({{ $c->id }})"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                                                <span class="material-symbols-outlined text-xs">handshake</span>
                                                <span>Serahkan ke Teknisi</span>
                                            </button>
                                        @else
                                            <span class="text-[11px] text-slate-400 font-semibold">Siap dipasang</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">inventory_2</span>
                                            <p class="text-sm">Tidak ada barang yang sedang berada dalam titipan (custody).</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($custodies->hasPages())
                    <div class="p-4 border-t border-slate-200 dark:border-slate-700/80">
                        {{ $custodies->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- TAB 2: TRANSFERS (SERAH TERIMA RESELLER -> TEKNISI) --}}
    @if($activeTab === 'transfers')
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4">No. Serah Terima</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Pihak Menyerahkan</th>
                            <th class="py-3 px-4">Penerima (Teknisi)</th>
                            <th class="py-3 px-4">Barang & Serial</th>
                            <th class="py-3 px-4 text-right">Jumlah</th>
                            <th class="py-3 px-4">Status Penerimaan</th>
                            <th class="py-3 px-4 text-right">Aksi Konfirmasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs sm:text-sm">
                        @forelse($transfers as $trf)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-purple-700 dark:text-purple-400 text-xs">
                                    {{ $trf->transfer_number }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                    {{ $trf->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200 font-semibold">
                                    {{ $trf->fromUser?->name ?? '-' }}
                                    <span class="text-[10px] text-slate-400 block uppercase">Reseller</span>
                                </td>
                                <td class="py-3.5 px-4 text-indigo-600 dark:text-indigo-400 font-bold">
                                    {{ $trf->toUser?->name ?? '-' }}
                                    <span class="text-[10px] text-slate-400 block uppercase font-normal">Teknisi</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200">
                                    <div class="font-semibold">{{ $trf->item?->name }}</div>
                                    @if($trf->itemSerial)
                                        <span class="font-mono text-[10px] text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                            SN: {{ $trf->itemSerial->serial_number }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-slate-100">
                                    {{ number_format($trf->quantity, 0) }} {{ $trf->item?->unit ?? 'unit' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($trf->status === 'accepted')
                                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800 text-[11px]">
                                            <span class="material-symbols-outlined text-xs">check</span>
                                            Diterima Teknisi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 font-bold text-amber-700 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800 text-[11px]">
                                            <span class="material-symbols-outlined text-xs">schedule</span>
                                            Menunggu Konfirmasi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($trf->status === 'pending_acceptance')
                                        @if($trf->to_user_id === auth()->id() || auth()->user()?->isSuperAdmin())
                                            <button wire:click="acceptTransfer({{ $trf->id }})"
                                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-1 ml-auto">
                                                <span class="material-symbols-outlined text-xs">check_circle</span>
                                                <span>Konfirmasi Terima</span>
                                            </button>
                                        @else
                                            <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold italic">Menunggu Teknisi</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">swap_horiz</span>
                                        <p class="text-sm">Belum ada catatan serah terima material.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transfers->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-700/80">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- MODAL SERAH TERIMA KE TEKNISI (METODE A) --}}
    @if($showHandoverModal && $selectedCustody)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <span class="material-symbols-outlined">handshake</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Serahkan Material ke Teknisi</h3>
                            <p class="text-xs text-slate-500">Metode A: Alihkan titipan material dari Reseller ke Teknisi</p>
                        </div>
                    </div>
                    <button wire:click="$set('showHandoverModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700/80 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Barang:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedCustody->item?->name }}</span>
                    </div>
                    @if($selectedCustody->itemSerial)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Serial Number:</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $selectedCustody->itemSerial->serial_number }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-400">Jumlah Diserahkan:</span>
                        <span class="font-black text-slate-900 dark:text-slate-100">{{ number_format($selectedCustody->available_quantity, 0) }} {{ $selectedCustody->item?->unit }}</span>
                    </div>
                </div>

                <form wire:submit="submitHandover" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Teknisi Penerima *</label>
                        <select wire:model="handover_to_user_id" required
                                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-purple-500 text-slate-800 dark:text-slate-200 font-bold">
                            <option value="">-- Pilih Teknisi Pelaksana --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                        @error('handover_to_user_id') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Pelanggan Tujuan <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <select wire:model="handover_customer_id"
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 text-slate-800 dark:text-slate-200 font-medium">
                                <option value="">-- Tanpa Pelanggan Khusus --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code ?? ($c->customer_code ?? $c->phone) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Pekerjaan</label>
                            <select wire:model="handover_work_order_type"
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 text-slate-800 dark:text-slate-200">
                                <option value="psb">Pemasangan Baru (PSB)</option>
                                <option value="maintenance">Gangguan / Maintenance</option>
                                <option value="other">Operasional Lapangan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Jumlah yang Diserahkan ({{ $selectedCustody->item?->unit ?? 'unit' }}) *
                        </label>
                        @if($selectedCustody->itemSerial)
                            <input type="text" value="1 {{ $selectedCustody->item?->unit ?? 'unit' }} (SN: {{ $selectedCustody->itemSerial->serial_number }})" disabled
                                   class="w-full py-2 px-3 bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">
                        @else
                            <input wire:model="handover_quantity" type="number" step="any" min="0.01" max="{{ $selectedCustody->available_quantity }}" required
                                   class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold focus:ring-2 focus:ring-purple-500 text-slate-800 dark:text-slate-200">
                            <span class="text-[11px] text-slate-400 mt-0.5 block">Tersedia di tangan Anda: <strong>{{ number_format($selectedCustody->available_quantity, 0) }} {{ $selectedCustody->item?->unit }}</strong></span>
                        @endif
                        @error('handover_quantity') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Serah Terima</label>
                        <input wire:model="handover_notes" type="text" placeholder="Contoh: Diserahkan di lokasi pasang untuk penarikan dropcore..."
                               class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 text-slate-800 dark:text-slate-200">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/80">
                        <button wire:click="$set('showHandoverModal', false)" type="button"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">send</span>
                            <span>Kirim Serah Terima</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
