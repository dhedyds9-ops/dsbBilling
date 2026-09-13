<?php
  $fmt = fn($n) => 'Rp ' . number_format((float)($n ?? 0), 0, ',', '.');
  $statusBadge = fn($s) => match(strtolower($s)){
    'paid' => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'Dibayar'],
    'pending' => ['bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300', 'Pending'],
    'draft' => ['bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-200', 'Draft'],
    default => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', $s ?: '-'],
  };
?>
<div>
    <?php $__env->startSection('page_title'); ?>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-orange-500" translate="no" style="font-size:24px">receipt_long</span>
            <span class="text-lg">Laporan BHP & USO</span>
        </div>
  <?php $__env->stopSection(); ?>

  
  <div class="mb-4 flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
      <div class="flex-1 w-full relative flex gap-2">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->filterConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($f['type'] === 'select'): ?>
                  <select wire:model.live="filters.<?php echo e($f['key']); ?>" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block px-3 py-2 min-w-[120px] dark:bg-slate-900 dark:text-slate-100">
                      <option value=""><?php echo e($f['label']); ?></option>
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $f['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                          <option value="<?php echo e($val); ?>"><?php echo e($lbl); ?></option>
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                  </select>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
      </div>
      <div class="flex items-center gap-2">
          <button wire:click="exportCsv" class="px-4 py-2 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 dark:text-emerald-400 rounded-lg text-sm font-medium hover:bg-emerald-100 dark:bg-emerald-900/50 dark:hover:bg-emerald-900/40 transition-colors flex items-center gap-2">
              <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span> CSV
          </button>
          <button wire:click="cetakSkri" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2">
              <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">printer</span> Cetak SPT
          </button>
      </div>
       <div>
              <button wire:click="confirmRowAction('pay-bhp', 0)" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">payments</span> Bayar BHP/USO
              </button>
          </div>
  </div>

  <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-800 shadow-sm">
      <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <nav class="flex items-center gap-1 text-sm font-medium overflow-x-auto">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['summary'=>'Ringkasan','detail'=>'Detail Perhitungan','history'=>'Riwayat Pembayaran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <button wire:click="setActiveTab('<?php echo e($k); ?>')" class="whitespace-nowrap px-3 py-1.5 rounded-md transition-colors <?php echo e($activeTab===$k ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700'); ?>"><?php echo e($l); ?></button>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </nav>
  </div>

  <div class="relative bg-white dark:bg-slate-800 overflow-auto">
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'summary'): ?>
      <div class="p-4 grid grid-cols-1 xl:grid-cols-4 gap-4">
        <div class="xl:col-span-1 space-y-3">
          <div class="border border-blue-200 dark:border-blue-800 rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4">
            <div class="text-xs text-blue-700 dark:text-blue-400 mb-1">Periode</div>
            <div class="text-lg font-bold text-blue-900 dark:text-blue-100"><?php echo e($summary['period_label'] ?? '-'); ?></div>
            <div class="text-xs text-blue-600 dark:text-blue-400 mt-1"><?php echo e($summary['count_days'] ?? 0); ?> hari aktif</div>
          </div>
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Revenue (Dasar)</div>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-slate-100"><?php echo e($fmt($summary['revenue_total'] ?? 0)); ?></div>
          </div>
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">Total BHP (18.75%)</div>
                <div class="font-mono font-bold text-sm text-amber-700 dark:text-amber-300 mt-0.5"><?php echo e($fmt($summary['bhp_total'] ?? 0)); ?></div>
              </div>
              <div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">USO Kominfo (1.25%)</div>
                <div class="font-mono font-bold text-sm text-orange-700 dark:text-orange-300 mt-0.5"><?php echo e($fmt($summary['uso_total'] ?? 0)); ?></div>
              </div>
            </div>
          </div>
          <div class="border-2 border-red-200 dark:border-red-800 rounded-lg bg-red-50 dark:bg-red-900/20 p-4">
            <div class="text-xs text-red-700 dark:text-red-400 mb-1">TOTAL SETORAN</div>
            <div class="text-2xl font-bold font-mono text-red-700 dark:text-red-300"><?php echo e($fmt($summary['grand_total'] ?? 0)); ?></div>
            <div class="mt-1 text-xs text-red-700/80 dark:text-red-400/80">Effective rate <?php echo e(number_format($summary['effective_pct'] ?? 0, 2)); ?>% dari revenue</div>
          </div>
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">YTD (Jan s/d periode ini)</div>
            <div class="font-mono font-bold text-slate-900 dark:text-slate-100"><?php echo e($fmt($summary['ytd_total'] ?? 0)); ?></div>
          </div>
        </div>
        <div class="xl:col-span-3">
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
              <div class="font-semibold text-slate-900 dark:text-slate-100">Breakdown BHP per Komponen</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Kontribusi terhadap Total BHP 18.75%</div>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $bhpItems ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="px-4 py-2.5">
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-slate-100"><?php echo e($b['code']); ?> · <?php echo e($b['name']); ?></div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">Rate: <?php echo e($b['base_pct']); ?>% · Min <?php echo e($fmt($b['base_min'] ?? 0)); ?></div>
                    </div>
                    <div class="text-right">
                      <div class="font-mono font-bold text-slate-800 dark:text-slate-100"><?php echo e($fmt($b['nominal'] ?? 0)); ?></div>
                      <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400"><?php echo e(number_format($b['pct_of_bhp'] ?? 0, 1)); ?>% dari BHP</div>
                    </div>
                  </div>
                  <div class="mt-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-amber-400 to-orange-500" style="width:<?php echo e(number_format($b['pct_of_bhp'] ?? 0, 1)); ?>%"></div></div>
                </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              <div class="px-4 py-2.5 bg-emerald-50 dark:bg-emerald-900/20 border-t-2 border-emerald-200 dark:border-emerald-800 flex justify-between font-bold">
                <span class="text-emerald-900 dark:text-emerald-100">TOTAL BHP + USO</span>
                <span class="font-mono text-emerald-700 dark:text-emerald-300"><?php echo e($fmt($summary['grand_total'] ?? 0)); ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php elseif($activeTab === 'detail'): ?>
      <div class="p-4">
        <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Detail Perhitungan BHP & USO · Periode <?php echo e($summary['period_label'] ?? '-'); ?></div>
          <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Kode</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Uraian Komponen</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Dasar Pengenaan</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Rate (%)</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Nilai Minimum</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Nominal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <?php $subtotals = ['BHP'=>0,'USO'=>0]; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detailRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                  if ($r['row_type'] ?? null) {
                    if ($r['row_type'] === 'subtotal-bhp') $subtotals['BHP'] = $r['nominal'];
                    if ($r['row_type'] === 'subtotal-uso') $subtotals['USO'] = $r['nominal'];
                  }
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($r['row_type'] ?? null) === 'detail'): ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/30">
                  <td class="px-4 py-2 font-mono text-xs text-slate-600 dark:text-slate-400"><?php echo e($r['code']); ?></td>
                  <td class="px-4 py-2 text-slate-700 dark:text-slate-200"><?php echo e($r['name']); ?></td>
                  <td class="px-4 py-2 text-right font-mono text-slate-600 dark:text-slate-300"><?php echo e($fmt($r['dasar_pengenaan'] ?? 0)); ?></td>
                  <td class="px-4 py-2 text-right font-mono text-slate-500 dark:text-slate-400 text-xs"><?php echo e($r['base_pct']); ?>%</td>
                  <td class="px-4 py-2 text-right font-mono text-slate-500 dark:text-slate-400 text-xs"><?php echo e($fmt($r['base_min'] ?? 0)); ?></td>
                  <td class="px-4 py-2 text-right font-mono font-semibold text-slate-800 dark:text-slate-100"><?php echo e($fmt($r['nominal'] ?? 0)); ?></td>
                </tr>
                <?php elseif(($r['row_type'] ?? null) === 'subtotal-bhp'): ?>
                <tr class="bg-amber-50 dark:bg-amber-900/20 font-bold border-t-2 border-amber-200 dark:border-amber-800">
                  <td class="px-4 py-2.5" colspan="5"><span class="text-amber-900 dark:text-amber-100">SUBTOTAL BHP (18.75%)</span></td>
                  <td class="px-4 py-2.5 text-right font-mono text-amber-700 dark:text-amber-300"><?php echo e($fmt($r['nominal'] ?? 0)); ?></td>
                </tr>
                <?php elseif(($r['row_type'] ?? null) === 'subtotal-uso'): ?>
                <tr class="bg-orange-50 dark:bg-orange-900/20 font-bold border-t border-orange-200 dark:border-orange-800">
                  <td class="px-4 py-2.5" colspan="5"><span class="text-orange-900 dark:text-orange-100">SUBTOTAL USO KOMINFO (1.25%)</span></td>
                  <td class="px-4 py-2.5 text-right font-mono text-orange-700 dark:text-orange-300"><?php echo e($fmt($r['nominal'] ?? 0)); ?></td>
                </tr>
                <?php elseif(($r['row_type'] ?? null) === 'grand'): ?>
                <tr class="bg-red-50 dark:bg-red-900/20 border-t-2 border-red-200 dark:border-red-800 font-bold">
                  <td class="px-4 py-3" colspan="5"><span class="text-red-900 dark:text-red-100 text-lg">GRAND TOTAL BHP + USO YANG HARUS DISETORKAN</span></td>
                  <td class="px-4 py-3 text-right font-mono text-red-700 dark:text-red-300 text-xl"><?php echo e($fmt($r['nominal'] ?? 0)); ?></td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php elseif($activeTab === 'history'): ?>
      <div class="p-4">
        <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Riwayat Pembayaran BHP & USO (Tahun <?php echo e($year ?? date('Y')); ?>)</div>
          <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Periode</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Revenue</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">BHP</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">USO</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Total</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Status</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">No. Bukti / Dibayar</th>
                <th class="w-20 px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $historyRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php [$sc, $sl] = $statusBadge($h['status'] ?? 'draft'); ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/30">
                  <td class="px-4 py-2 font-medium text-slate-800 dark:text-slate-100"><?php echo e($h['period_label']); ?></td>
                  <td class="px-4 py-2 text-right font-mono text-slate-600 dark:text-slate-300"><?php echo e($fmt($h['revenue'] ?? 0)); ?></td>
                  <td class="px-4 py-2 text-right font-mono text-amber-700 dark:text-amber-300"><?php echo e($fmt($h['bhp'] ?? 0)); ?></td>
                  <td class="px-4 py-2 text-right font-mono text-orange-700 dark:text-orange-300"><?php echo e($fmt($h['uso'] ?? 0)); ?></td>
                  <td class="px-4 py-2 text-right font-mono font-bold text-slate-800 dark:text-slate-100"><?php echo e($fmt($h['total'] ?? 0)); ?></td>
                  <td class="px-4 py-2"><span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full <?php echo e($sc); ?>"><?php echo e($sl); ?></span></td>
                  <td class="px-4 py-2">
                    <div class="font-mono text-xs text-slate-700 dark:text-slate-200"><?php echo e($h['no_bukti'] ?? '-'); ?></div>
                    <div class="text-[10px] text-slate-500 dark:text-slate-400"><?php echo e($h['paid_date'] ?? '-'); ?></div>
                  </td>
                  <td class="px-4 py-2">
                    <div class="flex justify-end gap-1">
                      <button title="Cetak Bukti" class="p-1.5 rounded text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:bg-blue-900/30 dark:text-slate-400 dark:hover:bg-blue-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($h['status'] ?? 'draft') !== 'paid'): ?>
                      <button wire:click="confirmRowAction('pay-bhp', <?php echo e($h['id'] ?? 0); ?>)" title="Bayar" class="p-1.5 rounded text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:bg-emerald-900/30 dark:text-slate-400 dark:hover:bg-emerald-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </div>
                  </td>
                </tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($historyRows)): ?>
                <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">Belum ada riwayat pembayaran BHP/USO.</td></tr>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\keuangan\bhp-uso\index.blade.php ENDPATH**/ ?>