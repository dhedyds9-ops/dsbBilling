<div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tickets</h3>
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-800">
                    <?php echo e($pendingCount); ?> Pending
                </span>
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-800">
                    <?php echo e($inProgressCount); ?> In Progress
                </span>
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/50 text-green-800">
                    <?php echo e($resolvedCount); ?> Resolved
                </span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 space-y-3">
        <!-- Search -->
        <input 
            type="text"
            wire:model.live="searchQuery"
            placeholder="Search tickets..."
            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
        >

        <div class="flex flex-wrap gap-2">
            <!-- Status Filter -->
            <select 
                wire:model.live="filterStatus"
                class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
            >
                <option value="all">All Status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>

            <!-- Priority Filter -->
            <select 
                wire:model.live="filterPriority"
                class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
            >
                <option value="all">All Priority</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>

            <!-- Type Filter -->
            <select 
                wire:model.live="filterType"
                class="text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100"
            >
                <option value="all">All Types</option>
                <option value="fault">Fault</option>
                <option value="maintenance">Maintenance</option>
                <option value="installation">Installation</option>
                <option value="service_request">Service Request</option>
            </select>
        </div>

        <!-- Show Assigned Only -->
        <label class="flex items-center space-x-2 cursor-pointer">
            <input 
                type="checkbox" 
                wire:model.live="showAssignedOnly"
                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100"
            >
            <span class="text-sm text-gray-600 dark:text-gray-400">Show assigned only</span>
        </label>
    </div>

    <!-- Ticket List -->
    <div class="max-h-96 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div 
            wire:click="$emit('showTicketDetails', '<?php echo e($ticket['id'] ?? ''); ?>')"
            class="px-4 py-3 hover:bg-gray-50 dark:bg-gray-900/50 cursor-pointer transition-colors"
        >
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3">
                    <div class="mt-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($ticket['priority'] ?? 'medium'):
                            case ('critical'): ?>
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                                <?php break; ?>
                            <?php case ('high'): ?>
                                <span class="h-3 w-3 rounded-full bg-red-500"></span>
                                <?php break; ?>
                            <?php case ('medium'): ?>
                                <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                                <?php break; ?>
                            <?php default: ?>
                                <span class="h-3 w-3 rounded-full bg-green-500"></span>
                        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($ticket['title'] ?? 'Unknown Ticket'); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo e($ticket['id'] ?? ''); ?> - <?php echo e($ticket['customer_name'] ?? ''); ?></p>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="px-2 py-0.5 text-xs font-medium rounded 
                                <?php switch($ticket['status'] ?? 'open'):
                                    case ('open'): ?>
                                        bg-amber-100 dark:bg-amber-900/50 text-amber-800
                                        <?php break; ?>
                                    <?php case ('in_progress'): ?>
                                        bg-blue-100 dark:bg-blue-900/50 text-blue-800
                                        <?php break; ?>
                                    <?php case ('resolved'): ?>
                                        bg-green-100 dark:bg-green-900/50 text-green-800
                                        <?php break; ?>
                                    <?php default: ?>
                                        bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200
                                <?php endswitch; ?>
                            ">
                                <?php echo e(str_replace('_', ' ', ucfirst($ticket['status'] ?? 'open'))); ?>

                            </span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                <?php echo e(ucfirst($ticket['type'] ?? 'fault')); ?>

                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($ticket['sla_deadline'])): ?>
                        <p class="text-xs text-gray-400">SLA</p>
                        <p class="text-xs font-medium <?php echo e(($ticket['sla_breached'] ?? false) ? 'text-red-600' : 'text-gray-600 dark:text-gray-400'); ?>">
                            <?php echo e($ticket['sla_deadline'] ?? ''); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- Assignee -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($ticket['assignee'])): ?>
            <div class="mt-2 flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                <img class="w-4 h-4 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($ticket['assignee'])); ?>&size=16" alt="">
                <span><?php echo e($ticket['assignee']); ?></span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No tickets found</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Create Ticket Button -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        <button 
            wire:click="$emit('createNewTicket')"
            class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 flex items-center justify-center space-x-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Create New Ticket</span>
        </button>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\gis\components\ticket-panel.blade.php ENDPATH**/ ?>