<div class="flex items-center text-xs">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$attendance): ?>
        <button wire:click="checkIn" class="flex items-center gap-1.5 px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-full transition-colors font-semibold shadow-sm" title="Klik untuk absen masuk">
            <i class="bi bi-person-check-fill"></i>
            <span>Check-In NOC</span>
        </button>
    <?php elseif($attendance && !$attendance->checked_out_at): ?>
        <div class="flex items-center gap-2">
            <span class="text-emerald-400 font-mono font-bold tracking-wider" title="Waktu masuk: <?php echo e($attendance->checked_in_at->format('H:i')); ?>">
                <i class="bi bi-clock mr-0.5"></i> IN: <?php echo e($attendance->checked_in_at->format('H:i')); ?>

            </span>
            <button wire:click="checkOut" class="flex items-center gap-1.5 px-3 py-1 bg-orange-600 hover:bg-orange-500 text-white rounded-full transition-colors font-semibold shadow-sm" title="Klik untuk absen pulang">
                <i class="bi bi-box-arrow-right"></i>
                <span>Check-Out</span>
            </button>
        </div>
    <?php else: ?>
        <div class="flex items-center gap-2 px-3 py-1 bg-gray-800 text-gray-400 rounded-full font-mono font-semibold" title="Shift Selesai">
            <i class="bi bi-check-circle-fill text-emerald-500"></i>
            <span>Selesai (<?php echo e($attendance->checked_out_at->format('H:i')); ?>)</span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views/livewire/noc/attendance-widget.blade.php ENDPATH**/ ?>