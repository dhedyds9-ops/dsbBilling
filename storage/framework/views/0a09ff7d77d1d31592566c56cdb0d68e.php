

<footer class="py-6 px-6 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
    <div class="max-w-screen-2xl mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500 dark:text-slate-400">
            
            <!-- Left Side -->
            <div class="flex items-center gap-3">
                <span>&copy; <?php echo e(date('Y')); ?> dsBilling</span>
                <span class="hidden md:inline text-slate-300 dark:text-slate-700">•</span>
                <span class="font-mono text-xs">v<?php echo e(config('app.version', '1.0.0')); ?></span>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->environment('local')): ?>
                    <span class="px-2 py-0.5 text-[10px] font-medium rounded bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300">
                        LOCAL
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Right Side -->
            <div class="flex items-center gap-6">
                <a href="<?php echo e(url('/docs')); ?>" 
                   class="hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                    Dokumentasi
                </a>
                <a href="<?php echo e(url('/support')); ?>" 
                   class="hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                    Support
                </a>
                <a href="<?php echo e(url('/terms')); ?>" 
                   class="hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                    Terms
                </a>
                <a href="<?php echo e(url('/privacy')); ?>" 
                   class="hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                    Privacy
                </a>
            </div>

            <!-- Extra Info -->
            <div class="text-xs text-slate-400 dark:text-slate-500 md:text-right">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <span><?php echo e(auth()->user()->name); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\admin\footer.blade.php ENDPATH**/ ?>