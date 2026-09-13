@section('page_title')
    <div class="flex items-center gap-2">
        <a href="{{ route('crm.leads.index') }}" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
        </a>
        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
        </div>
        <span class="text-lg">Tambah Lead Baru</span>
    </div>
@endsection

<div class="max-w-4xl mx-auto space-y-6 pb-10">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Informasi Prospek</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Masukkan detail kontak dan informasi awal lead.</p>
        </div>
        
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap *</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: Budi Santoso">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Telepon / WhatsApp</label>
                    <input type="text" wire:model="phone" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: 08123456789">
                    @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100" placeholder="budi@example.com">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Awal</label>
                    <select wire:model="status" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="new">Baru</option>
                        <option value="contacted">Sudah Dihubungi</option>
                        <option value="qualified">Qualified</option>
                        <option value="converted">Dikonversi</option>
                        <option value="lost">Hilang (Lost)</option>
                    </select>
                    @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Lengkap</label>
                <textarea wire:model="address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100" placeholder="Alamat pemasangan atau domisili..."></textarea>
                @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan (Notes)</label>
                <textarea wire:model="notes" rows="4" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100" placeholder="Detail hasil percakapan, minat paket, dll..."></textarea>
                @error('notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('crm.leads.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                    Simpan Lead
                </button>
            </div>
        </form>
    </div>
</div>
