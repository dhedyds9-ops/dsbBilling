<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-3xl">assignment_add</span>
                <span>Permintaan Barang (Requisitions)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Alur pengajuan material oleh Reseller, Pengurus, atau Teknisi untuk PSB dan maintenance pelanggan.
            </p>
        </div>

        @if($canCreateRequisition)
            <button wire:click="openCreateModal" type="button"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Ajukan Permintaan Baru</span>
            </button>
        @endif
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
                       placeholder="Cari no permintaan, nama pelanggan, keterangan..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[210px] flex-1 sm:flex-initial">
                    <select wire:model.live="warehouseFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                        <option value="">Semua Gudang Asal</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[210px] flex-1 sm:flex-initial">
                    <select wire:model.live="statusFilter"
                            class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Persetujuan</option>
                        <option value="approved">Telah Disetujui</option>
                        <option value="dispatched">Telah Dikeluarkan</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Requisitions Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700/80 bg-slate-50/75 dark:bg-slate-900/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">No. Permintaan</th>
                        <th class="py-3 px-4">Pemohon</th>
                        <th class="py-3 px-4">Metode Distribusi</th>
                        <th class="py-3 px-4">Pelanggan & Pekerjaan</th>
                        <th class="py-3 px-4">Teknisi Ditunjuk</th>
                        <th class="py-3 px-4 text-center">Jml Barang</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs sm:text-sm">
                    @forelse($requisitions as $req)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-slate-800 dark:text-slate-200 text-xs">
                                    {{ $req->requisition_number }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $req->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-semibold">{{ $req->requester?->name ?? '-' }}</div>
                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    {{ $req->requester_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($req->distribution_method === 'pickup_reseller')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800">
                                        <span class="material-symbols-outlined text-xs">store</span>
                                        Metode A: Reseller Ambil
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800">
                                        <span class="material-symbols-outlined text-xs">local_shipping</span>
                                        Metode B: Gudang ke Teknisi
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                <div class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $req->customer?->name ?? ($req->work_order_type === 'stock_topup' ? 'Stok Cadangan Reseller' : 'Stok Operasional') }}
                                </div>
                                @php
                                    $woLabel = match($req->work_order_type) {
                                        'stock_topup' => 'Pengisian Stok Reseller',
                                        'psb' => 'Pemasangan Baru (PSB)',
                                        'maintenance' => 'Maintenance',
                                        'other' => 'Operasional Lainnya',
                                        default => strtoupper($req->work_order_type ?? '-')
                                    };
                                    $woColor = match($req->work_order_type) {
                                        'stock_topup' => 'text-amber-600 dark:text-amber-400 font-bold',
                                        'psb' => 'text-emerald-600 dark:text-emerald-400 font-semibold',
                                        default => 'text-slate-500 font-medium'
                                    };
                                @endphp
                                <div class="text-[11px] {{ $woColor }}">{{ $woLabel }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                @if($req->designatedTechnician)
                                    <div class="font-semibold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">engineering</span>
                                        <span>{{ $req->designatedTechnician->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">person</span>
                                        <span>Dipegang Reseller</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs">
                                    {{ $req->items->count() }} item
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $badge = match($req->status) {
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                        'approved' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                        'dispatched' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                                        'received' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                        'rejected' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
                                    };
                                    $lbl = match($req->status) {
                                        'pending' => 'Menunggu Approval',
                                        'approved' => 'Disetujui',
                                        'dispatched' => 'Barang Dikeluarkan',
                                        'received' => 'Diterima',
                                        'rejected' => 'Ditolak',
                                        default => $req->status
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 font-bold px-2 py-0.5 rounded-md border text-[11px] {{ $badge }}">
                                    {{ $lbl }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button wire:click="showDetail({{ $req->id }})"
                                            class="p-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg transition-colors"
                                            title="Lihat Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </button>

                                    {{-- Tombol Proses (Approval) HANYA untuk Petugas Gudang / Manager, BUKAN untuk Reseller atau Teknisi --}}
                                    @if($canManageWarehouse && !$isReseller)
                                        @if($req->status === 'pending' && $req->requester_id !== auth()->id())
                                            <button wire:click="openApprovalModal({{ $req->id }})"
                                                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1 shadow-xs">
                                                <span class="material-symbols-outlined text-xs">done</span>
                                                <span>Proses</span>
                                            </button>
                                        @endif

                                        @if($req->status === 'approved')
                                            <a href="{{ route('inventory.dispatches.index', ['requisition_id' => $req->id]) }}"
                                               class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1 shadow-xs">
                                                <span class="material-symbols-outlined text-xs">local_shipping</span>
                                                <span>Dispatch</span>
                                            </a>
                                        @endif
                                    @endif

                                    {{-- Tombol Konfirmasi Terima Barang untuk Pemohon (Reseller / Teknisi) saat barang sudah di-dispatch --}}
                                    @if(($isReseller || $isTechnician) && $req->status === 'dispatched' && ($req->requester_id === auth()->id() || $req->designated_technician_id === auth()->id()))
                                        <button wire:click="confirmReceipt({{ $req->id }})"
                                                class="px-2.5 py-1 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1 shadow-xs animate-pulse"
                                                title="Konfirmasi bahwa fisik barang telah Anda terima dari Gudang">
                                            <span class="material-symbols-outlined text-xs">inventory_2</span>
                                            <span>Konfirmasi Terima</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">assignment</span>
                                    <p class="text-sm">Belum ada data permintaan barang.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requisitions->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80">
                {{ $requisitions->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL BUAT PERMINTAAN BARANG --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-2xl w-full p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-xl notranslate" translate="no">assignment_turned_in</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Form Permintaan Barang</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pengajuan barang material gudang untuk kebutuhan PSB atau maintenance pelanggan.</p>
                        </div>
                    </div>
                    <button wire:click="$set('showCreateModal', false)" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        <span class="material-symbols-outlined text-lg notranslate" translate="no">close</span>
                    </button>
                </div>

                <form wire:submit="saveRequisition" class="space-y-4">
                    {{-- Metode Distribusi Compact Cards --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Metode Distribusi Pengambilan Barang *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <label class="cursor-pointer p-2.5 rounded-xl border-2 transition-all flex items-start gap-2.5 {{ $distribution_method === 'pickup_reseller' ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30' }}">
                                <input type="radio" wire:model.live="distribution_method" value="pickup_reseller" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs">Metode A: Reseller Ambil Barang</div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Gudang ke Reseller, lalu reseller serahkan ke teknisi.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer p-2.5 rounded-xl border-2 transition-all flex items-start gap-2.5 {{ $distribution_method === 'direct_technician' ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30' }}">
                                <input type="radio" wire:model.live="distribution_method" value="direct_technician" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs">Metode B: Gudang ke Teknisi Langsung</div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Atas nama Reseller, tetapi fisik barang diserahkan ke Teknisi.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Form Fields 2-Column Balanced Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Kebutuhan Pengajuan *</label>
                            <select wire:model.live="work_order_type" required
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-bold focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                <option value="stock_topup">1. Pengisian Stok Reseller (Cadangan Operasional)</option>
                                <option value="psb">2. Kebutuhan Pekerjaan PSB (Pelanggan Baru)</option>
                                <option value="maintenance">3. Kebutuhan Maintenance (Gangguan)</option>
                                <option value="other">4. Kebutuhan Operasional Lainnya</option>
                            </select>
                        </div>

                        @if($work_order_type === 'stock_topup')
                            <div class="sm:col-span-2 p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/80 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-2.5">
                                <span class="material-symbols-outlined text-base text-amber-600 dark:text-amber-400 shrink-0 mt-0.5">info</span>
                                <div>
                                    <strong class="block font-bold">Mode Pengisian Stok Reseller:</strong>
                                    <span>Material yang diminta akan diserahkan untuk disimpan sebagai stok operasional Reseller. Penunjukan teknisi dan pelanggan tidak diperlukan saat pengajuan ini.</span>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Gudang Sumber *</label>
                            <select wire:model="warehouse_id" required
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                <option value="">-- Pilih Gudang Sumber --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Pelanggan Tujuan <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <select wire:model="customer_id"
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 font-medium">
                                <option value="">-- Tanpa Pelanggan (Stok Reseller) --</option>
                                @foreach($customers as $c)
                                    @php
                                        $pkg = $c->customerServices->first()?->serviceProfile?->name;
                                    @endphp
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code ?? ($c->customer_code ?? $c->phone) }}){{ $pkg ? ' - [' . $pkg . ']' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Teknisi Pelaksana <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <select wire:model="designated_technician_id"
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                <option value="">-- Belum Ditunjuk / Dipegang Reseller --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            @error('designated_technician_id') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan (Opsional)</label>
                            <input wire:model="notes" type="text" placeholder="misal: Pengisian stok modem awal bulan..."
                                   class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    {{-- Items Input Section --}}
                    <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-700/80">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600 text-base notranslate" translate="no">inventory</span>
                                <span>Daftar Barang yang Diminta</span>
                            </h4>
                            <button wire:click="addItemRow" type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 rounded-xl border border-emerald-200 dark:border-emerald-800 transition-colors">
                                <span class="material-symbols-outlined text-sm notranslate" translate="no">add</span>
                                <span>Tambah Baris</span>
                            </button>
                        </div>

                        @error('requisitionItems')
                            <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                        @enderror

                        <div class="space-y-3">
                            @foreach($requisitionItems as $idx => $row)
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 space-y-2.5">
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-[11px] font-bold">
                                                <span>Item #{{ $idx + 1 }}</span>
                                            </span>
                                            @if(!empty($row['unit']))
                                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Satuan: <strong>{{ $row['unit'] }}</strong></span>
                                            @endif
                                        </div>
                                        @if(count($requisitionItems) > 1)
                                            <button wire:click="removeItemRow({{ $idx }})" type="button"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors"
                                                    title="Hapus baris ini">
                                                <span class="material-symbols-outlined text-sm notranslate" translate="no">delete</span>
                                                <span>Hapus</span>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-start">
                                        <div class="sm:col-span-6">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Pilih Barang Katalog *</label>
                                            <select wire:model.live="requisitionItems.{{ $idx }}.item_id" required
                                                    class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 shadow-xs">
                                                <option value="">-- Pilih Barang dari Katalog --</option>
                                                @foreach($availableItems as $it)
                                                    <option value="{{ $it->id }}">[{{ $it->sku }}] {{ $it->name }} ({{ $it->category }} - {{ $it->unit }})</option>
                                                @endforeach
                                            </select>
                                            @error("requisitionItems.{$idx}.item_id") <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah ({{ $row['unit'] ?? 'unit' }}) *</label>
                                            <input wire:model="requisitionItems.{{ $idx }}.quantity" type="number" step="any" min="0.01" required placeholder="1"
                                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 font-bold">
                                            @error("requisitionItems.{$idx}.quantity") <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="sm:col-span-4">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan (Opsional)</label>
                                            <input wire:model="requisitionItems.{{ $idx }}.notes" type="text" placeholder="misal: pasang baru..."
                                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-3 pt-3.5 border-t border-slate-100 dark:border-slate-700/80">
                        <button wire:click="$set('showCreateModal', false)" type="button"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-md transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm notranslate" translate="no">send</span>
                            <span>Kirim Permintaan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL APPROVAL / REJECTION --}}
    @if($showApprovalModal && $selectedRequisition)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <span class="material-symbols-outlined">rule</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Persetujuan Permintaan</h3>
                            <p class="text-xs text-slate-500 font-mono">{{ $selectedRequisition->requisition_number }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showApprovalModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700/80 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Pemohon:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedRequisition->requester?->name }} ({{ $selectedRequisition->requester_type }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Pelanggan:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedRequisition->customer?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Teknisi Ditunjuk:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedRequisition->designatedTechnician?->name ?? '-' }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span class="font-bold text-slate-600 dark:text-slate-300 block mb-1">Daftar Barang:</span>
                        <ul class="space-y-1">
                            @foreach($selectedRequisition->items as $it)
                                <li class="flex justify-between text-[11px]">
                                    <span>{{ $it->item?->name }}</span>
                                    <span class="font-bold">{{ number_format($it->quantity_requested, 0) }} {{ $it->item?->unit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Opsi Tolak Form --}}
                <div class="space-y-2 pt-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400">Alasan Penolakan (Hanya diisi jika menolak)</label>
                    <textarea wire:model="rejection_reason" rows="2" placeholder="Tulis alasan jika menolak permintaan..."
                              class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 text-slate-800 dark:text-slate-200"></textarea>
                    @error('rejection_reason') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/80">
                    <button wire:click="rejectRequisition" type="button"
                            class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-bold transition-colors">
                        Tolak Permintaan
                    </button>
                    <button wire:click="approveRequisition" type="button"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span>Setujui Permintaan</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL PERMINTAAN --}}
    @if($showDetailModal && $selectedRequisition)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-2xl w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined">receipt</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">{{ $selectedRequisition->requisition_number }}</h3>
                            <p class="text-xs text-slate-500">Detail Permintaan Barang</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Gudang</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedRequisition->warehouse?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Pemohon</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedRequisition->requester?->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Teknisi Ditunjuk</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedRequisition->designatedTechnician?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Status</span>
                        <span class="font-bold uppercase text-slate-800 dark:text-slate-200">{{ $selectedRequisition->status }}</span>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 font-bold uppercase text-[10px] text-slate-500">
                            <tr>
                                <th class="py-2.5 px-3">Barang</th>
                                <th class="py-2.5 px-3 text-right">Diminta</th>
                                <th class="py-2.5 px-3 text-right">Disetujui</th>
                                <th class="py-2.5 px-3 text-right">Terkirim (Dispatch)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach($selectedRequisition->items as $it)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $it->item?->name }}
                                        <span class="font-mono text-[10px] text-slate-400 ml-1">[{{ $it->item?->sku }}]</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold">{{ number_format($it->quantity_requested, 0) }} {{ $it->item?->unit }}</td>
                                    <td class="py-2.5 px-3 text-right font-bold text-blue-600">{{ number_format($it->quantity_approved ?? $it->quantity_requested, 0) }} {{ $it->item?->unit }}</td>
                                    <td class="py-2.5 px-3 text-right font-bold text-emerald-600">{{ number_format($it->quantity_dispatched, 0) }} {{ $it->item?->unit }}</td>
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
