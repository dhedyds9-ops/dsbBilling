<?php
  $fmt = fn($n) => 'Rp ' . number_format((float)($n ?? 0), 0, ',', '.');
  $pct = fn($n) => number_format((float)($n ?? 0), 2, ',', '.') . '%';
?>
<div>
    <?php $__env->startSection('page_title'); ?>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <span class="material-symbols-outlined notranslate" translate="no">account_balance</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Laporan Keuangan (Laba Rugi)</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Analisa pendapatan, pengeluaran, AR Aging, dan Net Profit</p>
            </div>
        </div>
    <?php $__env->stopSection(); ?>

    
    <div class="mb-4 flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex-1 w-full relative flex flex-wrap gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $filterConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
            <button wire:click="exportCsv" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span> CSV
            </button>
            <button wire:click="exportPdf" class="px-4 py-2 bg-red-50 text-red-600 dark:bg-red-900/20 border border-red-200 dark:border-red-800 dark:text-red-400 rounded-lg text-sm font-medium hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-900/40 transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">picture_as_pdf</span> PDF
            </button>
        </div>
    </div>

  <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-800 shadow-sm">
    <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <nav class="flex items-center gap-1 text-sm font-medium overflow-x-auto">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['income_statement'=>'Laba Rugi','cash_flow'=>'Arus Kas','expense_detail'=>'Detail Beban','top_revenue'=>'Top Revenue','ar_aging'=>'AR Aging']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'income_statement'): ?>
      <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
            <div class="font-semibold text-slate-900 dark:text-slate-100">Income Statement</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">Periode <?php echo e($summary['period_label'] ?? date('F Y')); ?></div>
          </div>
          <table class="w-full text-sm">
            <tbody>
              <tr class="bg-slate-50 dark:bg-slate-900/40 font-semibold"><td class="px-4 py-2.5 text-slate-900 dark:text-slate-100" colspan="2">PENDAPATAN (Revenue)</td></tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($incomeStatement['revenue'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr><td class="px-8 py-2 text-slate-700 dark:text-slate-200"><?php echo e($line['label']); ?></td><td class="px-4 py-2 text-right font-mono text-slate-700 dark:text-slate-200"><?php echo e($fmt($line['amount'])); ?></td></tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              <tr class="border-t-2 border-slate-200 dark:border-slate-600"><td class="px-4 py-2.5 font-bold text-slate-900 dark:text-slate-100">Total Pendapatan</td><td class="px-4 py-2.5 text-right font-mono font-bold text-blue-700 dark:text-blue-300"><?php echo e($fmt($incomeStatement['total_revenue'] ?? 0)); ?></td></tr>

              <tr class="bg-slate-50 dark:bg-slate-900/40 font-semibold"><td class="px-4 py-2.5 text-slate-900 dark:text-slate-100" colspan="2">HARGA POKOK PENJUALAN (COGS)</td></tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($incomeStatement['cogs'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr><td class="px-8 py-2 text-slate-700 dark:text-slate-200"><?php echo e($line['label']); ?></td><td class="px-4 py-2 text-right font-mono text-red-600 dark:text-red-300">(<?php echo e($fmt($line['amount'])); ?>)</td></tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              <tr class="border-t-2 border-slate-200 dark:border-slate-600"><td class="px-4 py-2.5 font-bold text-slate-900 dark:text-slate-100">Gross Profit</td><td class="px-4 py-2.5 text-right font-mono font-bold text-emerald-700 dark:text-emerald-300"><?php echo e($fmt($incomeStatement['gross_profit'] ?? 0)); ?></td></tr>
              <tr><td class="px-4 py-1.5 text-xs text-slate-500 dark:text-slate-400">Gross Margin</td><td class="px-4 py-1.5 text-right font-mono text-xs text-emerald-700 dark:text-emerald-300"><?php echo e($pct($incomeStatement['gross_margin_pct'] ?? 0)); ?></td></tr>

              <tr class="bg-slate-50 dark:bg-slate-900/40 font-semibold"><td class="px-4 py-2.5 text-slate-900 dark:text-slate-100" colspan="2">BEBAN OPERASIONAL (OPEX)</td></tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($incomeStatement['opex'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr><td class="px-8 py-2 text-slate-700 dark:text-slate-200"><?php echo e($line['label']); ?></td><td class="px-4 py-2 text-right font-mono text-red-600 dark:text-red-300">(<?php echo e($fmt($line['amount'])); ?>)</td></tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              <tr class="border-t border-slate-200 dark:border-slate-700"><td class="px-4 py-2.5 font-bold text-slate-900 dark:text-slate-100">EBITDA</td><td class="px-4 py-2.5 text-right font-mono font-bold"><?php echo e($fmt($incomeStatement['ebitda'] ?? 0)); ?></td></tr>
              <tr><td class="px-8 py-2 text-slate-700 dark:text-slate-200">Depresiasi & Amortisasi</td><td class="px-4 py-2 text-right font-mono text-red-600 dark:text-red-300">(<?php echo e($fmt($incomeStatement['depreciation'] ?? 0)); ?>)</td></tr>
              <tr class="border-t-2 border-slate-200 dark:border-slate-600"><td class="px-4 py-2.5 font-bold text-slate-900 dark:text-slate-100">EBIT (Laba Usaha)</td><td class="px-4 py-2.5 text-right font-mono font-bold"><?php echo e($fmt($incomeStatement['ebit'] ?? 0)); ?></td></tr>
              <tr><td class="px-8 py-2 text-slate-700 dark:text-slate-200">Pajak (Estimasi 11%)</td><td class="px-4 py-2 text-right font-mono text-red-600 dark:text-red-300">(<?php echo e($fmt($incomeStatement['tax'] ?? 0)); ?>)</td></tr>
              <tr class="bg-emerald-50 dark:bg-emerald-900/20 border-t-2 border-emerald-200 dark:border-emerald-800"><td class="px-4 py-3 font-bold text-emerald-900 dark:text-emerald-100">LABA BERSIH (Net Income)</td><td class="px-4 py-3 text-right font-mono font-bold text-emerald-700 dark:text-emerald-300 text-lg"><?php echo e($fmt($incomeStatement['net_income'] ?? 0)); ?></td></tr>
              <tr><td class="px-4 py-1.5 text-xs text-emerald-700 dark:text-emerald-400">Net Margin</td><td class="px-4 py-1.5 text-right font-mono text-xs text-emerald-700 dark:text-emerald-400"><?php echo e($pct($incomeStatement['net_margin_pct'] ?? 0)); ?></td></tr>
            </tbody>
          </table>
        </div>

        <div class="space-y-4">
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Ringkasan Kinerja</div>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-3"><div class="text-xs text-blue-700 dark:text-blue-400">Pendapatan</div><div class="font-mono font-bold text-blue-800 dark:text-blue-200 mt-0.5"><?php echo e($fmt($summary['revenue'] ?? 0)); ?></div></div>
              <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 p-3"><div class="text-xs text-amber-700 dark:text-amber-400">Total Beban</div><div class="font-mono font-bold text-amber-800 dark:text-amber-200 mt-0.5"><?php echo e($fmt(($summary['cogs_total']??0)+($summary['opex_total']??0)+($summary['tax']??0))); ?></div></div>
              <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 p-3 col-span-2"><div class="text-xs text-emerald-700 dark:text-emerald-400">Laba Bersih</div><div class="font-mono font-bold text-emerald-800 dark:text-emerald-200 mt-0.5 text-lg"><?php echo e($fmt($summary['net_income'] ?? 0)); ?></div></div>
              <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">Gross Margin</div><div class="font-mono font-bold text-sm text-slate-800 dark:text-slate-100"><?php echo e($pct($summary['gross_margin'] ?? 0)); ?></div></div>
              <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">Net Margin</div><div class="font-mono font-bold text-sm text-slate-800 dark:text-slate-100"><?php echo e($pct($summary['net_margin'] ?? 0)); ?></div></div>
            </div>
          </div>
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Breakdown Pendapatan (Bar)</div>
            <div class="space-y-2 text-xs">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $incomeStatement['revenue'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                  $revTotal = max(1, (float)($incomeStatement['total_revenue'] ?? 1));
                  $p = min(100, round(((float)($line['amount'] ?? 0) / $revTotal) * 100, 1));
                ?>
                <div>
                  <div class="flex justify-between mb-1 text-slate-700 dark:text-slate-200"><span><?php echo e($line['label']); ?></span><span class="font-mono"><?php echo e($p); ?>%</span></div>
                  <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full" style="width:<?php echo e($p); ?>%"></div></div>
                </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    <?php elseif($activeTab === 'cash_flow'): ?>
      <div class="p-4">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
          <div class="xl:col-span-2 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30">
              <div class="font-semibold text-slate-900 dark:text-slate-100">Cash Flow Statement</div>
              <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($summary['period_label'] ?? date('F Y')); ?></div>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['operating'=>'Aktivitas Operasional','investing'=>'Aktivitas Investasi','financing'=>'Aktivitas Pendanaan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div>
                  <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900/40 font-semibold text-slate-900 dark:text-slate-100 flex justify-between">
                    <span><?php echo e($l); ?></span>
                    <span class="font-mono <?php echo e((($cashFlow[$k]['net'] ?? 0) >= 0) ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-300'); ?>">
                      <?php echo e((($cashFlow[$k]['net'] ?? 0) >= 0 ? '' : '(').$fmt(abs($cashFlow[$k]['net'] ?? 0)).((($cashFlow[$k]['net'] ?? 0) >= 0) ? '' : ')')); ?>

                    </span>
                  </div>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cashFlow[$k]['lines'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ln): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="px-8 py-2 flex justify-between text-slate-700 dark:text-slate-200">
                      <span><?php echo e($ln['label']); ?></span>
                      <span class="font-mono <?php echo e(($ln['amount'] ?? 0) >= 0 ? 'text-slate-700 dark:text-slate-200' : 'text-red-600 dark:text-red-300'); ?>">
                        <?php echo e(($ln['amount'] ?? 0) >= 0 ? '' : '('); ?><?php echo e($fmt(abs($ln['amount'] ?? 0))); ?><?php echo e(($ln['amount'] ?? 0) >= 0 ? '' : ')'); ?>

                      </span>
                    </div>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              <div class="px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border-t-2 border-emerald-200 dark:border-emerald-800 flex justify-between font-bold">
                <span class="text-emerald-900 dark:text-emerald-100">Kenaikan Bersih Kas</span>
                <span class="font-mono text-emerald-700 dark:text-emerald-300 text-lg"><?php echo e($fmt($cashFlow['total_net'] ?? 0)); ?></span>
              </div>
            </div>
          </div>

          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Cash Flow Bulanan (12M)</div>
            <div class="space-y-2">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $monthlyCashflow ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="text-xs">
                  <div class="flex justify-between mb-1 text-slate-700 dark:text-slate-200"><span><?php echo e($m['label']); ?></span><span class="font-mono"><?php echo e($fmt($m['value'])); ?></span></div>
                  <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden flex">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($m['value'] >= 0): ?>
                      <div class="h-full bg-emerald-500 rounded-full" style="width:<?php echo e(min(100, max(3, abs($m['pct'] ?? 10)))); ?>%"></div>
                    <?php else: ?>
                      <div class="h-full bg-red-500 rounded-full ml-auto" style="width:<?php echo e(min(100, max(3, abs($m['pct'] ?? 10)))); ?>%"></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </div>
                </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    <?php elseif($activeTab === 'expense_detail'): ?>
      <div class="p-4">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
          <div class="xl:col-span-2 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Detail Beban</div>
            <table class="w-full text-sm">
              <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kategori</th>
                  <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">COGS</th>
                  <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">OPEX</th>
                  <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total</th>
                  <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">%</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $expenseBreakdown ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/30">
                    <td class="px-4 py-2 text-slate-700 dark:text-slate-200"><?php echo e($e['label']); ?></td>
                    <td class="px-4 py-2 text-right font-mono text-slate-600 dark:text-slate-300"><?php echo e($fmt($e['cogs'] ?? 0)); ?></td>
                    <td class="px-4 py-2 text-right font-mono text-slate-600 dark:text-slate-300"><?php echo e($fmt($e['opex'] ?? 0)); ?></td>
                    <td class="px-4 py-2 text-right font-mono font-semibold text-slate-800 dark:text-slate-100"><?php echo e($fmt($e['total'] ?? 0)); ?></td>
                    <td class="px-4 py-2 text-right font-mono text-xs text-slate-500 dark:text-slate-400"><?php echo e(number_format($e['pct'] ?? 0, 1)); ?>%</td>
                  </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($expenseBreakdown)): ?>
                  <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">Detail beban belum tersedia.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="space-y-3">
            <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
              <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Beban vs Anggaran</div>
              <div class="h-48 flex items-end justify-around gap-1">
                <?php $maxBudget = collect($expenseBreakdown ?? [])->flatMap(fn($e)=>[$e['total']??0,$e['budget']??0])->max() ?: 1; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $expenseBreakdown ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end">
                    <div class="w-full flex gap-0.5 items-end h-full">
                      <div class="flex-1 bg-blue-500 rounded-t" style="height:<?php echo e(max(5, (($e['total']??0)/$maxBudget)*100)); ?>%" title="Realisasi"></div>
                      <div class="flex-1 bg-slate-200 dark:bg-slate-600 rounded-t" style="height:<?php echo e(max(5, (($e['budget']??0)/$maxBudget)*100)); ?>%" title="Budget"></div>
                    </div>
                    <div class="text-[9px] text-slate-500 dark:text-slate-400 truncate w-full text-center"><?php echo e(substr($e['label'] ?? '-', 0, 10)); ?></div>
                  </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              </div>
              <div class="mt-2 flex gap-3 text-[10px] text-slate-600 dark:text-slate-400">
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 bg-blue-500 rounded-sm inline-block"></span>Realisasi</span>
                <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 bg-slate-200 dark:bg-slate-600 rounded-sm inline-block"></span>Anggaran</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    <?php elseif($activeTab === 'top_revenue'): ?>
      <div class="p-4 grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Top 15 Pelanggan</div>
          <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
              <tr><th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">#</th><th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Pelanggan</th><th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Revenue</th><th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">%</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $topRevenue['rows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>$c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/30">
                  <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-mono"><?php echo e($i+1); ?></td>
                  <td class="px-4 py-2"><div class="font-medium text-slate-800 dark:text-slate-100"><?php echo e($c['name']); ?></div><div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($c['code'] ?? ''); ?> · <?php echo e($c['package'] ?? ''); ?></div></td>
                  <td class="px-4 py-2 text-right font-mono font-semibold text-slate-800 dark:text-slate-100"><?php echo e($fmt($c['amount'])); ?></td>
                  <td class="px-4 py-2 text-right font-mono text-xs text-slate-500 dark:text-slate-400"><?php echo e(number_format($c['pct'] ?? 0, 1)); ?>%</td>
                </tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Top Sales Reseller</div>
          <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
              <tr><th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">#</th><th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Sales / Reseller</th><th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Kontribusi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $topSales ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>$s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/30">
                  <td class="px-4 py-2 text-slate-500 dark:text-slate-400 font-mono"><?php echo e($i+1); ?></td>
                  <td class="px-4 py-2"><div class="font-medium text-slate-800 dark:text-slate-100"><?php echo e($s['name']); ?></div><div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($s['type'] ?? 'Sales'); ?> · <?php echo e($s['customer_count'] ?? 0); ?> plg</div></td>
                  <td class="px-4 py-2 text-right font-mono font-semibold text-slate-800 dark:text-slate-100"><?php echo e($fmt($s['amount'])); ?></td>
                </tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($topSales)): ?>
                <tr><td colspan="3" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">Data sales/reseller belum tersedia.</td></tr>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif($activeTab === 'ar_aging'): ?>
      <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Aging Piutang (AR Aging) - Berdasarkan Jatuh Tempo</div>
          <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Bucket (hari over)</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Jumlah Invoice</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Outstanding</th>
                <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Komposisi</th>
                <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Tindakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
              <?php
                $colors = [
                  '0-30' => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'Tetap Sopan'],
                  '31-60' => ['bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300', 'WA + Telepon'],
                  '61-90' => ['bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300', 'Kunjungan Lapangan'],
                  '91-180' => ['bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300', 'Surat Peringatan'],
                  '>180' => ['bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', 'Isolir / SP3 / Putus'],
                ];
                $grand = 0;
                foreach($arAging['rows'] ?? [] as $a) $grand += (float)($a['outstanding'] ?? 0);
              ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $arAging['rows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php [$cls, $action] = $colors[$a['bucket']] ?? ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', '-']; $pct = $grand ? min(100, round(((float)$a['outstanding'] / $grand)*100,1)) : 0; ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/30">
                  <td class="px-4 py-2"><span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full <?php echo e($cls); ?>"><?php echo e($a['bucket']); ?> hari</span></td>
                  <td class="px-4 py-2 text-right font-mono text-slate-700 dark:text-slate-200"><?php echo e(number_format($a['count'] ?? 0, 0, ',', '.')); ?></td>
                  <td class="px-4 py-2 text-right font-mono font-semibold text-slate-800 dark:text-slate-100"><?php echo e($fmt($a['outstanding'] ?? 0)); ?></td>
                  <td class="px-4 py-2 w-44"><div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500" style="width:<?php echo e($pct); ?>%"></div></div><div class="text-[10px] font-mono mt-0.5 text-slate-500 dark:text-slate-400"><?php echo e($pct); ?>%</div></td>
                  <td class="px-4 py-2 text-xs text-slate-600 dark:text-slate-300"><?php echo e($action); ?></td>
                </tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              <tr class="bg-slate-50 dark:bg-slate-900/40 border-t-2 border-slate-300 dark:border-slate-600 font-bold">
                <td class="px-4 py-2.5 text-slate-900 dark:text-slate-100">TOTAL PIUTANG</td>
                <td class="px-4 py-2.5 text-right font-mono text-slate-800 dark:text-slate-100"><?php echo e(number_format(collect($arAging['rows'] ?? [])->sum('count') ?? 0)); ?></td>
                <td class="px-4 py-2.5 text-right font-mono text-red-700 dark:text-red-300"><?php echo e($fmt($grand)); ?></td>
                <td class="px-4 py-2.5"></td><td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">100%</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="space-y-4">
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Komposisi AR Aging</div>
            <div class="space-y-2">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $arAging['rows'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php $pct = $grand ? min(100, round(((float)$a['outstanding'] / $grand)*100,1)) : 0; ?>
                <div>
                  <div class="flex justify-between mb-1 text-xs text-slate-700 dark:text-slate-200"><span><?php echo e($a['bucket']); ?> hari</span><span class="font-mono"><?php echo e($pct); ?>% · <?php echo e($fmt($a['outstanding']??0)); ?></span></div>
                  <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <?php
                      $bar = match($a['bucket']) {
                        '0-30' => 'from-emerald-400 to-emerald-600',
                        '31-60' => 'from-blue-400 to-blue-600',
                        '61-90' => 'from-amber-400 to-amber-600',
                        '91-180' => 'from-orange-400 to-orange-600',
                        default => 'from-red-400 to-red-600',
                      };
                    ?>
                    <div class="h-full bg-gradient-to-r <?php echo e($bar); ?> rounded-full" style="width:<?php echo e($pct); ?>%"></div>
                  </div>
                </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
          </div>
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Rekomendasi Kolektibilitas</div>
            <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300 list-disc pl-4">
              <li>Fokus tagih bucket 31-60 hari untuk menekan risiko ke bucket lebih buruk.</li>
              <li>Bucket >180 hari: evaluasi write-off atau restruktur (DP + cicilan).</li>
              <li>Prosentase AR >90 hari ideal < 10% dari total revenue bulanan.</li>
            </ul>
          </div>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\keuangan\laba-rugi\index.blade.php ENDPATH**/ ?>