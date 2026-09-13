<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <?php echo e(__('Template Voucher')); ?>

            </h2>
            <div class="flex space-x-2">
                <a href="<?php echo e(route('isp.voucher-templates.import')); ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Import Legacy
                </a>
                <a href="<?php echo e(route('isp.voucher-templates.create')); ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">
                    + Template Baru
                </a>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 p-4 mb-6 rounded-lg shadow flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[200px]">
                    <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['wire:model.live.debounce.300ms' => 'search','placeholder' => 'Cari template...','class' => 'w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live.debounce.300ms' => 'search','placeholder' => 'Cari template...','class' => 'w-full']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                </div>
                <div>
                    <select wire:model.live="category" class="border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Kategori</option>
                        <option value="wifi">WiFi / Hotspot</option><option value="classic">Classic</option><option value="modern">Modern</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="type" class="border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Tipe</option>
                        <option value="system">System (Bawaan)</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="status" class="border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg flex flex-col relative">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($template->is_system): ?>
                            <div class="absolute top-0 right-0 bg-gray-800 text-white text-xs px-2 py-1 rounded-bl-lg z-10">
                                System
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($template->is_default): ?>
                            <div class="absolute top-0 left-0 bg-primary-600 text-white text-xs px-2 py-1 rounded-br-lg z-10">
                                Default
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div class="p-4 bg-gray-100 dark:bg-gray-800 flex items-center justify-center min-h-[150px] relative overflow-hidden">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($template->preview_image): ?>
                                <img src="<?php echo e(Storage::url($template->preview_image)); ?>" alt="Preview" class="max-w-full max-h-full object-contain">
                            <?php else: ?>
                                <div class="text-gray-400 text-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 block">receipt_long</span>
                                    Tidak ada preview
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                <a href="<?php echo e(route('isp.voucher-templates.preview', $template->id)); ?>" class="bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200 px-4 py-2 rounded font-bold text-sm">Preview</a>
                            </div>
                        </div>

                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200 mb-1"><?php echo e($template->name); ?></h3>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <span class="capitalize"><?php echo e($template->category->value); ?></span> &bull; 
                                v<?php echo e($template->latestVersion?->version ?? 1); ?>

                            </div>
                            
                            <p class="text-sm text-gray-600 dark:text-gray-400 flex-1 line-clamp-2"><?php echo e($template->description); ?></p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <div class="flex space-x-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$template->is_system): ?>
                                        <a href="<?php echo e(route('isp.voucher-templates.edit', $template->id)); ?>" class="text-primary-600 hover:text-blue-900" title="Edit">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </a>
                                        <button wire:click="delete(<?php echo e($template->id); ?>)" wire:confirm="Yakin ingin menghapus template ini ?? " class="text-red-600 hover:text-red-900" title="Hapus">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                        <a href="<?php echo e(route('isp.voucher-templates.versions', $template->id)); ?>" class="text-gray-600 hover:text-gray-900 dark:text-gray-100" title="Riwayat Versi">
                                            <span class="material-symbols-outlined text-sm">history</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">System (Read-only)</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="flex space-x-2">
                                    <button wire:click="duplicate(<?php echo e($template->id); ?>)" class="text-gray-600 hover:text-gray-900 dark:text-gray-100" title="Duplikasi">
                                        <span class="material-symbols-outlined text-sm">content_copy</span>
                                    </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$template->is_system): ?>
                                        <button wire:click="toggleActive(<?php echo e($template->id); ?>)" class="<?php echo e($template->is_active ?'text-green-600' : 'text-gray-400'); ?>" title="Toggle Aktif">
                                            <span class="material-symbols-outlined text-sm"><?php echo e($template->is_active ?'toggle_on' : 'toggle_off'); ?></span>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$template->is_default && $template->is_active): ?>
                                        <button wire:click="setAsDefault(<?php echo e($template->id); ?>)" class="text-primary-600 hover:text-blue-900" title="Jadikan Default">
                                            <span class="material-symbols-outlined text-sm">star</span>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="col-span-full bg-white dark:bg-slate-800 p-8 text-center text-gray-500 dark:text-gray-400 rounded-lg shadow">
                        Tidak ada template yang ditemukan.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            <div class="mt-6">
                <?php echo e($templates->links()); ?>

            </div>
        </div>
    </div>
</div>









<?php /**PATH D:\dsBilling\resources\views\livewire\i-s-p\voucher-template\index.blade.php ENDPATH**/ ?>