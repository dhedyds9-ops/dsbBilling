                <!-- Editor -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Source Code (HTML/CSS)</h3>
                        <button wire:click="validateContent" class="text-sm bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded hover:bg-slate-200">
                            Validasi Syntax
                        </button>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($validationErrors)): ?>
                        <div class="mb-4 bg-red-50 dark:bg-red-900/30 p-4 rounded-md">
                            <h4 class="text-red-800 font-bold text-sm mb-2">Error Ditemukan:</h4>
                            <ul class="list-disc pl-5 text-sm text-red-700">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $validationErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <li><?php echo e($error); ?></li>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="border rounded-md border-slate-300 dark:border-slate-600">
                        
                        <div wire:ignore class="w-full h-[500px] border-0">
                            <div id="monaco-container" class="w-full h-full"></div>
                        </div>
                        <input type="hidden" wire:model.defer="template_code" id="hidden_template_code">







<?php /**PATH D:\dsBilling\resources\views/livewire/isp/voucher-template/editor.blade.php ENDPATH**/ ?>