<?php
  $sortIcon = fn($f) => $this->sortField === $f ? ' <span class="text-blue-600">'.($this->sortDirection==='asc'?'↑':'↓').'</span>' : '';
  $statusBadge = fn($s) => match(strtolower($s)){
    'scheduled' => ['bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300','Dijadwalkan'],
    'in_progress' => ['bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300','Dikerjakan'],
    'completed' => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300','Selesai'],
    'overdue' => ['bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300','Terlambat'],
    default => ['bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200', $s ?: '-'],
  };
  $typeBadge = fn($t) => match(strtolower($t)){
    'preventive' => ['bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300','Preventive'],
    'corrective' => ['bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300','Corrective'],
    'predictive' => ['bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300','Predictive'],
    'emergency' => ['bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300','Emergency'],
    default => ['bg-slate-100 text-slate-600', $t ?: '-'],
  };
?>
<div>
  <?php echo $__env->make('partials.enterprise.list-toolbar', [
    'title' => 'Support Maintenance',
    'primaryLabel' => 'Jadwal Baru',
    'primaryAction' => 'openCreate',
    'actions' => [
      ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
      ['label' => 'Pemakaian Material', 'icon' => 'package', 'action' => 'openUsageModal=true'],
    ],
    'searchPlaceholder' => 'Cari jadwal / perangkat / lokasi / catatan...',
    'showFiltersToggle' => true,
  ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <?php echo $__env->make('partials.enterprise.summary-cards', [
    'items' => [
      ['label' => 'Total Jadwal', 'value' => number_format($summary['total'] ?? 0), 'color' => 'slate', 'icon' => 'wrench'],
      ['label' => 'Dijadwalkan', 'value' => number_format($summary['scheduled'] ?? 0), 'color' => 'blue', 'icon' => 'calendar'],
      ['label' => 'Dikerjakan', 'value' => number_format($summary['in_progress'] ?? 0), 'color' => 'amber', 'icon' => 'loader'],
      ['label' => 'Preventive', 'value' => number_format($summary['preventive'] ?? 0), 'color' => 'sky', 'icon' => 'shield-check'],
      ['label' => 'Corrective', 'value' => number_format($summary['corrective'] ?? 0), 'color' => 'orange', 'icon' => 'wrench-screwdriver'],
      ['label' => 'Overdue', 'value' => number_format($summary['overdue'] ?? 0), 'color' => 'red', 'icon' => 'alert-triangle'],
    ],
  ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFilters): ?>
    <?php echo $__env->make('partials.enterprise.filters', ['filters' => $filterConfig], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <nav class="flex items-center gap-1 text-sm font-medium overflow-x-auto">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['calendar'=>'Kalender','history'=>'Riwayat','technician'=>'Teknisi','material'=>'Material']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <button wire:click="setActiveTab('<?php echo e($k); ?>')" class="whitespace-nowrap px-3 py-1.5 rounded-md transition-colors <?php echo e($activeTab===$k ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'); ?>"><?php echo e($l); ?></button>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </nav>
  </div>

  <?php echo $__env->make('partials.enterprise.bulk-bar', [
    'bulkActions' => [
      ['key' => 'complete', 'label' => 'Tandai Selesai'],
      ['key' => 'export', 'label' => 'Export'],
    ],
  ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="relative overflow-auto bg-white dark:bg-slate-800">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
      <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 dark:bg-slate-900/60 backdrop-blur-[1px]">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border border-blue-100 dark:border-blue-800">
          <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
          Memuat data...
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
      <div class="mx-3 mt-3 p-3 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
        <?php echo e($errorMessage); ?>

      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'calendar'): ?>
      <div class="p-4">
        <div class="grid grid-cols-7 gap-1 text-xs">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Min','Sen','Sel','Rab','Kam','Jum','Sab']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="px-2 py-1 font-semibold text-center text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700"><?php echo e($d); ?></div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($calendar) && is_array($calendar)): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $calendar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
              <div class="min-h-[92px] border border-slate-100 dark:border-slate-700/60 rounded-md p-1 text-slate-700 dark:text-slate-200 <?php echo e($cell['is_today']??false ? 'bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-200 dark:ring-blue-800' : ''); ?>">
                <div class="text-right text-[10px] text-slate-500 dark:text-slate-400"><?php echo e($cell['day']); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cell['events'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <div class="mt-0.5 text-[10px] truncate rounded px-1 py-0.5 <?php echo e($ev['type']==='preventive' ? 'bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300' : ($ev['type']==='emergency' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300')); ?>" title="<?php echo e($ev['title']); ?>"><?php echo e($ev['title']); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>
    <?php elseif($activeTab === 'history'): ?>
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/40 border-y border-slate-200 dark:border-slate-700">
          <tr>
            <th class="w-10 px-3 py-2 text-left"><input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"></th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('scheduled_date')">Jadwal<?php echo $sortIcon('scheduled_date'); ?></th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Judul / Perangkat</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Type</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Teknisi</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Lokasi</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('status')">Status<?php echo $sortIcon('status'); ?></th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('completed_at')">Selesai<?php echo $sortIcon('completed_at'); ?></th>
            <th class="w-28 px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php [$sc,$sl]=$statusBadge($r->status??'scheduled'); [$tc,$tl]=$typeBadge($r->type??'corrective'); ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40">
              <td class="px-3 py-2"><input type="checkbox" value="<?php echo e($r->id); ?>" wire:model.live="selected" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"></td>
              <td class="px-3 py-2 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300"><?php echo e($r->scheduled_date ? \Carbon\Carbon::parse($r->scheduled_date)->format('d/m/Y H:i') : '-'); ?></td>
              <td class="px-3 py-2">
                <div class="font-medium text-slate-800 dark:text-slate-100"><?php echo e($r->title ?? $r->subject ?? $r->device_name ?? '-'); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($r->device_code ?? $r->notes ?? ''); ?></div>
              </td>
              <td class="px-3 py-2"><span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full <?php echo e($tc); ?>"><?php echo e($tl); ?></span></td>
              <td class="px-3 py-2 text-slate-600 dark:text-slate-300 text-xs"><?php echo e($r->technician_name ?? '-'); ?></td>
              <td class="px-3 py-2 text-slate-600 dark:text-slate-300 text-xs"><?php echo e($r->location_name ?? $r->pop_name ?? $r->router_name ?? '-'); ?></td>
              <td class="px-3 py-2"><span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full <?php echo e($sc); ?>"><?php echo e($sl); ?></span></td>
              <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400"><?php echo e($r->completed_at ? \Carbon\Carbon::parse($r->completed_at)->format('d/m/Y H:i') : '-'); ?></td>
              <td class="px-3 py-2">
                <div class="flex items-center justify-end gap-1">
                  <button wire:click="rowDetail(<?php echo e($r->id); ?>)" title="Detail" class="p-1.5 rounded text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:text-slate-400 dark:hover:bg-blue-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($r->status??'scheduled')==='scheduled'): ?>
                    <button wire:click="rowStart(<?php echo e($r->id); ?>)" title="Mulai" class="p-1.5 rounded text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:text-slate-400 dark:hover:bg-amber-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg></button>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($r->status??'scheduled')==='in_progress'): ?>
                    <button wire:click="confirmRowAction('complete', <?php echo e($r->id); ?>)" title="Selesai" class="p-1.5 rounded text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:text-slate-400 dark:hover:bg-emerald-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <button wire:click="confirmRowAction('delete', <?php echo e($r->id); ?>)" title="Hapus" class="p-1.5 rounded text-slate-500 hover:text-red-600 hover:bg-red-50 dark:text-slate-400 dark:hover:bg-red-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/></svg></button>
                </div>
              </td>
            </tr>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr><td colspan="9" class="px-3 py-16 text-center">
              <div class="inline-flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <div class="text-sm font-medium">Belum ada jadwal maintenance</div>
                <div class="text-xs opacity-80">Klik "Jadwal Baru" untuk menambahkan.</div>
              </div>
            </td></tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    <?php elseif($activeTab === 'technician'): ?>
      <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $technicianLoad ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50/50 dark:bg-slate-900/20">
            <div class="flex items-center justify-between mb-2">
              <div>
                <div class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e($t['name']); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($t['specialization'] ?? 'General Maintenance'); ?></div>
              </div>
              <div class="w-9 h-9 rounded-full bg-amber-600 text-white flex items-center justify-center font-semibold text-sm"><?php echo e(strtoupper(substr($t['name'],0,1))); ?></div>
            </div>
            <div class="grid grid-cols-4 gap-1 text-center text-xs">
              <div class="rounded bg-blue-100 dark:bg-blue-900/40 p-1"><div class="font-bold text-blue-700 dark:text-blue-300"><?php echo e($t['scheduled']??0); ?></div><div class="text-blue-600/80">Jdwl</div></div>
              <div class="rounded bg-amber-100 dark:bg-amber-900/40 p-1"><div class="font-bold text-amber-700 dark:text-amber-300"><?php echo e($t['in_progress']??0); ?></div><div class="text-amber-600/80">P</div></div>
              <div class="rounded bg-emerald-100 dark:bg-emerald-900/40 p-1"><div class="font-bold text-emerald-700 dark:text-emerald-300"><?php echo e($t['completed']??0); ?></div><div class="text-emerald-600/80">S</div></div>
              <div class="rounded bg-red-100 dark:bg-red-900/40 p-1"><div class="font-bold text-red-700 dark:text-red-300"><?php echo e($t['overdue']??0); ?></div><div class="text-red-600/80">O</div></div>
            </div>
          </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($technicianLoad)): ?>
          <div class="col-span-full text-center py-12 text-slate-500 dark:text-slate-400">Data teknisi maintenance belum tersedia.</div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    <?php elseif($activeTab === 'material'): ?>
      <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="border border-slate-200 dark:border-slate-700 rounded-lg">
          <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 font-semibold text-slate-900 dark:text-slate-100 flex justify-between items-center">
            <span>Stok Material</span>
            <span class="text-xs font-normal text-slate-500 dark:text-slate-400">Real-time</span>
          </div>
          <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stock ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
              <div class="px-4 py-2.5 flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-slate-800 dark:text-slate-100"><?php echo e($s['name']); ?></div>
                  <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($s['unit']); ?> · <?php echo e($s['location']); ?></div>
                </div>
                <div class="text-right">
                  <?php
                    $qty = $s['qty'] ?? 0;
                    $min = $s['min_stock'] ?? 0;
                    $cls = $qty == 0 ? 'text-red-600' : ($qty < $min ? 'text-amber-600' : 'text-emerald-600');
                  ?>
                  <div class="font-mono font-bold text-sm <?php echo e($cls); ?>"><?php echo e($qty); ?> <?php echo e($s['unit']); ?></div>
                  <div class="text-[10px] text-slate-500 dark:text-slate-400">Min <?php echo e($min); ?></div>
                </div>
              </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
          </div>
        </div>
        <div class="border border-slate-200 dark:border-slate-700 rounded-lg">
          <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 font-semibold text-slate-900 dark:text-slate-100">Pemakaian Terbaru</div>
          <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $usage ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
              <div class="px-4 py-2.5">
                <div class="flex justify-between">
                  <span class="font-medium text-slate-800 dark:text-slate-100"><?php echo e($u['material']); ?></span>
                  <span class="font-mono text-slate-600 dark:text-slate-300">-<?php echo e($u['qty']); ?> <?php echo e($u['unit']); ?></span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($u['date']); ?> · <?php echo e($u['technician']); ?> · <?php echo e($u['wo_ref']); ?></div>
              </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($usage)): ?>
              <div class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Belum ada pemakaian material.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'history'): ?>
    <div class="px-3 py-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
      <?php echo e($rows->links('livewire::simple-tailwind')); ?>

    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\support\maintenance\index.blade.php ENDPATH**/ ?>