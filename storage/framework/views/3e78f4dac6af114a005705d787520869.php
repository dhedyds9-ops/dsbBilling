<div class="space-y-4">
    <?php if (isset($component)) { $__componentOriginal1a2164c88256e2df02baa87be70e8a2b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.breadcrumbs','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1a2164c88256e2df02baa87be70e8a2b)): ?>
<?php $attributes = $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b; ?>
<?php unset($__attributesOriginal1a2164c88256e2df02baa87be70e8a2b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1a2164c88256e2df02baa87be70e8a2b)): ?>
<?php $component = $__componentOriginal1a2164c88256e2df02baa87be70e8a2b; ?>
<?php unset($__componentOriginal1a2164c88256e2df02baa87be70e8a2b); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => 'Kategori Tiket','subtitle' => 'Kelola kategori tiket- poin prioritas- dan departemen penanggung jawab.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kategori Tiket','subtitle' => 'Kelola kategori tiket- poin prioritas- dan departemen penanggung jawab.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

         <?php $__env->slot('actions', null, []); ?> 
            <button wire:click="openForm" class="px-3 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 inline-flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Kategori Baru
            </button>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="p-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-sm dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-200">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if (isset($component)) { $__componentOriginaldf54224cf245156c316d9d3b07da8b50 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf54224cf245156c316d9d3b07da8b50 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <?php if (isset($component)) { $__componentOriginal793d2b22631f88b8a3d00569a12acf88 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal793d2b22631f88b8a3d00569a12acf88 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                    <tr class="text-slate-500 dark:text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Nama Kategori</th>
                        <th class="px-4 py-3 font-medium">Tingkat Prioritas (Level)</th>
                        <th class="px-4 py-3 font-medium">Poin Bobot</th>
                        <th class="px-4 py-3 font-medium">Penanggung Jawab (PIC)</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200"><?php echo e($cat['name']); ?></td>
                            <td class="px-4 py-3">
                                <?php
                                    $lvlColors = [
                                        'low' => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:bg-slate-800 dark:text-slate-300'-
                                        'medium' => 'bg-blue-100 text-primary-700 dark:bg-blue-900/50 dark:text-blue-300'-
                                        'high' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'-
                                        'critical' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'-
                                    ];
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase <?php echo e($lvlColors[$cat['level']] ?? ''); ?>">
                                    <?php echo e($cat['level']); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 font-mono text-xs font-bold border border-indigo-100 dark:border-indigo-800/50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <?php echo e($cat['poin']); ?> Poin
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold"><?php echo e(strtoupper(substr($cat['pic']- 0- 1))); ?></span>
                                    <?php echo e($cat['pic']); ?>

                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cat['active']): ?>
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-medium inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif</span>
                                <?php else: ?>
                                    <span class="text-slate-400 text-xs font-medium inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="openForm(<?php echo e($cat['id']); ?>)" class="w-7 h-7 inline-flex items-center justify-center rounded shadow-sm transition-colors bg-emerald-500 text-white hover:bg-emerald-600" title="Edit Kategori">
                                    <svg class="w-4 h-4 flex items-center justify-center rounded shadow-sm transition-colors bg-emerald-500 text-white hover:bg-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal793d2b22631f88b8a3d00569a12acf88)): ?>
<?php $attributes = $__attributesOriginal793d2b22631f88b8a3d00569a12acf88; ?>
<?php unset($__attributesOriginal793d2b22631f88b8a3d00569a12acf88); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal793d2b22631f88b8a3d00569a12acf88)): ?>
<?php $component = $__componentOriginal793d2b22631f88b8a3d00569a12acf88; ?>
<?php unset($__componentOriginal793d2b22631f88b8a3d00569a12acf88); ?>
<?php endif; ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf54224cf245156c316d9d3b07da8b50)): ?>
<?php $attributes = $__attributesOriginaldf54224cf245156c316d9d3b07da8b50; ?>
<?php unset($__attributesOriginaldf54224cf245156c316d9d3b07da8b50); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf54224cf245156c316d9d3b07da8b50)): ?>
<?php $component = $__componentOriginaldf54224cf245156c316d9d3b07da8b50; ?>
<?php unset($__componentOriginaldf54224cf245156c316d9d3b07da8b50); ?>
<?php endif; ?>

    <!-- Modal Form -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
        <div x-data="{ show: true }" x-show="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div @click.away="show = false; $wire.closeForm()" class="w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50/50 dark:bg-slate-800/50 rounded-t-xl">
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-100">Form Kategori Tiket</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Atur level- poin evaluasi- dan PIC otomatis.</p>
                    </div>
                    <button @click="show = false; $wire.closeForm()" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 p-1.5 rounded-lg hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Kategori</label>
                        <input wire:model.defer="formData.name" type="text" placeholder="Contoh: Gangguan Jaringan" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tingkat Prioritas</label>
                            <select wire:model.defer="formData.level" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                                <option value="low">Rendah (Low)</option>
                                <option value="medium">Menengah (Medium)</option>
                                <option value="high">Tinggi (High)</option>
                                <option value="critical">Kritis (Critical)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bobot Poin</label>
                            <input wire:model.defer="formData.poin" type="number" min="1" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                            <p class="text-[10px] text-slate-400 mt-1">Poin untuk performa teknisi/agen.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Penanggung Jawab Otomatis (PIC)</label>
                        <select wire:model.defer="formData.pic" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih Departemen / Tim --</option>
                            <option value="NOC Team">NOC Team</option>
                            <option value="Technician">Technician</option>
                            <option value="Finance Dept">Finance Dept</option>
                            <option value="Customer Service">Customer Service</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <input wire:model.defer="formData.active" type="checkbox" id="isActive" class="rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        <label for="isActive" class="text-sm text-slate-700 dark:text-slate-300 cursor-pointer">Status Kategori Aktif</label>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end gap-3 border-t border-slate-100 dark:border-slate-700">
                    <button @click="show = false; $wire.closeForm()" class="px-4 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">Batal</button>
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'primary','wire:click' => 'save']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','wire:click' => 'save']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Simpan Kategori <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>







<?php /**PATH D:\dsBilling\resources\views\livewire\support\ticket-category\index.blade.php ENDPATH**/ ?>