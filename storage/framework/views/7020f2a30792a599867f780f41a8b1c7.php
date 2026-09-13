<?php $__env->startSection('page_title'); ?>
<div class="flex items-center gap-3">
  <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
  <span class="text-lg">GenieACS (TR-069)</span>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_actions'); ?>
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($actions)): ?>
    <?php echo e($actions); ?>

  <?php else: ?>
    <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
        Dashboard Utama
    </a>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<div class="mb-5 border-b border-slate-200 dark:border-slate-700">
  <nav class="flex items-center gap-6 overflow-x-auto">
    <?php
      $tabs = [
          ['route' => 'acs.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
          ['route' => 'acs.devices.index', 'label' => 'Devices', 'icon' => 'router'],
          ['route' => 'acs.tasks.index', 'label' => 'Task Queue', 'icon' => 'pending_actions'],
          ['route' => 'acs.alarms.index', 'label' => 'Alarms', 'icon' => 'warning'],
          ['route' => 'acs.firmware.index', 'label' => 'Firmware', 'icon' => 'system_update_alt'],
          ['route' => 'acs.settings', 'label' => 'Pengaturan', 'icon' => 'settings'],
      ];
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
      <?php
        $isActive = request()->routeIs(explode('.', $tab['route'])[0] . '.' . explode('.', $tab['route'])[1] . '.*') || request()->routeIs($tab['route']);
      ?>
      <a href="<?php echo e(route($tab['route'])); ?>" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm inline-flex items-center gap-2 transition-colors <?php echo e($isActive ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-500' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:border-slate-600 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-600'); ?>">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px"><?php echo e($tab['icon']); ?></span>
        <?php echo e($tab['label']); ?>

      </a>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
  </nav>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\acs\_tabs.blade.php ENDPATH**/ ?>