<?php
/** @var \App\Livewire\Support\Ticket\Index $this */
/** @var mixed $rows */
$summaryItems = $this->getSummaryItems();
$toolbarActions = $this->getToolbarActions();
$bulkActions = $this->getBulkActions();
$filterConfig = $this->getFilterConfig();
$statusBadge = [
    'open' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border-blue-100 dark:border-blue-800',
    'in_progress' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border-amber-100 dark:border-amber-800',
    'pending_customer' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border-purple-100 dark:border-purple-800',
    'resolved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-100 dark:border-emerald-800',
    'closed' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600',
];
$priorityBadge = [
    'low' => 'bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    'medium' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    'critical' => 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300',
];
$statusLabels = [
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'pending_customer' => 'Pending Customer',
    'resolved' => 'Resolved',
    'closed' => 'Closed',
];
?>
<div class="flex flex-col h-full min-h-0 bg-slate-50 dark:bg-slate-900">

    <?php echo $__env->make('partials.enterprise.list-toolbar', [
        'title' => 'Support > Tiket Support',
        'primaryAction' => null,
        'actions' => $toolbarActions,
        'searchPlaceholder' => 'Cari judul, customer, ticket ID...',
        'showFiltersToggle' => true,
        'tabs' => $this->tabs,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('partials.enterprise.summary-cards', ['items' => $summaryItems], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showFilters): ?>
        <?php echo $__env->make('partials.enterprise.filters', ['filters' => $filterConfig], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.enterprise.bulk-bar', ['bulkActions' => $bulkActions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->errorMessage): ?>
        <div class="px-3 py-2 bg-red-50 border-b border-red-100 dark:bg-red-900/30 dark:border-red-800 text-red-700 dark:text-red-200 text-sm"><?php echo e($this->errorMessage); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->loading): ?>
        <div class="flex-1 flex items-center justify-center py-16">
            <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Memuat data...
            </div>
        </div>
    <?php elseif($this->activeTab === 'kanban'): ?>
        <div class="flex-1 overflow-auto min-h-0 p-3">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 min-w-max">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['open', 'in_progress', 'pending_customer', 'resolved', 'closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $cards = $this->kanbanData[$status] ?? [];
                        $count = is_countable($cards) ? count($cards) : 0;
                        $colColor = match($status) {
                            'open' => 'border-blue-200 dark:border-blue-800',
                            'in_progress' => 'border-amber-200 dark:border-amber-800',
                            'pending_customer' => 'border-purple-200 dark:border-purple-800',
                            'resolved' => 'border-emerald-200 dark:border-emerald-800',
                            'closed' => 'border-slate-200 dark:border-slate-700',
                        };
                    ?>
                    <div class="w-72 flex-shrink-0 flex flex-col bg-white dark:bg-slate-800 border <?php echo e($colColor); ?> rounded-lg">
                        <div class="px-3 py-2 border-b <?php echo e($colColor); ?> flex items-center justify-between sticky top-0 bg-white dark:bg-slate-800 z-10">
                            <span class="text-xs font-semibold uppercase tracking-wide"><?php echo e($statusLabels[$status]); ?></span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700"><?php echo e($count); ?></span>
                        </div>
                        <div class="p-2 space-y-2 flex-1 overflow-y-auto max-h-[70vh]">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="p-2 rounded-md border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50 hover:shadow-sm cursor-pointer" wire:click="rowDetail(<?php echo e($t->id); ?>)">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-mono text-[10px] text-slate-400">#<?php echo e($t->id); ?> · <?php echo e(substr($t->uuid, -6)); ?></span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full <?php echo e($priorityBadge[$t->priority] ?? ''); ?>"><?php echo e(strtoupper($t->priority)); ?></span>
                                    </div>
                                    <div class="font-medium text-sm mt-1 line-clamp-2 text-slate-800 dark:text-slate-100"><?php echo e($t->title); ?></div>
                                    <div class="mt-1.5 flex items-center justify-between text-[11px]">
                                        <span class="text-slate-500 truncate max-w-[60%]"><?php echo e($t->customer?->name ?? '-'); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t->assignedTo): ?>
                                            <span class="flex items-center gap-1 text-slate-600 dark:text-slate-300">
                                                <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-[9px]"><?php echo e(strtoupper(substr($t->assignedTo->name, 0, 1))); ?></span>
                                                <span class="truncate max-w-[60px]"><?php echo e($t->assignedTo->name); ?></span>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">Unassigned</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="mt-1 text-[10px] text-slate-400">
                                        <?php echo e(\Illuminate\Support\Carbon::parse($t->created_at)->diffForHumans()); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t->due_date): ?>
                                            <span class="ml-2 <?php echo e(\Illuminate\Support\Carbon::parse($t->due_date)->isPast() ? 'text-red-500 font-medium' : ''); ?>">Due <?php echo e(\Illuminate\Support\Carbon::parse($t->due_date)->format('d/m')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div class="py-6 text-center text-xs text-slate-400 italic">Kosong</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    <?php elseif($this->activeTab === 'timeline'): ?>
        <div class="flex-1 overflow-auto min-h-0 p-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->timelineData && $this->timelineTicketId): ?>
                <div class="max-w-3xl mx-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs text-slate-500">#<?php echo e($this->timelineData['ticket']->id); ?></span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full <?php echo e($statusBadge[$this->timelineData['ticket']->status] ?? ''); ?> border"><?php echo e($statusLabels[$this->timelineData['ticket']->status] ?? $this->timelineData['ticket']->status); ?></span>
                            </div>
                            <h3 class="font-semibold mt-1"><?php echo e($this->timelineData['ticket']->title); ?></h3>
                            <div class="text-xs text-slate-500 mt-0.5"><?php echo e($this->timelineData['ticket']->customer?->name ?? '-'); ?> · <?php echo e($this->timelineData['ticket']->category); ?> · Priority <span class="font-medium"><?php echo e($this->timelineData['ticket']->priority); ?></span></div>
                        </div>
                        <button wire:click="$set('timelineTicketId', null)" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-4">
                        <?php
                            $events = $this->timelineData['events'] ?? [];
                        ?>
                        <div class="relative pl-6 border-l-2 border-slate-200 dark:border-slate-700 space-y-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $typeIcon = match($ev['type']) {
                                        'create' => 'text-blue-500 bg-blue-100 dark:bg-blue-900/50',
                                        'assign' => 'text-amber-500 bg-amber-100 dark:bg-amber-900/50',
                                        'comment' => 'text-slate-500 bg-slate-100 dark:bg-slate-700',
                                        'resolve' => 'text-emerald-500 bg-emerald-100 dark:bg-emerald-900/50',
                                        'close' => 'text-slate-700 bg-slate-200 dark:bg-slate-600',
                                        default => 'text-slate-500 bg-slate-100',
                                    };
                                ?>
                                <div class="relative">
                                    <div class="absolute -left-[30px] top-1 w-6 h-6 rounded-full flex items-center justify-center <?php echo e($typeIcon); ?>">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ev['type']==='create'): ?><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            <?php elseif($ev['type']==='assign'): ?><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            <?php elseif($ev['type']==='resolve'): ?><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            <?php elseif($ev['type']==='close'): ?><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            <?php else: ?><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </svg>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="font-semibold"><?php echo e($ev['title']); ?></span>
                                        <span class="text-slate-400">oleh <?php echo e($ev['by']); ?></span>
                                        <span class="ml-auto text-slate-400"><?php echo e(\Illuminate\Support\Carbon::parse($ev['time'])->diffForHumans()); ?> · <?php echo e(\Illuminate\Support\Carbon::parse($ev['time'])->format('d/m H:i')); ?></span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ev['detail'])): ?>
                                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/40 rounded-md p-2 border border-slate-100 dark:border-slate-700"><?php echo e($ev['detail']); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="h-full flex items-center justify-center text-slate-400 text-sm">
                    <div class="text-center">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Klik ticket di Table/Kanban untuk lihat timeline
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 z-10">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                        <th class="w-10 px-3 py-2"><input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600"></th>
                        <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('id')">Ticket ID</th>
                        <th class="px-3 py-2 text-left">Title</th>
                        <th class="px-3 py-2 text-left">Customer</th>
                        <th class="px-3 py-2 text-left">Category</th>
                        <th class="px-3 py-2 text-left">Priority</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Assignee</th>
                        <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('due_date')">Due</th>
                        <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('updated_at')">Last Update</th>
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php
                        $pageRows = $rows instanceof \Illuminate\Pagination\LengthAwarePaginator ? $rows->items() : (is_array($rows) ? $rows : $rows->all());
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pageRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-3 py-2"><input type="checkbox" wire:model.live="selected" value="<?php echo e((string) $t->id); ?>" class="rounded border-slate-300 dark:border-slate-600"></td>
                            <td class="px-3 py-2 font-mono text-xs">
                                <span class="text-slate-500">#<?php echo e($t->id); ?></span>
                                <span class="text-slate-400 block"><?php echo e(substr($t->uuid, -6)); ?></span>
                            </td>
                            <td class="px-3 py-2 max-w-xs truncate font-medium"><?php echo e($t->title); ?></td>
                            <td class="px-3 py-2 text-xs"><?php echo e($t->customer?->name ?? '-'); ?></td>
                            <td class="px-3 py-2 text-xs capitalize"><?php echo e($t->category); ?></td>
                            <td class="px-3 py-2"><span class="text-[10px] px-2 py-0.5 rounded-full <?php echo e($priorityBadge[$t->priority] ?? ''); ?>"><?php echo e(strtoupper($t->priority)); ?></span></td>
                            <td class="px-3 py-2"><span class="text-[10px] px-2 py-0.5 rounded-full border <?php echo e($statusBadge[$t->status] ?? ''); ?>"><?php echo e($statusLabels[$t->status] ?? $t->status); ?></span></td>
                            <td class="px-3 py-2 text-xs">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t->assignedTo): ?>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-[9px] font-semibold"><?php echo e(strtoupper(substr($t->assignedTo->name,0,1))); ?></span>
                                        <span class="truncate max-w-[80px]"><?php echo e($t->assignedTo->name); ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-400 italic text-[10px]">Unassigned</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-3 py-2 text-xs <?php echo e($t->due_date && \Illuminate\Support\Carbon::parse($t->due_date)->isPast() && !in_array($t->status, ['resolved','closed']) ? 'text-red-600 dark:text-red-400 font-semibold' : ''); ?>">
                                <?php echo e($t->due_date ? \Illuminate\Support\Carbon::parse($t->due_date)->format('d/m/Y') : '-'); ?>

                            </td>
                            <td class="px-3 py-2 text-xs text-slate-500"><?php echo e(\Illuminate\Support\Carbon::parse($t->updated_at)->diffForHumans()); ?></td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-0.5">
                                    <button wire:click="rowDetail(<?php echo e($t->id); ?>)" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded dark:hover:bg-blue-900/30" title="Detail/Timeline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button wire:click="rowEdit(<?php echo e($t->id); ?>)" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded dark:hover:bg-blue-900/30" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="rowAssign(<?php echo e($t->id); ?>)" class="p-1 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded dark:hover:bg-amber-900/30" title="Assign">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!in_array($t->status, ['resolved','closed'])): ?>
                                        <button wire:click="rowResolve(<?php echo e($t->id); ?>)" class="p-1 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded dark:hover:bg-emerald-900/30" title="Resolve">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t->status !== 'closed'): ?>
                                        <button wire:click="rowClose(<?php echo e($t->id); ?>)" class="p-1 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded dark:hover:bg-slate-700" title="Close">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <button wire:click="rowDelete(<?php echo e($t->id); ?>)" class="p-1 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="100" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            <div class="font-medium">Tidak ada tiket</div>
                        </td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows instanceof \Illuminate\Pagination\LengthAwarePaginator && $rows->hasPages()): ?>
            <div class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 flex items-center justify-between text-sm">
                <div class="text-slate-500 dark:text-slate-400 text-xs">Menampilkan <?php echo e($rows->firstItem()); ?>-<?php echo e($rows->lastItem()); ?> dari <?php echo e($rows->total()); ?></div>
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="text-xs rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-1 px-2">
                        <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
                    </select>
                    <?php echo e($rows->links('livewire::simple-tailwind')); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showBulkAssign): ?>
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div x-show="show" class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700"><h3 class="font-semibold">Bulk Assign Teknisi</h3><p class="text-xs text-slate-500 mt-0.5"><?php echo e(count($this->selected)); ?> ticket terpilih</p></div>
                <div class="px-5 py-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pilih Petugas</label>
                        <select wire:model.live="bulkAssigneeId" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3">
                            <option value="">-- Pilih --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($this->filterOptions['assignees'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end gap-2">
                    <button @click="show = false; $wire.cancelBulkAssign()" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700">Batal</button>
                    <button wire:click="submitBulkAssign" class="px-3 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Assign</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showCreateForm): ?>
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div x-show="show" class="w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl max-h-[90vh] overflow-auto">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white dark:bg-slate-800">
                    <h3 class="font-semibold">Buat Ticket Baru</h3>
                    <button @click="show = false; $wire.closeCreateForm()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1">Customer *</label>
                            <select wire:model.live="newTicket.customer_id" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3">
                                <option value="">-- Pilih --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($this->filterOptions['customers'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1">Title *</label>
                            <input wire:model.live="newTicket.title" type="text" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1">Deskripsi</label>
                            <textarea wire:model.live="newTicket.description" rows="3" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kategori</label>
                            <select wire:model.live="newTicket.category" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($this->filterOptions['categories'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Priority</label>
                            <select wire:model.live="newTicket.priority" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($this->filterOptions['priorities'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Assignee</label>
                            <select wire:model.live="newTicket.assigned_to" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3">
                                <option value="">-- Opsional --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($this->filterOptions['assignees'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Due Date</label>
                            <input wire:model.live="newTicket.due_date" type="date" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3">
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end gap-2 sticky bottom-0">
                    <button @click="show = false; $wire.closeCreateForm()" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700">Batal</button>
                    <button wire:click="submitCreate" class="px-4 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Buat Ticket</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\support\ticket\index.blade.php ENDPATH**/ ?>