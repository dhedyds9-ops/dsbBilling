<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Ganti Password PPPoE</h1>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message): ?>
            <div class="mb-4 <?php echo e($messageType === 'success' ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e($message); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form wire:submit="savePassword">
            <div class="mb-4">
                <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                <input type="password" id="newPassword" wire:model="newPassword" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
            </div>
            <div class="mb-4">
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                <input type="password" id="confirmPassword" wire:model="confirmPassword" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
            </div>
            <div class="flex items-center justify-end">
                <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>

<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\customer-portal\self-service\change-pppoe-password.blade.php ENDPATH**/ ?>