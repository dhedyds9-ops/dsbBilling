<?php
  $sortIcon = function($field) {
    $dir = $this->sortField === $field ? ($this->sortDirection === 'asc' ? '↑' : '↓') : '';
    return $dir ? " <span class='text-blue-600'>{$dir}</span>" : '';
  };
  $statusBadge = function($status, $since) {
    if (is_string($since) && $since !== '') {
      try { $d = \Illuminate\Support\Carbon::parse($since); } catch (\Throwable $e) { $d = null; }
    } else {
      $d = $since instanceof \DateTimeInterface ? \Illuminate\Support\Carbon::instance($since) : null;
    }
    $days = $d ? now()->diffInDays($d) : 0;
    $cls = $days >= 14 ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
         : ($days >= 7 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'
         : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200');
    return "<span class='inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full {$cls}'>" . ($days > 0 ? "Isolir {$days}h" : 'Isolir') . "</span>";
  };
?>
<div>
  <!-- Header -->
  <div class="mb-6 flex flex-col md:flex-row md:items-start md:justify-between gap-4">
      <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center text-red-600 dark:text-red-400 font-bold text-sm">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">wifi_off</span>
              </div>
              Pelanggan Terisolir
          </h1>
          <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm ml-[3.25rem]">Daftar pelanggan yang layanannya saat ini ditangguhkan akibat tunggakan pembayaran.</p>
      </div>
      <div class="flex items-center gap-3">
          <button wire:click="exportCsv" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/50 shadow-sm flex items-center gap-2 transition-colors">
              <span class="material-symbols-outlined notranslate text-[18px]" translate="no">download</span>
              Export Data
          </button>
      </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="cursor-pointer relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-md bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 transition-all">
          <div class="p-4 pt-5">
              <h3 class="text-xs font-bold text-red-500 dark:text-red-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                  <span class="material-symbols-outlined notranslate text-[16px]" translate="no">block</span> Total Terisolir
              </h3>
              <div class="text-2xl font-black text-red-700 dark:text-red-300 mb-1"><?php echo e(number_format($this->summary['total'] ?? 0)); ?></div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Semua layanan yang dihentikan sementara</div>
          </div>
      </div>

      <div class="cursor-pointer relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-md bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 transition-all">
          <div class="p-4 pt-5">
              <h3 class="text-xs font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                  <span class="material-symbols-outlined notranslate text-[16px]" translate="no">schedule</span> Isolir Hari Ini
              </h3>
              <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mb-1"><?php echo e(number_format($this->summary['today'] ?? 0)); ?></div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Pelanggan yang baru terisolir dalam 24 jam</div>
          </div>
      </div>

      <div class="cursor-pointer relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 shadow-md bg-gradient-to-br from-slate-50 to-white dark:from-slate-800 dark:to-slate-900 transition-all">
          <div class="p-4 pt-5">
              <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                  <span class="material-symbols-outlined notranslate text-[16px]" translate="no">hourglass_bottom</span> Terlama Isolir
              </h3>
              <div class="text-xl font-black text-slate-700 dark:text-slate-300 mb-1 truncate"><?php echo e($this->summary['terlama'] ?? '-'); ?></div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Rekor waktu isolir terlama yang masih aktif</div>
          </div>
      </div>
  </div>

  <!-- Main Content Card (Toolbar + Table) -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
      <!-- Toolbar -->
      <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4 justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
          <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
              <div class="relative w-full sm:w-64">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
                  </div>
                  <input wire:model.live.debounce.300ms="search" type="text" 
                      class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100" 
                      placeholder="Cari nama pelanggan...">
              </div>
          </div>

          <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($this->selected) > 0): ?>
                  <div class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 rounded-lg border border-indigo-100 dark:border-indigo-800/50">
                      <span class="text-xs font-medium text-indigo-700 dark:text-indigo-400"><?php echo e(count($this->selected)); ?> terpilih</span>
                      <div class="h-4 w-px bg-indigo-200 dark:bg-indigo-700 mx-1"></div>
                      <button wire:click="handleBulkAction('activate', <?php echo e(json_encode($this->selected)); ?>)" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 px-2 py-1 hover:bg-emerald-50 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 rounded transition-colors">Aktifkan</button>
                      <button wire:click="handleBulkAction('send-wa', <?php echo e(json_encode($this->selected)); ?>)" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 px-2 py-1 hover:bg-indigo-50 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 rounded transition-colors">Kirim WA</button>
                  </div>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <select wire:model.live="perPage" class="py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                  <option value="20">20 / halaman</option>
                  <option value="50">50 / halaman</option>
                  <option value="all">Semua</option>
              </select>
          </div>
      </div>

      <div class="overflow-x-auto relative">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->loading): ?>
      <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 dark:bg-slate-900/60 backdrop-blur-[1px]">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border border-blue-100 dark:border-blue-800">
          <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
          Memuat data isolir...
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
                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
            </label>
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('username')">
            Username<?php echo $sortIcon('username'); ?>

          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('customer_name')">
            Nama Pelanggan<?php echo $sortIcon('customer_name'); ?>

          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('package_name')">
            Paket<?php echo $sortIcon('package_name'); ?>

          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Router
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('status')">
            Status<?php echo $sortIcon('status'); ?>

          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('alasan')">
            Alasan<?php echo $sortIcon('alasan'); ?>

          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('since_isolir')">
            Sejak Isolir<?php echo $sortIcon('since_isolir'); ?>

          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('last_due_date')">
            Jatuh Tempo Terakhir<?php echo $sortIcon('last_due_date'); ?>

          </th>
          <th class="w-32 px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
          <?php
            $displayId = $row->display_id ?? "{$row->source_type}-{$row->id}";
            $phone = $row->customer_phone ?? '';
            $name = e($row->customer_name ?? '');
          ?>
          <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/40 transition-colors">
            <td class="px-3 py-2">
              <label class="inline-flex items-center">
                <input type="checkbox" value="<?php echo e($displayId); ?>" wire:model.live="selected"
                  class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
              </label>
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400 uppercase"><?php echo e(strtoupper($row->source_type ?? '-')); ?></span>
                <span class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e($row->username); ?></span>
              </div>
            </td>
            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
              <div><?php echo e($row->customer_name ?? '-'); ?></div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($phone): ?>
                <div class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400"><?php echo e($phone); ?></div>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
              <?php echo e($row->package_name ?? '-'); ?>

            </td>
            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
              <?php echo e($row->router_name ?? '-'); ?>

            </td>
            <td class="px-3 py-2"><?php echo $statusBadge($row->status ?? 'suspended', $row->since_isolir ?? null); ?></td>
            <td class="px-3 py-2 text-slate-600 dark:text-slate-300 text-xs">
              <?php echo e($row->alasan ?? 'Non pembayaran'); ?>

            </td>
            <td class="px-3 py-2 text-slate-500 dark:text-slate-400 text-xs">
              <?php echo e($row->since_isolir ? (is_string($row->since_isolir) ? $row->since_isolir : \Illuminate\Support\Carbon::parse($row->since_isolir)->format('d/m/Y H:i')) : '-'); ?>

            </td>
            <td class="px-3 py-2 text-slate-500 dark:text-slate-400 text-xs">
              <?php echo e($row->last_due_date ? (is_string($row->last_due_date) ? $row->last_due_date : \Illuminate\Support\Carbon::parse($row->last_due_date)->format('d/m/Y')) : '-'); ?>

            </td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-1">
                <button wire:click="confirmRowAction('activate', '<?php echo e($displayId); ?>', '<?php echo e($phone); ?>', '<?php echo e($name); ?>')" title="Aktifkan"
                  class="p-1.5 rounded-md text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:bg-emerald-900/30 dark:text-slate-400 dark:hover:bg-emerald-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                </button>
                <button wire:click="confirmRowAction('perpanjang', '<?php echo e($displayId); ?>', '<?php echo e($phone); ?>', '<?php echo e($name); ?>')" title="Perpanjang"
                  class="p-1.5 rounded-md text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:bg-blue-900/30 dark:text-slate-400 dark:hover:bg-blue-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <button wire:click="confirmRowAction('ganti-paket', '<?php echo e($displayId); ?>', '<?php echo e($phone); ?>', '<?php echo e($name); ?>')" title="Ganti Paket"
                  class="p-1.5 rounded-md text-slate-500 hover:text-purple-600 hover:bg-purple-50 dark:text-slate-400 dark:hover:bg-purple-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/></svg>
                </button>
                <button wire:click="confirmRowAction('wa', '<?php echo e($displayId); ?>', '<?php echo e($phone); ?>', '<?php echo e($name); ?>')" title="Kirim WA"
                  class="p-1.5 rounded-md text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:bg-emerald-900/30 dark:text-slate-400 dark:hover:bg-emerald-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
              </div>
            </td>
          </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
          <tr>
            <td colspan="10" class="px-3 py-16 text-center">
              <div class="inline-flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500 dark:text-slate-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/></svg>
                <div class="text-sm font-medium">Tidak ada data pelanggan terisolir</div>
                <div class="text-xs opacity-80">Bagus! Semua pelanggan dalam kondisi aktif.</div>
              </div>
            </td>
          </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
    <?php echo e($rows->links('livewire::simple-tailwind')); ?>

  </div>
  </div> <!-- Close Main Content Card -->

  <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showPerpanjangModal): ?>
    <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 dark:text-slate-100">Beri Kelonggaran Waktu (Janji Bayar)</h3>
          <button wire:click="closeModals" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="grid grid-cols-1 gap-3 px-5 py-4">
          <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700">
            <div class="text-xs text-slate-500 dark:text-slate-400">Pelanggan</div>
            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100"><?php echo e($this->selectedUser['name'] ?? '-'); ?></div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Janji Bayar (Hari)</label>
            <select wire:model.live="formParams.grace_days" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2 dark:bg-slate-900 dark:text-slate-100">
              <option value="1">1 Hari</option>
              <option value="2">2 Hari</option>
              <option value="3">3 Hari</option>
              <option value="5">5 Hari</option>
              <option value="7">7 Hari</option>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formParams.grace_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Catatan</label>
            <textarea wire:model.live="formParams.notes" rows="2" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2 dark:bg-slate-900 dark:text-slate-100"></textarea>
          </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-2 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl">
          <button wire:click="closeModals" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-white dark:bg-slate-800 dark:hover:bg-slate-700">Batal</button>
          <button wire:click="submitPerpanjang" class="px-4 py-1.5 text-sm font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Simpan Perpanjangan</button>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showGantiPaketModal): ?>
    <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 dark:text-slate-100">Ganti Paket Pelanggan</h3>
          <button wire:click="closeModals" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="grid grid-cols-1 gap-3 px-5 py-4">
          <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700">
            <div class="text-xs text-slate-500 dark:text-slate-400">Pelanggan</div>
            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100"><?php echo e($this->selectedUser['name'] ?? '-'); ?></div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Pilih Paket Baru</label>
            <select wire:model.live="formParams.package_id" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2 dark:bg-slate-900 dark:text-slate-100">
              <option value="">Pilih Paket</option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->filterOptions['packages'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Catatan</label>
            <textarea wire:model.live="formParams.notes" rows="2" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2 dark:bg-slate-900 dark:text-slate-100"></textarea>
          </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-2 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl">
          <button wire:click="closeModals" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-white dark:bg-slate-800 dark:hover:bg-slate-700">Batal</button>
          <button wire:click="submitGantiPaket" class="px-4 py-1.5 text-sm font-medium rounded-md bg-purple-600 hover:bg-purple-700 text-white shadow-sm">Ganti Paket</button>
        </div>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\pelanggan\isolir\index.blade.php ENDPATH**/ ?>