<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] relative pb-24">
    <div class="mb-5">
        <h1 class="text-xl font-bold mb-1 text-slate-800 dark:text-slate-200">Kredensial Hotspot</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Ubah username dan password login Hotspot Anda.</p>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message): ?>
    <div class="mb-5 p-4 rounded-xl flex items-start gap-3 <?php echo e($messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400' : 'bg-red-50 text-red-700 border border-red-100 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400'); ?>">
        <span class="material-symbols-outlined shrink-0"><?php echo e($messageType === 'success' ? 'check_circle' : 'error'); ?></span>
        <div class="text-sm"><?php echo e($message); ?></div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hotspotUsers->isEmpty()): ?>
        <div class="text-center text-slate-500 dark:text-slate-400 py-10">Anda tidak memiliki layanan Hotspot aktif.</div>
    <?php else: ?>
        <form wire:submit="saveCredentials" class="space-y-5">
            
            <div>
                <label for="hotspotUserId" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih Layanan</label>
                <div class="relative">
                    <select id="hotspotUserId" wire:model.live="hotspotUserId" class="w-full pl-4 pr-10 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 appearance-none dark:bg-slate-900 dark:text-slate-100" required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hotspotUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($user->id); ?>">
                                <?php echo e($user->username); ?> (<?php echo e($user->customerService?->serviceProfile?->name ?? 'Hotspot'); ?>)
                            </option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-3 text-slate-400 pointer-events-none">expand_more</span>
                </div>
            </div>

            
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tipe Autentikasi</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition-all <?php echo e($authType === 'up' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-600' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800'); ?>">
                        <input type="radio" wire:model.live="authType" value="up" class="sr-only dark:bg-slate-900 dark:text-slate-100">
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-0.5">User & Pass</span>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Username & Password dibedakan</span>
                    </label>
                    <label class="relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition-all <?php echo e($authType === 'vc' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-600' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800'); ?>">
                        <input type="radio" wire:model.live="authType" value="vc" class="sr-only dark:bg-slate-900 dark:text-slate-100">
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-0.5">Voucher Mode</span>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Username = Password</span>
                    </label>
                </div>
            </div>

            
            <div>
                <label for="newUsername" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Username Baru</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400">person</span>
                    <input type="text" id="newUsername" wire:model.live.debounce.300ms="newUsername" class="w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-slate-900 dark:text-slate-100" minlength="3">
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newUsername'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($authType === 'up'): ?>
            <div x-data="{ show: false }">
                <label for="newPassword" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password Baru</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400">lock</span>
                    <input :type="show ? 'text' : 'password'" id="newPassword" wire:model="newPassword" class="w-full pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-slate-900 dark:text-slate-100" minlength="3">
                    <button type="button" @click="show = !show" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div x-data="{ show: false }">
                <label for="confirmPassword" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Konfirmasi Password Baru</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-3 text-slate-400">lock_check</span>
                    <input :type="show ? 'text' : 'password'" id="confirmPassword" wire:model="confirmPassword" class="w-full pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-slate-900 dark:text-slate-100" minlength="3">
                    <button type="button" @click="show = !show" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['confirmPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button type="submit" wire:loading.attr="disabled"
                class="w-full mt-4 py-3.5 bg-orange-600 text-white font-bold rounded-xl hover:bg-orange-700 transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50">
                <span wire:loading.remove>Simpan Kredensial</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\customer-portal\self-service\change-hotspot-credentials.blade.php ENDPATH**/ ?>