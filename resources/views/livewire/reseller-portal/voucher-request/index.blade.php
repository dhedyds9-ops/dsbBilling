<div>
    @section('page_title')
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <span class="material-symbols-outlined notranslate" translate="no">local_printshop</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Permintaan Cetak Voucher</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Ajukan pencetakan batch voucher fisik atau pembuatan voucher digital ke admin pusat</p>
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
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Diajukan</span>
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

            {{-- Printing / In Progress --}}
            <div wire:click="setTab('in_progress')" class="relative overflow-hidden rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-sm bg-gradient-to-br from-indigo-50/50 to-white dark:from-indigo-950/30 dark:to-slate-800 p-4 cursor-pointer hover:shadow transition-all {{ $activeTab === 'in_progress' ? 'ring-2 ring-indigo-500' : '' }}">
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
            <div class="relative overflow-hidden rounded-xl border border-cyan-200 dark:border-cyan-800/60 shadow-sm bg-gradient-to-br from-cyan-50/50 to-white dark:from-cyan-950/30 dark:to-slate-800 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">Siap Ambil</span>
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
                        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Selesai / Diterima</span>
                        <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">{{ number_format($stats['completed'] ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <span class="material-symbols-outlined notranslate" translate="no">verified</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button wire:click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors whitespace-nowrap">
                    <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">add_circle</span>
                    Minta Cetak Voucher Baru
                </button>
                <a href="{{ route('reseller-portal.sales.voucher') }}" class="inline-flex items-center px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-lg text-xs font-medium transition-colors whitespace-nowrap">
                    <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">confirmation_number</span>
                    Voucher Saya
                </a>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-60">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 18px">search</span>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No. Request..." class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <select wire:model.live="perPage" class="py-1.5 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-300">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
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
                            <th class="py-3 px-4">Paket Voucher</th>
                            <th class="py-3 px-4 text-center">Jumlah</th>
                            <th class="py-3 px-4">Harga & Margin</th>
                            <th class="py-3 px-4">Total Modal</th>
                            <th class="py-3 px-4">Tipe Pengiriman</th>
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
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ $req->serviceProfile?->name ?? 'Paket' }}</div>
                                    @if($req->notes)
                                        <div class="text-[10px] text-slate-400 italic">"{{ Str::limit($req->notes, 30) }}"</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-900 dark:text-slate-100">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                        {{ number_format($req->quantity) }} Pcs
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-slate-800 dark:text-slate-200">Jual: Rp {{ number_format($req->unit_price) }}</div>
                                    <div class="text-[10px] text-slate-400">Modal: Rp {{ number_format($req->cost_price) }}</div>
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
                                    <div class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">{{ $req->payment_method }} ({{ $req->payment_status }})</div>
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
                                    @if($req->status === 'rejected' && $req->rejection_reason)
                                        <div class="text-[10px] text-rose-500 mt-1 italic">{{ $req->rejection_reason }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($req->status === 'pending')
                                            <button wire:click="cancelRequest({{ $req->id }})" wire:confirm="Yakin ingin membatalkan permintaan ini?" class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-rose-600 dark:text-rose-400 rounded-lg text-xs font-medium transition-colors">
                                                <span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size:14px">close</span>
                                                Batalkan
                                            </button>
                                        @elseif(!empty($req->generated_voucher_ids))
                                            <button wire:click="printVouchers({{ $req->id }})" class="inline-flex items-center px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-semibold transition-colors">
                                                <span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size:14px">print</span>
                                                Cetak Mandiri
                                            </button>
                                        @else
                                            <span class="text-[11px] text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-4xl block mb-2" translate="no">receipt_long</span>
                                    Belum ada permintaan voucher yang diajukan.
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

    {{-- MODAL BUAT PERMINTAAN VOUCHER --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-600" translate="no">add_circle</span>
                        <h3 class="font-bold text-slate-900 dark:text-slate-100">Buat Permintaan Voucher ke Admin</h3>
                    </div>
                    <button wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    {{-- Paket Selection --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Paket Hotspot / Voucher *</label>
                        <select wire:model.live="service_profile_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="">-- Pilih Paket Layanan --</option>
                            @foreach($packages as $pkg)
                                @php
                                    $pkgRetail = $pkg->promo_price ?: ($pkg->base_price ?: 0);
                                    $pkgCost = $pkg->reseller_price ?: ($pkg->cost_price ?: 0);
                                @endphp
                                <option value="{{ $pkg->id }}">
                                    {{ $pkg->name }} (Jual: Rp {{ number_format($pkgRetail) }} | Modal: Rp {{ number_format($pkgCost) }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_profile_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    {{-- Quantity & Presets --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Voucher (Pcs / Lembar) *</label>
                        <div class="flex items-center gap-1.5 mb-2">
                            @foreach([50, 100, 200, 500] as $preset)
                                <button type="button" wire:click="setQuantityPreset({{ $preset }})" class="flex-1 py-1 px-2 rounded-lg text-xs font-semibold border transition-all {{ $quantity === $preset ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:bg-slate-100' }}">
                                    {{ $preset }} Pcs
                                </button>
                            @endforeach
                        </div>
                        <input type="number" wire:model.live.debounce.300ms="quantity" min="1" max="5000" placeholder="Ketik jumlah kustom..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                        @error('quantity') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    {{-- Live Calculation Box --}}
                    <div class="p-3.5 rounded-xl bg-gradient-to-br from-indigo-50/70 to-slate-50 dark:from-indigo-950/40 dark:to-slate-800 border border-indigo-100 dark:border-indigo-900/50 space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Harga Jual Satuan ke Pelanggan:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($calc_unit_price) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Harga Modal Satuan Reseller:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($calc_cost_price) }}</span>
                        </div>
                        <div class="h-px bg-slate-200 dark:bg-slate-700 my-1"></div>
                        <div class="flex justify-between text-sm font-bold">
                            <span class="text-indigo-900 dark:text-indigo-200">Total Modal (Wajib Bayar):</span>
                            <span class="text-indigo-700 dark:text-indigo-300">Rp {{ number_format($calc_total_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                            <span>Estimasi Keuntungan Anda:</span>
                            <span>+ Rp {{ number_format($calc_estimated_profit) }}</span>
                        </div>
                    </div>

                    {{-- Delivery Type & Payment Method --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Penyerahan</label>
                            <select wire:model="delivery_type" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                                <option value="physical_print">Cetak Fisik (Kertas/Karton)</option>
                                <option value="digital">Digital (Kode Softcopy)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Metode Pembayaran</label>
                            <select wire:model="payment_method" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                                <option value="deposit">Potong Saldo Deposit</option>
                                <option value="cash">Tunai saat Ambil Voucher</option>
                                <option value="tempo">Tempo / Konsinyasi</option>
                            </select>
                        </div>
                    </div>

                    {{-- Voucher Template Selection (for physical print) --}}
                    @if($delivery_type === 'physical_print')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilihan Desain Template (Opsional)</label>
                            <select wire:model="voucher_template_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                                <option value="">Template Standar Kantor / Default</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }} {{ $tpl->is_default ? '(Default)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea wire:model="notes" rows="2" placeholder="Misal: Mohon dipotong rapi / perlu dikirim ke pos reseller / minta prefix MSTORE" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <button wire:click="closeCreateModal" class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                        Batal
                    </button>
                    <button wire:click="submitRequest" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                        <span wire:loading.remove wire:target="submitRequest" class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:16px">send</span>
                        <span wire:loading wire:target="submitRequest" class="material-symbols-outlined notranslate mr-1.5 animate-spin" translate="no" style="font-size:16px">sync</span>
                        <span>Kirim Permintaan ke Admin</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Hidden form for printing vouchers --}}
    <form id="resellerPrintVoucherForm" method="POST" action="{{ route('reseller-portal.sales.voucher.print') }}" target="_blank" class="hidden">
        @csrf
        <input type="hidden" name="ids_string" id="resellerPrintIdsString">
        <input type="hidden" name="template_id" id="resellerPrintTemplateId">
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-print-dialog', (data) => {
            const ids = data.ids || (data[0] ? data[0].ids : '');
            const templateId = data.template_id || (data[0] ? data[0].template_id : '');

            document.getElementById('resellerPrintIdsString').value = ids;
            document.getElementById('resellerPrintTemplateId').value = templateId || '';
            document.getElementById('resellerPrintVoucherForm').submit();
        });
    });
</script>
@endpush
