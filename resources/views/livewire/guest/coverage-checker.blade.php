<div>
    <div class="glass-card p-2 rounded-2xl flex flex-col sm:flex-row gap-2 max-w-2xl mx-auto shadow-lg border border-gray-100 dark:border-slate-700 mb-8 relative z-10" style="background: #fff;">
        <div class="flex-grow flex items-center px-4">
            <span class="material-symbols-outlined text-gray-400 mr-2">location_on</span>
            <input type="text" wire:model="searchQuery" wire:keydown.enter="checkCoverage" class="w-full bg-transparent border-none outline-none focus:ring-0 text-gray-700 dark:text-gray-300 placeholder-gray-400 py-3 dark:bg-slate-900 dark:text-slate-100" placeholder="Masukkan alamat, perumahan, desa, atau kode pos...">
        </div>
        <button wire:click="checkCoverage" wire:loading.attr="disabled" class="btn-primary-solid px-8 py-3 rounded-xl font-bold flex-shrink-0 transition-all">
            <span wire:loading.remove wire:target="checkCoverage">Cek Coverage</span>
            <span wire:loading wire:target="checkCoverage" class="material-symbols-outlined animate-spin">refresh</span>
        </button>
    </div>

    @error('searchQuery') <div class="text-red-500 text-sm text-center mb-4">{{ $message }}</div> @enderror

    <!-- Result: Available -->
    @if($status === 'available')
        <div class="max-w-2xl mx-auto bg-green-50 dark:bg-green-900/30 border border-green-200 rounded-2xl p-6 sm:p-8 text-center animate-pulse-once">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/50 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-[32px]">check_circle</span>
            </div>
            <h3 class="text-xl sm:text-xl font-bold text-green-800 mb-2">Selamat! Area Anda Tercover</h3>
            <p class="text-green-700 mb-6">Jaringan fiber optic kami sudah tersedia di sekitar <strong>{{ $searchQuery }}</strong>. Silakan pilih paket yang sesuai kebutuhan Anda.</p>
            <a href="#packages" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl transition">Lihat Paket Tersedia</a>
        </div>
    @endif

    <!-- Result: Unavailable -->
    @if($status === 'unavailable')
        <div class="max-w-2xl mx-auto bg-orange-50 border border-orange-200 rounded-2xl p-6 sm:p-8 text-center">
            <div class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-[32px]">warning</span>
            </div>
            <h3 class="text-xl sm:text-xl font-bold text-orange-800 mb-2">Maaf, Area Belum Terdeteksi</h3>
            <p class="text-orange-700 mb-6">Lokasi <strong>{{ $searchQuery }}</strong> belum terdeteksi di database jaringan kami. Tinggalkan data Anda agar kami dapat melakukan survei area.</p>
            
            <form wire:submit="submitLead" class="text-left space-y-4 max-w-md mx-auto mt-6 bg-white dark:bg-slate-800 p-6 rounded-xl border border-orange-100 shadow-sm">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                    <input type="text" wire:model="formName" required class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 dark:bg-slate-900 dark:text-slate-100">
                    @error('formName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor WhatsApp</label>
                    <input type="text" wire:model="formPhone" required class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 dark:bg-slate-900 dark:text-slate-100">
                    @error('formPhone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap</label>
                    <textarea wire:model="formAddress" required rows="3" class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 dark:bg-slate-900 dark:text-slate-100"></textarea>
                    @error('formAddress') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <button type="submit" wire:loading.attr="disabled" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 rounded-lg transition-colors flex justify-center items-center gap-2">
                    <span wire:loading.remove wire:target="submitLead">Ajukan Perluasan Area</span>
                    <span wire:loading wire:target="submitLead" class="material-symbols-outlined animate-spin text-xl">refresh</span>
                </button>
            </form>
        </div>
    @endif

    <!-- Result: Submitted -->
    @if($status === 'submitted')
        <div class="max-w-2xl mx-auto bg-blue-50 dark:bg-blue-900/30 border border-blue-200 rounded-2xl p-6 sm:p-8 text-center animate-pulse-once">
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/50 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-[32px]">mark_email_read</span>
            </div>
            <h3 class="text-xl sm:text-xl font-bold text-blue-800 mb-2">Terima Kasih!</h3>
            <p class="text-primary-700 mb-0">Permintaan cakupan jaringan untuk <strong>{{ $formName }}</strong> telah kami terima. Tim survei akan menghubungi Anda melalui WhatsApp sesegera mungkin.</p>
        </div>
    @endif
</div>






