<div class="p-4 bg-slate-50 dark:bg-slate-900/30 min-h-screen">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600" translate="no">receipt_long</span>
                Pengeluaran Reseller
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola dan catat semua biaya operasional, perlengkapan jaringan, dan pengeluaran harian Anda.</p>
        </div>
        <button wire:click="openCreateModal" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shadow-sm hover:shadow-md transition-all">
            <span class="material-symbols-outlined notranslate text-lg" translate="no">add_circle</span>
            Tambah Pengeluaran
        </button>
    </div>

    <!-- Flash Alert -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between text-emerald-800 dark:text-emerald-300 text-sm">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined notranslate text-xl" translate="no">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-200">
                <span class="material-symbols-outlined notranslate text-base" translate="no">close</span>
            </button>
        </div>
    @endif

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Bulan Ini -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pengeluaran Bulan Ini</p>
                    <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-2">
                        Rp {{ number_format($monthTotal, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ now()->translatedFormat('F Y') }}</p>
                </div>
                <div class="p-3 rounded-xl bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">trending_down</span>
                </div>
            </div>
        </div>

        <!-- Hari Ini -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pengeluaran Hari Ini</p>
                    <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">
                        Rp {{ number_format($todayTotal, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ today()->format('d M Y') }}</p>
                </div>
                <div class="p-3 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">calendar_today</span>
                </div>
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Catatan</p>
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-2">
                        {{ number_format($totalTransactions, 0, ',', '.') }}
                        <span class="text-sm font-medium text-slate-400">item</span>
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Sepanjang waktu</p>
                </div>
                <div class="p-3 rounded-xl bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">receipt_long</span>
                </div>
            </div>
        </div>

        <!-- Pending Approval -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Menunggu Approval</p>
                    <p class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-2">
                        {{ number_format($pendingApproval, 0, ',', '.') }}
                        <span class="text-sm font-medium text-slate-400">item</span>
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Perlu konfirmasi</p>
                </div>
                <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">pending</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar & Filter -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate text-lg" translate="no">search</span>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari keterangan, kode..." 
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none">
            </div>

            <!-- Category -->
            <div>
                <select wire:model.live="filterCategory" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $key => $name)
                        <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <select wire:model.live="filterStatus" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="approved">Disetujui / Selesai</option>
                    <option value="pending_approval">Menunggu Approval</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="flex items-center gap-1.5">
                <input wire:model.live="startDate" type="date" class="w-1/2 px-2 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none" title="Dari Tanggal">
                <span class="text-slate-400 text-xs">-</span>
                <input wire:model.live="endDate" type="date" class="w-1/2 px-2 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none" title="Sampai Tanggal">
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Tanggal & Kode</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Keterangan</th>
                        <th class="px-4 py-3.5">Bukti / Nota</th>
                        <th class="px-4 py-3.5 text-right">Nominal</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($expenses as $item)
                        @php
                            $catBadge = match($item->category) {
                                'operasional' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
                                'perawatan' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                                'pegawai' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                                'isp_tools' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'sewa' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
                                default => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300'
                            };

                            $statusBadge = match($item->status) {
                                'approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'pending_approval' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                                'rejected' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'
                            };

                            $statusLabel = match($item->status) {
                                'approved' => 'Disetujui',
                                'pending_approval' => 'Menunggu',
                                'rejected' => 'Ditolak',
                                default => 'Draft'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <p class="text-xs font-semibold text-slate-900 dark:text-white">
                                    {{ $item->expense_date ? $item->expense_date->format('d M Y') : '-' }}
                                </p>
                                <p class="text-[11px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">
                                    {{ $item->code ?? '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $catBadge }}">
                                    {{ $categories[$item->category] ?? ucfirst($item->category ?? 'Lain') }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 max-w-xs">
                                <p class="text-xs text-slate-800 dark:text-slate-200 line-clamp-2">
                                    {{ $item->description }}
                                </p>
                                @if($item->reject_reason)
                                    <p class="text-[10px] text-rose-500 mt-0.5">Alasan tolak: {{ $item->reject_reason }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($item->attachment_file)
                                    <a href="{{ Storage::disk('public')->url($item->attachment_file) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:underline">
                                        <span class="material-symbols-outlined notranslate text-sm" translate="no">attach_file</span>
                                        Lihat Nota
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-bold text-sm text-slate-900 dark:text-white">
                                Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="openEditModal({{ $item->id }})" type="button" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate text-lg" translate="no">edit</span>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" type="button" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate text-lg" translate="no">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-400 mb-3">
                                        <span class="material-symbols-outlined notranslate text-2xl" translate="no">receipt_long</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Belum ada catatan pengeluaran</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Gunakan tombol "Tambah Pengeluaran" di atas untuk mencatat biaya operasional pertama Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div wire:click="closeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700">
                <form wire:submit.prevent="save">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-primary-600" translate="no">
                                {{ $isEditing ? 'edit_note' : 'add_circle' }}
                            </span>
                            {{ $isEditing ? 'Edit Pengeluaran' : 'Catat Pengeluaran Baru' }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <span class="material-symbols-outlined notranslate text-xl" translate="no">close</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-4 space-y-4">
                        <!-- Tanggal & Kategori -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Tanggal <span class="text-rose-500">*</span>
                                </label>
                                <input wire:model="expense_date" type="date" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none @error('expense_date') border-rose-500 @enderror">
                                @error('expense_date') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Kategori <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="category" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none @error('category') border-rose-500 @enderror">
                                    @foreach($categories as $key => $name)
                                        <option value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('category') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Nominal -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Nominal Pengeluaran (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-xs text-slate-400">Rp</span>
                                <input wire:model="amount" type="number" step="any" min="0" placeholder="Contoh: 150000"
                                       class="w-full pl-9 pr-3 py-2 text-sm font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none @error('amount') border-rose-500 @enderror">
                            </div>
                            @error('amount') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Keterangan / Uraian Biaya <span class="text-rose-500">*</span>
                            </label>
                            <textarea wire:model="description" rows="3" placeholder="Contoh: Pembelian kabel dropcore 1 roll 1000m untuk pelanggan RT 04"
                                      class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none @error('description') border-rose-500 @enderror"></textarea>
                            @error('description') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Status Pencatatan <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="status" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500 focus:outline-none @error('status') border-rose-500 @enderror">
                                <option value="approved">Selesai / Sudah Terbayar (Approved)</option>
                                <option value="pending_approval">Menunggu Persetujuan Admin (Pending)</option>
                                <option value="draft">Draft (Belum Final)</option>
                            </select>
                            @error('status') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Upload Struk / Bukti Nota -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Bukti / Struk / Nota (Opsional)
                            </label>
                            <div class="mt-1 flex justify-center px-4 pt-4 pb-4 border-2 border-slate-200 dark:border-slate-700 border-dashed rounded-xl bg-slate-50/50 dark:bg-slate-900/30">
                                <div class="space-y-1 text-center">
                                    <span class="material-symbols-outlined notranslate text-3xl text-slate-400" translate="no">upload_file</span>
                                    <div class="flex text-xs text-slate-600 dark:text-slate-400 justify-center">
                                        <label for="attachment-upload" class="relative cursor-pointer font-semibold text-primary-600 hover:text-primary-500 focus-within:outline-none">
                                            <span>Pilih file nota</span>
                                            <input id="attachment-upload" wire:model="attachment" type="file" class="sr-only" accept="image/*,application/pdf">
                                        </label>
                                    </div>
                                    <p class="text-[10px] text-slate-400">PNG, JPG, PDF max 3MB</p>
                                </div>
                            </div>

                            <div wire:loading wire:target="attachment" class="text-xs text-primary-600 mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined notranslate text-sm animate-spin" translate="no">progress_activity</span>
                                Mengupload file...
                            </div>

                            @if ($attachment)
                                <div class="mt-2 p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-xs flex items-center justify-between">
                                    <span class="truncate">File dipilih: {{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="$set('attachment', null)" class="text-rose-500 hover:text-rose-700">
                                        <span class="material-symbols-outlined notranslate text-sm" translate="no">close</span>
                                    </button>
                                </div>
                            @elseif ($existingAttachment)
                                <div class="mt-2 p-2 rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 text-xs flex items-center justify-between">
                                    <span class="truncate">Lampiran tersimpan</span>
                                    <a href="{{ Storage::disk('public')->url($existingAttachment) }}" target="_blank" class="text-primary-600 hover:underline">Lihat</a>
                                </div>
                            @endif
                            @error('attachment') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-700/40 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-xs font-semibold text-white shadow-sm transition-colors flex items-center gap-1.5">
                            <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate text-base" translate="no">save</span>
                            <span wire:loading wire:target="save" class="material-symbols-outlined notranslate text-base animate-spin" translate="no">progress_activity</span>
                            {{ $isEditing ? 'Perbarui Pengeluaran' : 'Simpan Pengeluaran' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div wire:click="cancelDelete" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-700 p-4">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined notranslate text-xl" translate="no">warning</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Hapus Data Pengeluaran?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            Apakah Anda yakin ingin menghapus catatan pengeluaran ini? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" wire:click="cancelDelete" class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteExpense" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-xs font-semibold text-white shadow-sm transition-colors">
                        Hapus Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
