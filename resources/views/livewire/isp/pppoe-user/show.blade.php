<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('isp.pppoe-users.index') }}" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-2xl font-bold">
                    {{ substr($pppoeUser->username, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $pppoeUser->username }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            @if($pppoeUser->status === 'active') bg-green-100 dark:bg-green-900/50 text-green-600
                            @elseif($pppoeUser->status === 'inactive') bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                            @elseif($pppoeUser->status === 'suspended') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600
                            @else bg-red-100 dark:bg-red-900/50 text-red-600
                            @endif">
                            {{ ucfirst($pppoeUser->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('isp.pppoe-users.edit', $pppoeUser->id) }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-base.card>
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Informasi PPPoE User</h3>
            </x-slot>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Username</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono">{{ $pppoeUser->username }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Service Profile</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $pppoeUser->serviceProfile?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ ucfirst($pppoeUser->status) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Dibuat Oleh</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $pppoeUser->createdBy?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Informasi Billing & Jatuh Tempo</h3>
                    <button wire:click="openEditDueDateModal" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-xs font-semibold rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined notranslate text-[16px]">edit_calendar</span>
                        Edit Jatuh Tempo
                    </button>
                </div>
            </x-slot>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Tanggal Jatuh Tempo</label>
                        <p class="text-base font-bold text-slate-900 dark:text-slate-100">
                            {{ $pppoeUser->customerService?->due_date ? \Carbon\Carbon::parse($pppoeUser->customerService->due_date)->translatedFormat('d F Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Siklus Penagihan</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ ucfirst($pppoeUser->billing_cycle ?? 'monthly') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Biaya Layanan</label>
                        <p class="text-slate-900 dark:text-slate-100">
                            {{ $pppoeUser->serviceProfile?->base_price ? 'Rp ' . number_format($pppoeUser->serviceProfile->base_price, 0, ',', '.') : 'Gratis' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Pelanggan</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $pppoeUser->real_customer?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </x-base.card>
    </div>

    <!-- Modal Edit Jatuh Tempo -->
    @if($showEditDueDateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md overflow-hidden animate-scale-up">
                <div class="p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700 mb-5">
                        <div class="flex items-center gap-3 text-indigo-600 dark:text-indigo-400">
                            <span class="material-symbols-outlined notranslate text-2xl">event_upcoming</span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Edit Tanggal Jatuh Tempo</h3>
                        </div>
                        <button wire:click="closeEditDueDateModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <span class="material-symbols-outlined notranslate">close</span>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveDueDate" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Jatuh Tempo Baru <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="edit_due_date" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                            @error('edit_due_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Alasan Perubahan</label>
                            <textarea wire:model="edit_due_date_reason" rows="2" placeholder="Contoh: Permintaan pelanggan menyesuaikan tanggal gajian" 
                                      class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                            @error('edit_due_date_reason') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-lg text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                            <span class="material-symbols-outlined notranslate text-base shrink-0 mt-0.5">info</span>
                            <span>Perubahan tanggal jatuh tempo akan memperbarui jadwal tagihan dan seluruh invoice berstatus belum dibayar untuk layanan ini. Invoice yang sudah lunas tidak akan terpengaruh.</span>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeEditDueDateModal" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg shadow-sm transition-all">
                                <span wire:loading.remove wire:target="saveDueDate">Simpan Perubahan</span>
                                <span wire:loading wire:target="saveDueDate">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
