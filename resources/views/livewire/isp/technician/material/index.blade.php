@section('header_title', 'Stok Saya (Inventory)')

<div class="space-y-4 p-4 pb-24">
    @if(session('success'))
        <div class="p-3.5 bg-emerald-500 text-white rounded-2xl text-xs font-semibold flex items-center gap-2.5 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3.5 bg-rose-500 text-white rounded-2xl text-xs font-semibold flex items-center gap-2.5 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined text-sm">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-2xl flex items-center shadow-inner">
        <button wire:click="switchTab('stok')" type="button"
            class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'stok' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">inventory_2</span>
            <span>Stok Saya</span>
        </button>
        <button wire:click="switchTab('pemakaian')" type="button"
            class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5
                   {{ $activeTab === 'pemakaian' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">history</span>
            <span>Riwayat Pemakaian</span>
        </button>
    </div>

    {{-- Action Bar & Search --}}
    <div class="flex items-center gap-2">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:18px">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kabel, ONT, konektor..."
                   class="w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm">
        </div>
        <button wire:click="openRequestModal" type="button"
                class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors shrink-0">
            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
            <span>Minta Barang</span>
        </button>
    </div>

    {{-- TAB 1: STOK SAYA --}}
    @if($activeTab === 'stok')
        <div class="space-y-3">
            @forelse($materials as $item)
                @php
                    $stock = (int) ($item->stock ?? 0);
                    $min = (int) ($item->min_stock ?? 5);
                    $isLow = $stock > 0 && $stock <= $min;
                    $isOut = $stock <= 0;

                    $catIcon = match(strtolower($item->category ?? '')) {
                        'kabel' => 'cable',
                        'konektor' => 'settings_ethernet',
                        'perangkat aktif' => 'router',
                        default => 'category',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm flex items-center justify-between gap-3 hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl {{ $isOut ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/40' : ($isLow ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/40' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40') }} flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:22px">{{ $catIcon }}</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug truncate">
                                {{ $item->name }}
                            </h4>
                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-[10px] font-mono">{{ $item->sku ?? 'MAT' }}</span>
                                <span>•</span>
                                <span>{{ $item->category ?? 'Material' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        <div class="text-right">
                            <span class="text-base sm:text-lg font-black {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-900 dark:text-slate-100') }}">
                                {{ number_format($stock, 0, ',', '.') }}
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $item->unit ?? 'pcs' }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if($isOut)
                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">Habis</span>
                            @elseif($isLow)
                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">Menipis</span>
                            @else
                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">Tersedia</span>
                            @endif

                            <button wire:click="openRecordUsageModal({{ $item->id }})" type="button"
                                    class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-lg text-[10px] font-bold border border-indigo-200 dark:border-indigo-800 transition-colors">
                                Pakai
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">inventory_2</span>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Tidak ada material yang cocok dengan pencarian.</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- TAB 2: RIWAYAT PEMAKAIAN SAYA --}}
    @if($activeTab === 'pemakaian')
        <div class="space-y-3">
            @forelse($myUsages as $usage)
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4 shadow-sm space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-indigo-500">task_alt</span>
                            <span>{{ $usage->material_name }}</span>
                        </span>
                        <span class="px-2 py-0.5 text-[11px] font-black rounded-lg bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                            -{{ $usage->quantity }} {{ $usage->unit }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-700">
                        <span>{{ \Carbon\Carbon::parse($usage->used_date ?? $usage->created_at)->translatedFormat('d M Y, H:i') }}</span>
                        <span class="font-medium italic truncate max-w-[180px]">{{ $usage->notes ?: 'Pemakaian lapangan' }}</span>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">history_edu</span>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum ada riwayat pemakaian material.</p>
                </div>
            @endforelse

            @if(method_exists($myUsages, 'links'))
                <div class="pt-2">
                    {{ $myUsages->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- MODAL CATAT PEMAKAIAN MATERIAL --}}
    @if($showUsageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">build</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Catat Pemakaian Material</h3>
                    </div>
                    <button wire:click="$set('showUsageModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 block text-[10px] uppercase font-bold">Barang:</span>
                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $usageMaterialName }}</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah Terpakai ({{ $usageUnit }})</label>
                        <input type="number" wire:model="usageQuantity" min="1"
                               class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-black text-center focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Hubungkan ke Tiket / Pekerjaan (Opsional)</label>
                        <select wire:model="usageTicketId" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            <option value="">-- Pekerjaan Umum / Mandiri --</option>
                            @foreach($activeTickets as $t)
                                <option value="{{ $t->id }}">#{{ $t->id }} - {{ $t->title }} ({{ $t->customer?->name ?? 'Pelanggan' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Catatan Penggunaan</label>
                        <textarea wire:model="usageNotes" rows="2" placeholder="Contoh: Penarikan kabel PSB rumah Bpk. Santoso (85m)..."
                                  class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"></textarea>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button wire:click="$set('showUsageModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button wire:click="saveUsage" type="button"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors">
                        Simpan & Potong Stok
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL PERMINTAAN BARANG TAMBAHAN --}}
    @if($showRequestModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 max-w-sm w-full space-y-4 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Minta Barang Tambahan</h3>
                    </div>
                    <button wire:click="$set('showRequestModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Nama Barang / Material</label>
                        <input type="text" wire:model="reqMaterialName" placeholder="Contoh: Fast Connector SC/UPC / Dropcore"
                               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        @error('reqMaterialName') <span class="text-rose-500 text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Jumlah</label>
                            <input type="number" wire:model="reqQuantity" min="1"
                                   class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-center bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Satuan</label>
                            <select wire:model="reqUnit" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                                <option value="pcs">pcs</option>
                                <option value="meter">meter</option>
                                <option value="unit">unit</option>
                                <option value="roll">roll</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Alasan / Catatan Permintaan</label>
                        <textarea wire:model="reqReason" rows="2" placeholder="Contoh: Stok fast connector habis di lapangan, butuh 20 pcs..."
                                  class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"></textarea>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button wire:click="$set('showRequestModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button wire:click="submitMaterialRequest" type="button"
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors">
                        Kirim Permintaan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
