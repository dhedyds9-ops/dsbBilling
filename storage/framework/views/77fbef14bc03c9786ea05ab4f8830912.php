<?php
  $sortIcon = function($field) {
    $dir = $this->sortField === $field ? ($this->sortDirection === 'asc' ? '↑' : '↓') : '';
    return $dir ? " <span class='text-blue-600'>{$dir}</span>" : '';
  };
  $rupiah = function($n) {
    return 'Rp ' . number_format((float)($n ?? 0), 0, ',', '.');
  };
  $periodeKey = function($tahun, $bulan) {
    return "{$tahun}-{$bulan}";
  };
?>
<div>
  <?php echo $__env->make('partials.enterprise.list-toolbar', [
    'title' => 'Periode Tagihan',
    'primaryLabel' => 'Generate',
    'primaryAction' => 'openGenerate()',
    'actions' => [
      ['label' => 'Regenerate', 'icon' => 'repeat', 'action' => 'openRegenerate()'],
      ['label' => 'Kirim WhatsApp', 'icon' => 'send', 'action' => 'kirimWaSemua()'],
      ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
    ],
    'searchPlaceholder' => 'Cari nomor invoice / nama pelanggan...',
    'showFiltersToggle' => true,
  ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <?php echo $__env->make('partials.enterprise.summary-cards', [
    'items' => [
      ['label' => 'Total Tagihan', 'value' => $rupiah($this->summary['total_tagihan'] ?? 0), 'color' => 'slate', 'icon' => 'file-text'],
      ['label' => 'Belum Bayar', 'value' => $rupiah($this->summary['belum_bayar'] ?? 0), 'color' => 'amber', 'icon' => 'alert-triangle'],
      ['label' => 'Lunas', 'value' => $rupiah($this->summary['lunas'] ?? 0), 'color' => 'green', 'icon' => 'check-circle'],
      ['label' => 'Overdue', 'value' => $rupiah($this->summary['overdue'] ?? 0), 'color' => 'red', 'icon' => 'clock'],
    ],
  ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showFilters): ?>
    <?php echo $__env->make('partials.enterprise.filters', [
      'filters' => [
        ['key' => 'tahun', 'label' => 'Tahun', 'type' => 'select', 'options' => $this->filterOptions['tahun'] ?? []],
        ['key' => 'bulan', 'label' => 'Bulan', 'type' => 'select', 'options' => $this->filterOptions['bulan'] ?? []],
        ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
          'unpaid' => 'Unpaid',
          'partial' => 'Partial',
          'pending' => 'Pending',
          'paid' => 'Lunas',
          'overdue' => 'Overdue',
        ]],
        ['key' => 'router_id', 'label' => 'Router', 'type' => 'select', 'options' => $this->filterOptions['routers'] ?? []],
        ['key' => 'sales_id', 'label' => 'Sales', 'type' => 'select', 'options' => $this->filterOptions['sales'] ?? []],
        ['key' => 'reseller_id', 'label' => 'Reseller', 'type' => 'select', 'options' => $this->filterOptions['resellers'] ?? []],
        ['key' => 'package_id', 'label' => 'Paket', 'type' => 'select', 'options' => $this->filterOptions['packages'] ?? []],
      ],
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php echo $__env->make('partials.enterprise.bulk-bar', [
    'bulkActions' => [
      ['key' => 'generate', 'label' => 'Generate', 'variant' => 'bg-blue-600 text-white hover:bg-blue-700'],
      ['key' => 'regenerate', 'label' => 'Regenerate'],
      ['key' => 'send-wa', 'label' => 'Kirim WA'],
      ['key' => 'export', 'label' => 'Export'],
    ],
  ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="relative overflow-auto bg-white dark:bg-slate-800">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->loading): ?>
      <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 dark:bg-slate-900/60 backdrop-blur-[1px]">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border border-blue-100 dark:border-blue-800">
          <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
          Memuat periode tagihan...
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->errorMessage): ?>
      <div class="mx-3 mt-3 p-3 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
        <?php echo e($this->errorMessage); ?>

      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40 border-y border-slate-200 dark:border-slate-700">
        <tr>
          <th class="w-10 px-3 py-2 text-left">
            <label class="inline-flex items-center">
              <input type="checkbox" wire:model.live="selectAll"
                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600">
            </label>
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('tahun')">
            Periode<?php echo $sortIcon('tahun'); ?>

          </th>
          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('jumlah_invoice')">
            Jumlah Invoice<?php echo $sortIcon('jumlah_invoice'); ?>

          </th>
          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('total_tagihan')">
            Total Tagihan<?php echo $sortIcon('total_tagihan'); ?>

          </th>
          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('sudah_dibayar')">
            Sudah Dibayar<?php echo $sortIcon('sudah_dibayar'); ?>

          </th>
          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('belum_dibayar')">
            Belum Dibayar<?php echo $sortIcon('belum_dibayar'); ?>

          </th>
          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Lunas (%)
          </th>
          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('overdue_count')">
            Overdue<?php echo $sortIcon('overdue_count'); ?>

          </th>
          <th class="w-28 px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
          <?php
            $key = $periodeKey($row->tahun, $row->bulan);
            $lunasPct = (int)($row->lunas_pct ?? 0);
            $lunasColor = $lunasPct >= 90 ? 'text-emerald-600 dark:text-emerald-300'
                        : ($lunasPct >= 60 ? 'text-blue-600 dark:text-blue-300'
                        : ($lunasPct >= 30 ? 'text-amber-600 dark:text-amber-300'
                        : 'text-red-600 dark:text-red-300'));
          ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
            <td class="px-3 py-2">
              <label class="inline-flex items-center">
                <input type="checkbox" value="<?php echo e($key); ?>" wire:model.live="selected"
                  class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600">
              </label>
            </td>
            <td class="px-3 py-2">
              <div class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e($row->label_periode ?? "{$row->tahun}-{$row->bulan}"); ?></div>
              <div class="text-[11px] text-slate-400 dark:text-slate-500">Periode <?php echo e(str_pad($row->bulan, 2, '0', STR_PAD_LEFT)); ?>/<?php echo e($row->tahun); ?></div>
            </td>
            <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200 font-medium">
              <?php echo e(number_format((int)($row->jumlah_invoice ?? 0))); ?>

            </td>
            <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200 font-medium">
              <?php echo e($rupiah($row->total_tagihan)); ?>

            </td>
            <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-300">
              <?php echo e($rupiah($row->sudah_dibayar)); ?>

            </td>
            <td class="px-3 py-2 text-right text-amber-700 dark:text-amber-300">
              <?php echo e($rupiah($row->belum_dibayar)); ?>

            </td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-2">
                <div class="w-16 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                  <div class="h-full bg-blue-600 dark:bg-blue-500" style="width: <?php echo e(min(100, $lunasPct)); ?>%"></div>
                </div>
                <span class="text-xs font-medium <?php echo e($lunasColor); ?>"><?php echo e($lunasPct); ?>%</span>
              </div>
            </td>
            <td class="px-3 py-2 text-right">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($row->overdue_count ?? 0) > 0): ?>
                <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                  <?php echo e(number_format((int)($row->overdue_count ?? 0))); ?>

                </span>
              <?php else: ?>
                <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">0</span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-1">
                <button wire:click="confirmRowAction('generate', <?php echo e($row->tahun); ?>, <?php echo e($row->bulan); ?>)" title="Generate"
                  class="p-1.5 rounded-md text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:text-slate-400 dark:hover:bg-blue-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <button wire:click="confirmRowAction('regenerate', <?php echo e($row->tahun); ?>, <?php echo e($row->bulan); ?>)" title="Regenerate"
                  class="p-1.5 rounded-md text-slate-500 hover:text-purple-600 hover:bg-purple-50 dark:text-slate-400 dark:hover:bg-purple-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <button wire:click="confirmRowAction('send-wa', <?php echo e($row->tahun); ?>, <?php echo e($row->bulan); ?>)" title="Kirim WA"
                  class="p-1.5 rounded-md text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:text-slate-400 dark:hover:bg-emerald-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
                <button wire:click="confirmRowAction('export', <?php echo e($row->tahun); ?>, <?php echo e($row->bulan); ?>)" title="Export"
                  class="p-1.5 rounded-md text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-700">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </button>
              </div>
            </td>
          </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
          <tr>
            <td colspan="9" class="px-3 py-16 text-center">
              <div class="inline-flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div class="text-sm font-medium">Belum ada periode tagihan</div>
                <div class="text-xs opacity-80">Klik Generate untuk membuat tagihan untuk periode baru.</div>
              </div>
            </td>
          </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="px-3 py-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <?php echo e($rows->links('livewire::simple-tailwind')); ?>

  </div>

  <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showGenerateModal || $this->showRegenerateModal): ?>
    <?php $modalTitle = $this->showRegenerateModal ? 'Regenerate Periode Tagihan' : 'Generate Periode Tagihan'; ?>
    <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e($modalTitle); ?></h3>
          <button wire:click="closeGenerate" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="grid grid-cols-2 gap-3 px-5 py-4">
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Tahun</label>
            <select wire:model.live="generateTahun" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($this->filterOptions['tahun'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $th => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($th); ?>"><?php echo e($label); ?></option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Bulan</label>
            <select wire:model.live="generateBulan" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($this->filterOptions['bulan'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bl => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($bl); ?>"><?php echo e($label); ?></option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
          </div>
          <div class="col-span-2">
            <div class="p-3 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs">
              Sistem akan generate tagihan untuk semua pelanggan aktif pada periode <?php echo e($this->generateBulan); ?>/<?php echo e($this->generateTahun); ?>.
              Invoice yang sudah ada tidak akan diduplikasi.
            </div>
          </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-2 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl">
          <button wire:click="<?php if($this->showRegenerateModal): ?> closeRegenerate <?php else: ?> closeGenerate <?php endif; ?>" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700">Batal</button>
          <button wire:click="<?php if($this->showRegenerateModal): ?> submitRegenerate <?php else: ?> submitGenerate <?php endif; ?>" class="px-4 py-1.5 text-sm font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow-sm">
            <?php echo e($this->showRegenerateModal ? 'Regenerate' : 'Generate'); ?>

          </button>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\billing\periode-tagihan\index.blade.php ENDPATH**/ ?>