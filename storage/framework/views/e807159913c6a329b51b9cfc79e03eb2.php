<div class="space-y-6">
    
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pengaturan</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola pengaturan aplikasi dsBilling ERP Anda.</p>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Umum</h3>
                <p class="text-sm text-slate-500 mt-1">Pengaturan umum aplikasi</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Nama Aplikasi</label>
                    <input type="text" value="<?php echo e(config('app.name')); ?>" readonly class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">URL Aplikasi</label>
                    <input type="text" value="<?php echo e(config('app.url')); ?>" readonly class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Zona Waktu</label>
                    <input type="text" value="<?php echo e(config('app.timezone')); ?>" readonly class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Informasi Perusahaan</h3>
                <p class="text-sm text-slate-500 mt-1">Detail informasi perusahaan Anda</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Nama Perusahaan</label>
                    <input type="text" placeholder="Masukkan nama perusahaan" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" placeholder="email@perusahaan.com" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Telepon</label>
                    <input type="text" placeholder="021-xxx-xxxx" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea rows="3" placeholder="Masukkan alamat perusahaan" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
            </div>
            <div class="p-6 border-t border-slate-200">
                <button class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\admin\settings\index.blade.php ENDPATH**/ ?>