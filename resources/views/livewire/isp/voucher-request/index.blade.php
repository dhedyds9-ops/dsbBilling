<div>
    @section('page_title')
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400">
                <span class="material-symbols-outlined notranslate" translate="no">local_printshop</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Permintaan Cetak Voucher</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola dan proses pengajuan cetak voucher fisik & digital dari reseller mitra</p>
            </div>
        </div>
    @endsection

    <div class="space-y-4">
        {{-- Flash Alert --}}
        @if (session()->has('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-emerald-600" translate="no">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-rose-600" translate="no">error</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
            </div>
        @endif

        {{-- KPI STATS CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Total --}}
            <div wire:click="setTab('all')" class="relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm bg-white dark:bg-slate-800 p-4 cursor-pointer hover:shadow transition-all {{ $activeTab === 'all' ? 'ring-2 ring-indigo-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Request</span>
                        <div class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ number_format($stats['total'] ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500">
                        <span class="material-symbols-outlined notranslate" translate="no">receipt_long</span>
                    </div>
                </div>
            </div>

            {{-- Pending --}}
            <div wire:click="setTab('pending')" class="relative overflow-hidden rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-sm bg-gradient-to-br from-amber-50/50 to-white dark:from-amber-950/30 dark:to-slate-800 p-4 cursor-pointer hover:shadow transition-all {{ $activeTab === 'pending' ? 'ring-2 ring-amber-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Menunggu Review</span>
                        <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-1">{{ number_format($stats['pending'] ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <span class="material-symbols-outlined notranslate" translate="no">pending</span>
                    </div>
                </div>
            </div>

            {{-- Printing --}}
            <div wire:click="setTab('printing')" class="relative overflow-hidden rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-sm bg-gradient-to-br from-indigo-50/50 to-white dark:from-indigo-950/30 dark:to-slate-800 p-4 cursor-pointer hover:shadow transition-all {{ $activeTab === 'printing' ? 'ring-2 ring-indigo-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Sedang Dicetak</span>
                        <div class="text-2xl font-black text-indigo-700 dark:text-indigo-300 mt-1">{{ number_format($stats['printing'] ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <span class="material-symbols-outlined notranslate" translate="no">print</span>
                    </div>
                </div>
            </div>

            {{-- Ready --}}
            <div wire:click="setTab('ready_for_pickup')" class="relative overflow-hidden rounded-xl border border-cyan-200 dark:border-cyan-800/60 shadow-sm bg-gradient-to-br from-cyan-50/50 to-white dark:from-cyan-950/30 dark:to-slate-800 p-4 cursor-pointer hover:shadow transition-all {{ $activeTab === 'ready_for_pickup' ? 'ring-2 ring-cyan-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">Siap Ambil/Kirim</span>
                        <div class="text-2xl font-black text-cyan-700 dark:text-cyan-300 mt-1">{{ number_format($stats['ready'] ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-cyan-100 dark:bg-cyan-900/50 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                        <span class="material-symbols-outlined notranslate" translate="no">inventory</span>
                    </div>
                </div>
            </div>

            {{-- Completed --}}
            <div wire:click="setTab('completed')" class="relative overflow-hidden rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50/50 to-white dark:from-emerald-950/30 dark:to-slate-800 p-4 cursor-pointer hover:shadow transition-all {{ $activeTab === 'completed' ? 'ring-2 ring-emerald-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Selesai</span>
                        <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">{{ number_format($stats['completed'] ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <span class="material-symbols-outlined notranslate" translate="no">verified</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER & SEARCH TOOLBAR --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                <button wire:click="setTab('all')" class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $activeTab === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Semua
                </button>
                <button wire:click="setTab('pending')" class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $activeTab === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Perlu Diproses ({{ $stats['pending'] ?? 0 }})
                </button>
                <button wire:click="setTab('printing')" class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $activeTab === 'printing' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Proses Cetak
                </button>
                <button wire:click="setTab('ready_for_pickup')" class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $activeTab === 'ready_for_pickup' ? 'bg-cyan-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Siap Ambil
                </button>
                <button wire:click="setTab('completed')" class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $activeTab === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Selesai
                </button>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 18px">search</span>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No. Request / Reseller..." class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <select wire:model.live="perPage" class="py-1.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-300">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">No. Permintaan</th>
                            <th class="py-3 px-4">Reseller Mitra</th>
                            <th class="py-3 px-4">Paket Voucher</th>
                            <th class="py-3 px-4 text-center">Jumlah</th>
                            <th class="py-3 px-4">Total Modal</th>
                            <th class="py-3 px-4">Tipe & Pembayaran</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-slate-700 dark:text-slate-300">
                        @forelse($requests as $req)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $req->request_no }}
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $req->created_at?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ $req->reseller?->name ?? 'Reseller #'.$req->reseller_id }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $req->branch?->name ?? 'Pusat' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $req->serviceProfile?->name ?? 'Paket' }}</span>
                                    <div class="text-[10px] text-slate-400">Jual: Rp {{ number_format($req->unit_price) }} | Modal: Rp {{ number_format($req->cost_price) }}</div>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-900 dark:text-slate-100">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                        {{ number_format($req->quantity) }} Pcs
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($req->total_amount) }}
                                    <div class="text-[10px] text-slate-400 font-normal">Est. Margin: Rp {{ number_format($req->estimated_profit) }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1 font-medium">
                                        @if($req->delivery_type === 'physical_print')
                                            <span class="material-symbols-outlined text-xs text-indigo-500 notranslate" translate="no">print</span>
                                            <span>Cetak Fisik</span>
                                        @else
                                            <span class="material-symbols-outlined text-xs text-cyan-500 notranslate" translate="no">key</span>
                                            <span>Softcopy Digital</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">Metode: {{ $req->payment_method }} ({{ $req->payment_status }})</div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $color = $req->status_color;
                                        $label = $req->status_label;
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                                        @if($color === 'amber') bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300
                                        @elseif($color === 'blue') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                        @elseif($color === 'indigo') bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300
                                        @elseif($color === 'cyan') bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300
                                        @elseif($color === 'emerald') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                                        @elseif($color === 'rose') bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300
                                        @else bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300
                                        @endif">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($req->status === 'pending')
                                            <button wire:click="openApproveModal({{ $req->id }})" class="inline-flex items-center px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                                                <span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size:14px">check</span>
                                                Setujui & Buat
                                            </button>
                                            <button wire:click="openRejectModal({{ $req->id }})" class="inline-flex items-center px-2 py-1 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 rounded-lg text-xs font-medium transition-colors" title="Tolak Permintaan">
                                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">close</span>
                                            </button>
                                        @elseif(in_array($req->status, ['printing', 'ready_for_pickup', 'completed']))
                                            @if(!empty($req->generated_voucher_ids))
                                                <button wire:click="printRequestVouchers({{ $req->id }})" class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium transition-colors" title="Cetak Voucher Ulang/Batch">
                                                    <span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size:14px">print</span>
                                                    Cetak
                                                </button>
                                            @endif
                                            @if($req->status === 'printing')
                                                <button wire:click="markAsReady({{ $req->id }})" class="inline-flex items-center px-2.5 py-1 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                                                    <span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size:14px">inventory</span>
                                                    Siap Ambil
                                                </button>
                                            @elseif($req->status === 'ready_for_pickup')
                                                <button wire:click="markAsCompleted({{ $req->id }})" class="inline-flex items-center px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                                                    <span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size:14px">done_all</span>
                                                    Selesaikan
                                                </button>
                                            @endif
                                        @elseif($req->status === 'rejected')
                                            <span class="text-[11px] text-rose-500 italic" title="{{ $req->rejection_reason }}">Ditolak: {{ Str::limit($req->rejection_reason, 20) }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-4xl block mb-2" translate="no">inbox</span>
                                    Belum ada permintaan voucher pada kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="p-3 border-t border-slate-200 dark:border-slate-700">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL APPROVE & GENERATE --}}
    @if($showApproveModal && $selectedRequest)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-600" translate="no">verified</span>
                        <h3 class="font-bold text-slate-900 dark:text-slate-100">Review & Setujui Permintaan Voucher</h3>
                    </div>
                    <button wire:click="closeApproveModal" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    {{-- Summary Card --}}
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400">No. Permintaan:</span>
                            <div class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedRequest->request_no }}</div>
                        </div>
                        <div>
                            <span class="text-slate-400">Reseller:</span>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $selectedRequest->reseller?->name }}</div>
                        </div>
                        <div>
                            <span class="text-slate-400">Paket:</span>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $selectedRequest->serviceProfile?->name }}</div>
                        </div>
                        <div>
                            <span class="text-slate-400">Jumlah Cetak:</span>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ number_format($selectedRequest->quantity) }} Lembar/Pcs</div>
                        </div>
                        <div>
                            <span class="text-slate-400">Total Modal (Wajib Bayar):</span>
                            <div class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($selectedRequest->total_amount) }}</div>
                        </div>
                        <div>
                            <span class="text-slate-400">Tipe Pengiriman:</span>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $selectedRequest->delivery_type_label }}</div>
                        </div>
                    </div>

                    {{-- Generation Parameters --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Konfigurasi Batch Voucher MikroTik</h4>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Router / NAS Device</label>
                            <select wire:model="approve_nas_device_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                                <option value="">Semua Router / Hotspot Server</option>
                                @foreach($nasDevices as $nas)
                                    <option value="{{ $nas->id }}">{{ $nas->name }} ({{ $nas->ip_address }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Prefix Kode</label>
                                <input type="text" wire:model="approve_prefix" placeholder="Misal: VC, MSTORE" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Panjang Karakter</label>
                                <input type="number" wire:model="approve_length" min="4" max="16" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Metode Login</label>
                                <select wire:model="approve_login_method" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                                    <option value="voucher_code">Kode Voucher Saja</option>
                                    <option value="username_password">Username & Password Sama</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kombinasi Karakter</label>
                                <select wire:model="approve_code_combination" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                                    <option value="uppercase_alphanumeric">Huruf Besar & Angka (Acak)</option>
                                    <option value="numbers">Angka Saja (PIN)</option>
                                    <option value="lowercase">Huruf Kecil</option>
                                    <option value="uppercase">Huruf Besar Saja</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Template Desain Cetak</label>
                            <select wire:model="approve_voucher_template_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                                <option value="">-- Pilih Template Cetak --</option>
                                @foreach($voucherTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }} {{ $tpl->is_default ? '(Default)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <button wire:click="closeApproveModal" class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                        Batal
                    </button>
                    <button wire:click="approveRequest" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                        <span wire:loading.remove wire:target="approveRequest" class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:16px">check</span>
                        <span wire:loading wire:target="approveRequest" class="material-symbols-outlined notranslate mr-1.5 animate-spin" translate="no" style="font-size:16px">sync</span>
                        <span>Setujui & Buat Voucher Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL REJECT --}}
    @if($showRejectModal && $selectedRequest)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100">Tolak Permintaan Voucher</h3>
                    <button wire:click="closeRejectModal" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div class="p-6 space-y-3">
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Apakah Anda yakin ingin menolak permintaan <strong>#{{ $selectedRequest->request_no }}</strong> dari reseller <strong>{{ $selectedRequest->reseller?->name }}</strong>?
                    </p>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alasan Penolakan</label>
                        <textarea wire:model="reject_reason" rows="3" placeholder="Misal: Stok kertas habis / saldo deposit tidak mencukupi / paket sedang nonaktif" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"></textarea>
                        @error('reject_reason') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <button wire:click="closeRejectModal" class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">Batal</button>
                    <button wire:click="rejectRequest" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm">Konfirmasi Tolak</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Hidden form for printing vouchers --}}
    <form id="adminPrintVoucherForm" method="POST" action="{{ route('isp.vouchers.print') }}" target="_blank" class="hidden">
        @csrf
        <input type="hidden" name="ids_string" id="adminPrintIdsString">
        <input type="hidden" name="template_id" id="adminPrintTemplateId">
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-print-dialog', (data) => {
            const ids = data.ids || (data[0] ? data[0].ids : '');
            const templateId = data.template_id || (data[0] ? data[0].template_id : '');

            document.getElementById('adminPrintIdsString').value = ids;
            document.getElementById('adminPrintTemplateId').value = templateId || '';
            document.getElementById('adminPrintVoucherForm').submit();
        });
    });
</script>
@endpush
