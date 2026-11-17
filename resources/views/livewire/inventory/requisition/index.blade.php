<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-3xl">storefront</span>
                <span>Permintaan & Katalog Barang Gudang</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Katalog ketersediaan material gudang ala marketplace untuk pengambilan barang ready atau permohonan pengadaan (restock).
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="exportExcel" type="button"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 disabled:opacity-50 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs transition-all cursor-pointer"
                    title="Download Data Permintaan Barang ke Format Excel (.xlsx)">
                <span wire:loading.remove wire:target="exportExcel" class="material-symbols-outlined text-sm text-emerald-600 dark:text-emerald-400">download</span>
                <span wire:loading wire:target="exportExcel" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                <span>Export Excel</span>
            </button>

            @if($canCreateRequisition)
                <button wire:click="openCreateModal" type="button"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all">
                    <span class="material-symbols-outlined text-sm">add</span>
                    <span>Buat Pengajuan Baru</span>
                </button>
            @endif
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

    {{-- Tab Navigation Bar (Marketplace vs Riwayat) --}}
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700/80 pb-2">
        <button wire:click="setTab('list')" type="button"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'list' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <span class="material-symbols-outlined text-base">format_list_bulleted</span>
            <span>Riwayat Pengajuan</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'list' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $requisitions->total() }}
            </span>
        </button>

        <button wire:click="setTab('catalog')" type="button"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'catalog' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
            <span class="material-symbols-outlined text-base">storefront</span>
            <span>Katalog Stok Gudang (Marketplace)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'catalog' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                {{ $catalogItems->count() }}
            </span>
        </button>
    </div>

    {{-- TAB 1: RIWAYAT PENGAJUAN (TABLE VIEW) --}}
    @if($activeTab === 'list')
        {{-- Filter Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-4 shadow-xs">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                <div class="relative flex-1 min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Cari no pengajuan, pelanggan, catatan..."
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="min-w-[190px] flex-1 sm:flex-initial">
                        <select wire:model.live="typeFilter"
                                class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 cursor-pointer font-medium">
                            <option value="">Semua Jenis Pengajuan</option>
                            <option value="dispatch_request">Ambil Barang Ready</option>
                            <option value="procurement_request">Permohonan Pengadaan (Restock)</option>
                        </select>
                    </div>

                    <div class="min-w-[190px] flex-1 sm:flex-initial">
                        <select wire:model.live="warehouseFilter"
                                class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                            <option value="">Semua Gudang Asal</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-[180px] flex-1 sm:flex-initial">
                        <select wire:model.live="statusFilter"
                                class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu Persetujuan</option>
                            <option value="approved">Telah Disetujui</option>
                            <option value="dispatched">Telah Dikeluarkan</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>

                    <div class="min-w-[120px] flex-1 sm:flex-initial">
                        <select wire:model.live="perPage"
                                class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-slate-100 cursor-pointer font-medium">
                            <option value="20">20 / Hal</option>
                            <option value="50">50 / Hal</option>
                            <option value="100">100 / Hal</option>
                            <option value="all">Semua</option>
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
                            <th class="py-3 px-4">No. Pengajuan</th>
                            <th class="py-3 px-4">Pemohon</th>
                            <th class="py-3 px-4">Metode Distribusi</th>
                            <th class="py-3 px-4">Pelanggan & Peruntukan</th>
                            <th class="py-3 px-4">Teknisi Ditunjuk</th>
                            <th class="py-3 px-4">Rincian Barang</th>
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
                                    @if($req->isProcurementRequest())
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800 mt-1">
                                            <span class="material-symbols-outlined text-[11px]">shopping_bag</span>
                                            <span>Minta Pengadaan</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800 mt-1">
                                            <span class="material-symbols-outlined text-[11px]">local_shipping</span>
                                            <span>Ambil Ready</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                    <div class="font-semibold">{{ $req->requester?->name ?? '-' }}</div>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                        {{ $req->requester_type === 'reseller' ? 'Reseller' : 'Teknisi' }}
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
                                <td class="py-3.5 px-4">
                                    <div class="space-y-1">
                                        @forelse($req->items->take(2) as $ri)
                                            <div class="flex items-center gap-1.5 text-xs text-slate-800 dark:text-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                                <span class="font-bold truncate max-w-[200px]">{{ $ri->item?->name ?? 'Item #' . $ri->item_id }}</span>
                                                <span class="font-mono text-[11px] text-emerald-700 dark:text-emerald-400 font-bold shrink-0">
                                                    {{ (float)$ri->quantity_requested }} {{ $ri->item?->unit ?? 'unit' }}
                                                </span>
                                            </div>
                                        @empty
                                            <span class="text-xs text-slate-400 italic">Tidak ada item</span>
                                        @endforelse
                                        @if($req->items->count() > 2)
                                            <span class="inline-block text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">
                                                +{{ $req->items->count() - 2 }} barang lainnya
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badge = match($req->status) {
                                            'pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800',
                                            'approved' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800',
                                            'dispatched' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800',
                                            'received' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-400 dark:border-teal-800',
                                            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200'
                                        };
                                        $lbl = match($req->status) {
                                            'pending' => 'Menunggu Approval',
                                            'approved' => 'Disetujui',
                                            'dispatched' => 'Telah Dikeluarkan',
                                            'received' => 'Diterima Pemohon',
                                            'rejected' => 'Ditolak',
                                            default => ucfirst($req->status)
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

                                        {{-- Tombol Approval untuk Petugas Gudang / Manager --}}
                                        @if($canManageWarehouse && !$isReseller)
                                            @if($req->status === 'pending' && $req->requester_id !== auth()->id())
                                                <button wire:click="openApprovalModal({{ $req->id }})"
                                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1 shadow-xs">
                                                    <span class="material-symbols-outlined text-xs">done</span>
                                                    <span>Proses</span>
                                                </button>
                                            @endif

                                            @if($req->status === 'approved')
                                                @if($req->isProcurementRequest())
                                                    <a href="{{ route('inventory.inbounds.index') }}"
                                                       class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1 shadow-xs"
                                                       title="Tindak lanjuti pembelian barang / Inbound dari vendor">
                                                        <span class="material-symbols-outlined text-xs">add_shopping_cart</span>
                                                        <span>Inbound</span>
                                                    </a>
                                                @else
                                                    <a href="{{ route('inventory.dispatches.index', ['requisition_id' => $req->id]) }}"
                                                       class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1 shadow-xs"
                                                       title="Keluarkan fisik barang dari gudang">
                                                        <span class="material-symbols-outlined text-xs">local_shipping</span>
                                                        <span>Dispatch</span>
                                                    </a>
                                                    <button wire:click="openLinkDispatchModal({{ $req->id }})"
                                                            type="button"
                                                            class="px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg transition-colors flex items-center gap-1 border border-slate-200 dark:border-slate-700 cursor-pointer"
                                                            title="Tautkan jika barang sudah dikeluarkan secara manual di menu Barang Keluar">
                                                        <span class="material-symbols-outlined text-xs">link</span>
                                                        <span>Tautkan</span>
                                                    </button>
                                                @endif
                                            @endif
                                        @endif

                                        {{-- Tombol Konfirmasi Terima untuk Reseller / Teknisi --}}
                                        @if(($isReseller || $isTechnician) && $req->status === 'dispatched' && ($req->requester_id === auth()->id() || $req->designated_technician_id === auth()->id()))
                                            <button wire:click="confirmReceipt({{ $req->id }})"
                                                    class="px-2.5 py-1 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1 shadow-xs animate-pulse"
                                                    title="Konfirmasi bahwa fisik barang telah Anda terima dari Gudang">
                                                <span class="material-symbols-outlined text-xs">inventory_2</span>
                                                <span>Terima</span>
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
                                        <p class="text-sm font-medium">Belum ada data pengajuan barang.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $requisitions->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $requisitions->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-slate-700 dark:text-slate-200">{{ $requisitions->total() }}</span> data
                </div>
                <div>
                    {{ $requisitions->links() }}
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 2: KATALOG STOK GUDANG (MARKETPLACE GRID VIEW) --}}
    @if($activeTab === 'catalog')
        <div class="space-y-4">
            {{-- Search & Filter Toolbar --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-4 shadow-xs">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    <div class="relative flex-1 min-w-[260px]">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                        <input wire:model.live.debounce.300ms="catalogSearch" type="text"
                               placeholder="Cari barang di katalog (nama, SKU, merk, model)..."
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 placeholder:text-slate-400">
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="min-w-[190px]">
                            <select wire:model.live="catalogCategory"
                                    class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-slate-200 cursor-pointer font-medium">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="min-w-[200px]">
                            <select wire:model.live="warehouse_id"
                                    class="w-full py-2.5 px-3.5 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 text-blue-900 dark:text-blue-200 cursor-pointer font-bold">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">Stok: {{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Marketplace Grid Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse($catalogItems as $item)
                    @php
                        $stk = $catalogStockMap[$item->id] ?? 0.0;
                        $isReady = $stk > 0;
                    @endphp
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border transition-all duration-200 flex flex-col justify-between p-4 shadow-xs {{ $isReady ? 'border-slate-200 dark:border-slate-700/80 hover:border-emerald-400 hover:shadow-md' : 'border-rose-200/80 dark:border-rose-900/40 bg-rose-50/10' }}">
                        <div class="space-y-3">
                            {{-- Top Badges: Category & Stock Status --}}
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 truncate max-w-[120px]">
                                    {{ $item->category ?? 'General' }}
                                </span>
                                @if($isReady)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Ready: {{ number_format($stk, 0) }} {{ $item->unit }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-extrabold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        <span>Stok Habis (0 {{ $item->unit }})</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Item Info --}}
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 line-clamp-2 leading-snug">
                                    {{ $item->name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                    <span class="font-mono">SKU: {{ $item->sku }}</span>
                                    @if($item->is_serialized)
                                        <span>•</span>
                                        <span class="text-indigo-600 dark:text-indigo-400 font-semibold flex items-center gap-0.5">
                                            <span class="material-symbols-outlined text-xs">qr_code</span>
                                            Serial
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                            @if($canCreateRequisition)
                                @if($isReady)
                                    <button wire:click="openCreateForStock({{ $item->id }})" type="button"
                                            class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm">shopping_cart</span>
                                        <span>Ajukan Pengambilan</span>
                                    </button>
                                @else
                                    <button wire:click="openCreateForStock({{ $item->id }})" type="button"
                                            class="w-full py-2 px-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm">notification_add</span>
                                        <span>Minta Pengadaan (Restock)</span>
                                    </button>
                                @endif
                            @else
                                <div class="text-[11px] text-slate-400 text-center italic">
                                    Admin Gudang Mode
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400">
                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">search_off</span>
                        <p class="text-sm font-medium mt-2">Tidak ada barang yang cocok dengan pencarian katalog.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- MODAL BUAT PENGAJUAN BARANG (PENGAMBILAN ATAU PENGADAAN) --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-2xl w-full p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $requisition_type === 'procurement_request' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600' }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl notranslate" translate="no">
                                {{ $requisition_type === 'procurement_request' ? 'add_shopping_cart' : 'assignment_turned_in' }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                                {{ $requisition_type === 'procurement_request' ? 'Permohonan Pengadaan Barang Baru (Restock)' : 'Form Pengambilan Barang Ready Gudang' }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $requisition_type === 'procurement_request' ? 'Ajukan permintaan agar admin gudang membelanjakan stok ke vendor.' : 'Ajukan pengambilan material fisik yang sudah tersedia di gudang.' }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="$set('showCreateModal', false)" type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        <span class="material-symbols-outlined text-lg notranslate" translate="no">close</span>
                    </button>
                </div>

                {{-- In-Modal Alert Banner --}}
                @if(session('error'))
                    <div class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 rounded-2xl text-xs sm:text-sm font-semibold text-rose-800 dark:text-rose-200 flex items-start gap-2.5 animate-fadeIn">
                        <span class="material-symbols-outlined text-rose-600 dark:text-rose-400 shrink-0 text-xl">error</span>
                        <div class="flex-1">
                            <span class="font-bold block">Gagal Memproses Permintaan:</span>
                            <span class="text-xs font-normal leading-relaxed text-rose-700 dark:text-rose-300">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <form wire:submit="saveRequisition" class="space-y-4">
                    {{-- Pilihan Jenis Pengajuan: Ready vs Restock --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tujuan & Jenis Pengajuan *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <label class="cursor-pointer p-3 rounded-xl border-2 transition-all flex items-start gap-2.5 {{ $requisition_type === 'dispatch_request' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30' }}">
                                <input type="radio" wire:model.live="requisition_type" value="dispatch_request" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm text-emerald-600">inventory_2</span>
                                        <span>1. Ambil Barang Ready</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Ambil barang yang stok fisiknya sudah ada di gudang.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer p-3 rounded-xl border-2 transition-all flex items-start gap-2.5 {{ $requisition_type === 'procurement_request' ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/30' : 'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30' }}">
                                <input type="radio" wire:model.live="requisition_type" value="procurement_request" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm text-amber-600">add_shopping_cart</span>
                                        <span>2. Permohonan Pengadaan (Restock)</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Minta pengadaan baru karena stok gudang kosong (0 unit).</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Urgency & Distribution Method --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tingkat Urgensi *</label>
                            <select wire:model.live="urgency" required
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                <option value="normal">Normal (Sesuai Jadwal Rutin)</option>
                                <option value="urgent">Mendesak / Urgent (Prioritas Tinggi)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Metode Distribusi *</label>
                            <select wire:model.live="distribution_method" required
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                <option value="pickup_reseller">Metode A: Reseller Ambil ke Gudang</option>
                                <option value="direct_technician">Metode B: Gudang ke Teknisi Langsung</option>
                            </select>
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

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Gudang Sumber *</label>
                            <select wire:model.live="warehouse_id" required
                                    class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 font-bold">
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
                            <input wire:model="notes" type="text" placeholder="misal: Mohon segera diadakan untuk 10 PSB minggu ini..."
                                   class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    {{-- Items Input Section --}}
                    <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-700/80">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600 text-base notranslate" translate="no">inventory</span>
                                <span>Daftar Barang yang Diajukan</span>
                            </h4>
                            <button wire:click="addItemRow" type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 rounded-xl border border-emerald-200 dark:border-emerald-800 transition-colors">
                                <span class="material-symbols-outlined text-sm notranslate" translate="no">add</span>
                                <span>Tambah Baris</span>
                            </button>
                        </div>

                        @error('requisitionItems')
                            <div class="p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 rounded-xl text-xs text-rose-700 dark:text-rose-300 font-medium">
                                {{ $message }}
                            </div>
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
                                            <x-inventory.item-searchable-select
                                                :availableItems="$availableItems"
                                                :selectedId="$row['item_id'] ?? null"
                                                wireModel="requisitionItems.{{ $idx }}.item_id"
                                                placeholder="-- Cari / Pilih Barang dari Katalog --"
                                            />
                                            @error("requisitionItems.{$idx}.item_id") <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror

                                            @if(!empty($row['item_id']) && $warehouse_id)
                                                @php $stockQty = $warehouseStocks[$row['item_id']] ?? 0; @endphp
                                                @if($stockQty <= 0)
                                                    <div class="text-[11px] text-amber-700 dark:text-amber-400 mt-1.5 p-2 rounded-lg bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 flex items-center justify-between gap-2">
                                                        <span class="flex items-center gap-1 font-semibold">
                                                            <span class="material-symbols-outlined text-xs">warning</span>
                                                            <span>Stok fisik gudang kosong (0 {{ $row['unit'] ?? 'unit' }}).</span>
                                                        </span>
                                                        @if($requisition_type !== 'procurement_request')
                                                            <button type="button" wire:click="$set('requisition_type', 'procurement_request')" class="text-[10px] font-bold text-amber-900 dark:text-amber-300 underline hover:text-amber-700">
                                                                Alihkan ke Pengadaan
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="sm:col-span-3">
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300">Jumlah *</label>
                                                @if(!empty($row['item_id']) && $warehouse_id)
                                                    @php $currentStock = $warehouseStocks[$row['item_id']] ?? 0; @endphp
                                                    <span class="text-[10px] font-bold {{ $currentStock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                                        Sisa: {{ $currentStock }}
                                                    </span>
                                                @endif
                                            </div>
                                            <input type="number" step="0.01" min="0.01" wire:model="requisitionItems.{{ $idx }}.quantity" required
                                                   class="w-full py-2 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200 font-mono font-bold">
                                            @error("requisitionItems.{$idx}.quantity") <span class="text-xs text-rose-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan Baris</label>
                                            <input type="text" wire:model="requisitionItems.{{ $idx }}.notes" placeholder="e.g. SN tertentu / dropcore"
                                                   class="w-full py-2 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800 dark:text-slate-200">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/80">
                        <button wire:click="$set('showCreateModal', false)" type="button"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl {{ $requisition_type === 'procurement_request' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm notranslate" translate="no">send</span>
                            <span>{{ $requisition_type === 'procurement_request' ? 'Kirim Permohonan Pengadaan' : 'Kirim Permintaan Pengambilan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL APPROVAL / PROSES GUDANG --}}
    @if($showApprovalModal && $selectedRequisition)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <span class="material-symbols-outlined">rule</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Persetujuan Pengajuan</h3>
                            <p class="text-xs text-slate-500 font-mono">{{ $selectedRequisition->requisition_number }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showApprovalModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700/80 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Jenis Pengajuan:</span>
                        <span class="font-bold {{ $selectedRequisition->isProcurementRequest() ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $selectedRequisition->isProcurementRequest() ? 'Permohonan Pengadaan Barang Baru (Restock)' : 'Pengambilan Barang Fisik Ready' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Pemohon:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedRequisition->requester?->name }} ({{ $selectedRequisition->requester_type === 'reseller' ? 'Reseller' : 'Teknisi' }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Gudang Sumber:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedRequisition->warehouse?->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Peruntukan:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedRequisition->customer?->name ?? 'Stok Cadangan / Operasional' }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span class="font-bold text-slate-600 dark:text-slate-300 block mb-1">Daftar Barang yang Diminta:</span>
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
                        Tolak Pengajuan
                    </button>
                    <div class="flex items-center gap-2">
                        <button wire:click="approveRequisition" type="button"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-bold transition-all cursor-pointer">
                            Setujui Saja
                        </button>
                        @if(!$selectedRequisition->isProcurementRequest())
                            <button wire:click="approveAndDispatch" type="button"
                                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">local_shipping</span>
                                <span>Setujui & Buka Dispatch</span>
                            </button>
                        @else
                            <button wire:click="approveRequisition" type="button"
                                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                <span>Setujui Pengadaan</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL TAUTKAN PENGELUARAN YANG SUDAH ADA (LINK EXISTING DISPATCH) --}}
    @if($showLinkDispatchModal && $linkingRequisition)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <span class="material-symbols-outlined">link</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Tautkan Surat Jalan / Dispatch</h3>
                            <p class="text-xs text-slate-500 font-mono">{{ $linkingRequisition->requisition_number }} &bull; {{ $linkingRequisition->requester?->name }}</p>
                        </div>
                    </div>
                    <button wire:click="closeLinkDispatchModal" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300">
                    💡 Gunakan fitur ini jika barang untuk pengajuan ini <strong>sudah terlanjur dikeluarkan manual</strong> dari menu Barang Keluar tanpa memilih nomor pengajuan.
                    Sistem akan menautkan Surat Jalan tersebut ke pengajuan ini tanpa memotong stok ulang.
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Pilih Surat Jalan / Dokumen Pengeluaran yang Cocok <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model="selectedExistingDispatchId"
                                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Surat Jalan (Dispatch) --</option>
                            @foreach($availableDispatchesToLink as $dsp)
                                <option value="{{ $dsp->id }}">
                                    {{ $dsp->dispatch_number }} &bull; {{ $dsp->recipient?->name }} ({{ $dsp->recipient_type }}) &bull; {{ $dsp->dispatched_at?->format('d/m/Y H:i') }} &bull; {{ $dsp->items->map(fn($it) => $it->item?->name . ' (' . (float)$it->quantity . ' ' . $it->item?->unit . ')')->implode(', ') }}
                                </option>
                            @endforeach
                        </select>
                        @error('selectedExistingDispatchId') <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-700/80">
                    <button wire:click="closeLinkDispatchModal" type="button"
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                        Batal
                    </button>
                    <button wire:click="saveLinkDispatch" type="button"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">link</span>
                        <span>Tautkan & Tandai Dispatched</span>
                    </button>
                </div>
            </div>
        </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETAIL PENGAJUAN --}}
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
                            <p class="text-xs text-slate-500">
                                {{ $selectedRequisition->isProcurementRequest() ? 'Permohonan Pengadaan Barang Baru (Restock)' : 'Pengambilan Barang Fisik Ready' }}
                            </p>
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
                        <span class="text-slate-400 block text-[11px]">Jenis</span>
                        <span class="font-bold {{ $selectedRequisition->isProcurementRequest() ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $selectedRequisition->isProcurementRequest() ? 'Pengadaan (Restock)' : 'Ambil Ready' }}
                        </span>
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
