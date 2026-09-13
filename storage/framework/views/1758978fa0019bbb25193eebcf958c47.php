<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Top Up Saldo</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ajukan penambahan saldo dan pantau status riwayat top up Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="$set('showModal', true)" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size: 18px">add_circle</span>
                Ajukan Top Up
            </button>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
        <div class="flex-1 w-full relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor referensi..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <select wire:model.live="status" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu Konfirmasi</option>
                <option value="approved">Berhasil (Approved)</option>
                <option value="rejected">Ditolak</option>
            </select>
            <select wire:model.live="perPage" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 text-left font-semibold">Tanggal & Waktu</th>
                        <th class="px-4 py-3 text-left font-semibold">No Referensi</th>
                        <th class="px-4 py-3 text-left font-semibold">Nominal</th>
                        <th class="px-4 py-3 text-left font-semibold">Metode</th>
                        <th class="px-4 py-3 text-left font-semibold">Catatan</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $topups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                <?php echo e($row->created_at->format('d M Y')); ?><br>
                                <span class="text-xs text-slate-400"><?php echo e($row->created_at->format('H:i')); ?></span>
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-medium">
                                <?php echo e($row->reference_number); ?>

                            </td>
                            <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-200">
                                Rp <?php echo e(number_format($row->amount, 0, ',', '.')); ?>

                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 uppercase">
                                <?php echo e(str_replace('_', ' ', $row->method)); ?>

                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs truncate max-w-xs">
                                <?php echo e($row->gateway_transaction_id ?: '-'); ?>

                            </td>
                            <td class="px-4 py-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->status === 'approved'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        Approved
                                    </span>
                                <?php elseif($row->status === 'rejected'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800/50">
                                        Ditolak
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                        Pending
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50" translate="no">receipt_long</span>
                                    <p>Belum ada riwayat top up saldo.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($topups->hasPages()): ?>
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <?php echo e($topups->links(data: ['scrollTo' => false])); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Modal Ajukan Top Up -->
    <div x-data="{ show: <?php if ((object) ('showModal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showModal'->value()); ?>')<?php echo e('showModal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showModal'); ?>')<?php endif; ?> }" x-show="show" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="show" x-transition.scale.origin.bottom sm.origin.center class="relative transform overflow-x-auto rounded-xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="submit">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100" id="modal-title">Ajukan Top Up Saldo</h3>
                            <button type="button" x-on:click="show = false" class="text-slate-400 hover:text-slate-500 dark:text-slate-400 dark:hover:text-slate-300">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 24px">close</span>
                            </button>
                        </div>
                        <div class="px-6 py-6 space-y-4">
                            <!-- Nominal -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nominal Top Up <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 dark:text-slate-400 font-medium">Rp</span>
                                    <input type="number" wire:model="amount" class="w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors dark:bg-slate-900 dark:text-slate-100" placeholder="100000" min="10000">
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Metode Pembayaran -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                                <select wire:model="method" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                    <option value="bank_transfer">Transfer Bank</option>
                                    <option value="cash">Tunai / Cash (Ke Kantor)</option>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan (Opsional)</label>
                                <textarea wire:model="notes" rows="2" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100" placeholder="Misal: Sudah transfer via BCA a/n Budi"></textarea>
                            </div>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg flex gap-3 text-sm text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                                <span class="material-symbols-outlined notranslate" translate="no">info</span>
                                <div>Setelah mengajukan, mohon hubungi Admin via WhatsApp dan sertakan bukti transfer (jika via bank) agar saldo dapat segera disetujui.</div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                            <button type="button" x-on:click="show = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 font-medium transition-colors">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 18px">send</span>
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal:success', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'success',
                    title: info.title,
                    text: info.text,
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                    confirmButtonColor: '#4f46e5'
                });
            });
            Livewire.on('swal:error', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'error',
                    title: info.title,
                    text: info.text,
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    </script>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\reseller-portal\finance\topup.blade.php ENDPATH**/ ?>