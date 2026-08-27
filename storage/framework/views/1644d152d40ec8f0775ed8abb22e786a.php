<div>
  <div class="px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan Payment Gateway</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Midtrans, Xendit, Duitku, Tripay, Manual Bank Transfer & e-Wallet</div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savedStatus): ?>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Tersimpan
        </span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <button wire:click="save" class="px-4 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
        Simpan Semua
      </button>
    </div>
  </div>

  <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <nav class="flex items-center gap-1 text-sm font-medium overflow-x-auto flex-wrap">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['midtrans'=>'Midtrans','xendit'=>'Xendit','duitku'=>'Duitku','tripay'=>'Tripay','manual'=>'Bank Manual','ewallet'=>'e-Wallet Manual','general'=>'Umum']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <button wire:click="setActiveTab('<?php echo e($k); ?>')" class="whitespace-nowrap px-3 py-1.5 rounded-md transition-colors <?php echo e($activeTab===$k ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'); ?>"><?php echo e($l); ?></button>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </nav>
  </div>

  <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <form class="xl:col-span-2 space-y-4 text-sm">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'general'): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Pengaturan Umum Payment</div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Mode</label><select wire:model="general.mode" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm"><option value="sandbox">Sandbox</option><option value="production">Production</option></select></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Default Expiry (jam)</label><input type="number" wire:model="general.default_expiry_hours" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Invoice Prefix</label><input type="text" wire:model="general.invoice_prefix" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Min. Topup (Rp)</label><input type="number" wire:model="general.minimum_topup_min_amount" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Success URL</label><input type="text" wire:model="general.success_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Pending URL</label><input type="text" wire:model="general.pending_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Error URL</label><input type="text" wire:model="general.error_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Base URL (untuk Callback / Notification)</label><input type="url" wire:model="general.webhook_base_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs" placeholder="https://billing.domain.com/api/v1/pg/"></div>
            <div class="md:col-span-2 flex flex-wrap items-center gap-6">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="general.auto_confirm_payment" class="rounded border-slate-300"> Auto-confirm on Valid Callback</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="general.send_receipt_on_success" class="rounded border-slate-300"> Kirim WA/Email Tanda Terima</label>
            </div>
          </div>
        </div>

      <?php elseif($activeTab === 'midtrans'): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Midtrans (Pembayaran Indonesia)</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="midtrans.enabled" class="rounded border-slate-300"> Aktifkan Midtrans</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Merchant ID</label><input type="text" wire:model="midtrans.merchant_id" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Server Key Sandbox</label><input type="password" wire:model="midtrans.server_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Server Key Production</label><input type="password" wire:model="midtrans.server_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Client Key Sandbox</label><input type="text" wire:model="midtrans.client_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Client Key Production</label><input type="text" wire:model="midtrans.client_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry (satuan)</label><select wire:model="midtrans.expiry_unit" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm"><option value="minute">Menit</option><option value="hour">Jam</option><option value="day">Hari</option></select></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Durasi Expiry</label><input type="number" wire:model="midtrans.expiry_duration" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2 flex flex-wrap items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="midtrans.enable_3ds" class="rounded border-slate-300"> Enable 3DS Credit Card</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="midtrans.custom_expiry" class="rounded border-slate-300"> Custom Expiry</label>
            </div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Enabled Payment Channels</div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['bank_transfer'=>'Bank Transfer','credit_card'=>'Credit Card','ewallet'=>'e-Wallet','qris'=>'QRIS','direct_debit'=>'Direct Debit','store'=>'Indomaret/Alfamart','cardless_credit'=>'Cardless (Kredivo/Akulaku)']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-2 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="midtrans.enabled_channels.<?php echo e($k); ?>" class="rounded border-slate-300"> <?php echo e($l); ?></label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      <?php elseif($activeTab === 'xendit'): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Xendit Payment Gateway</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="xendit.enabled" class="rounded border-slate-300"> Aktifkan Xendit</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Secret Key Sandbox</label><input type="password" wire:model="xendit.secret_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Secret Key Production</label><input type="password" wire:model="xendit.secret_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Public Key Sandbox</label><input type="text" wire:model="xendit.public_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Public Key Production</label><input type="text" wire:model="xendit.public_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Verify Token</label><input type="password" wire:model="xendit.webhook_token" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Virtual Account Banks</div>
              <div class="grid grid-cols-3 gap-1.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_keys($xendit['va_banks']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="xendit.va_banks.<?php echo e($b); ?>" class="rounded border-slate-300"> <?php echo e($b); ?></label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              </div>
            </div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">e-Wallet Providers</div>
              <div class="grid grid-cols-3 gap-1.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_keys($xendit['ewallet_providers']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="xendit.ewallet_providers.<?php echo e($p); ?>" class="rounded border-slate-300"> <?php echo e($p); ?></label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              </div>
            </div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Retail Outlets</div>
              <div class="grid grid-cols-3 gap-1.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_keys($xendit['retail_providers']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="xendit.retail_providers.<?php echo e($p); ?>" class="rounded border-slate-300"> <?php echo e($p); ?></label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      <?php elseif($activeTab === 'duitku'): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Duitku (PT. Dukita Indonesia)</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="duitku.enabled" class="rounded border-slate-300"> Aktifkan Duitku</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Merchant Code</label><input type="text" wire:model="duitku.merchant_code" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry Period (menit)</label><input type="number" wire:model="duitku.expiry_period" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Sandbox</label><input type="password" wire:model="duitku.api_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Production</label><input type="password" wire:model="duitku.api_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Callback URL</label><input type="text" wire:model="duitku.callback_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Return URL</label><input type="text" wire:model="duitku.return_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs"></div>
          </div>
        </div>

      <?php elseif($activeTab === 'tripay'): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Tripay (PT. Lini Masa Triyas)</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="tripay.enabled" class="rounded border-slate-300"> Aktifkan Tripay</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Merchant Code</label><input type="text" wire:model="tripay.merchant_code" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Private Key</label><input type="password" wire:model="tripay.private_key" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Sandbox</label><input type="password" wire:model="tripay.api_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Production</label><input type="password" wire:model="tripay.api_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry Hours</label><input type="number" wire:model="tripay.expiry_hours" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Aktif Channels (Tripay Kode)</div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_keys($tripay['enabled_channels']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="tripay.enabled_channels.<?php echo e($p); ?>" class="rounded border-slate-300"> <?php echo e($p); ?></label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      <?php elseif($activeTab === 'manual'): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Rekening Bank Manual (Transfer)</span>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="manualBank.enabled" class="rounded border-slate-300"> Aktifkan</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="manualBank.require_attachment" class="rounded border-slate-300"> Wajib bukti transfer</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="manualBank.auto_approve_manual" class="rounded border-slate-300"> Auto-approve</label>
              <button type="button" wire:click="addBankAccount" class="text-xs px-2.5 py-1 rounded bg-blue-600 text-white inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Rekening
              </button>
            </div>
          </div>
          <div class="p-4 grid grid-cols-1 gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $manualBank['accounts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
              <div class="grid grid-cols-12 gap-2 items-center p-2 rounded bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700">
                <div class="col-span-3"><input type="text" wire:model="manualBank.accounts.<?php echo e($i); ?>.bank_name" placeholder="Nama Bank" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
                <div class="col-span-3"><input type="text" wire:model="manualBank.accounts.<?php echo e($i); ?>.account_no" placeholder="No. Rekening" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
                <div class="col-span-5"><input type="text" wire:model="manualBank.accounts.<?php echo e($i); ?>.account_holder" placeholder="Atas Nama" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
                <div class="col-span-1 flex items-center gap-1 justify-end">
                  <label class="inline-flex text-xs"><input type="checkbox" wire:model="manualBank.accounts.<?php echo e($i); ?>.enabled" class="rounded border-slate-300"></label>
                  <button type="button" wire:click="removeBankAccount(<?php echo e($i); ?>)" class="p-1 text-slate-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
              </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
          </div>
        </div>

      <?php elseif($activeTab === 'ewallet'): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-slate-900 dark:text-slate-100">e-Wallet Manual (GoPay / OVO / DANA / ShopeePay)</span>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="ewalletManual.require_attachment" class="rounded border-slate-300"> Wajib bukti</label>
              <button type="button" wire:click="addEwallet" class="text-xs px-2.5 py-1 rounded bg-blue-600 text-white inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah
              </button>
            </div>
          </div>
          <div class="p-4 grid grid-cols-1 gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ewalletManual['providers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
              <div class="grid grid-cols-12 gap-2 items-center p-2 rounded bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700">
                <div class="col-span-3"><input type="text" wire:model="ewalletManual.providers.<?php echo e($i); ?>.name" placeholder="GoPay / OVO / dll" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
                <div class="col-span-3"><input type="text" wire:model="ewalletManual.providers.<?php echo e($i); ?>.number" placeholder="No. HP terdaftar" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
                <div class="col-span-5"><input type="text" wire:model="ewalletManual.providers.<?php echo e($i); ?>.holder" placeholder="Atas Nama" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
                <div class="col-span-1 flex items-center gap-1 justify-end">
                  <label class="inline-flex text-xs"><input type="checkbox" wire:model="ewalletManual.providers.<?php echo e($i); ?>.enabled" class="rounded border-slate-300"></label>
                  <button type="button" wire:click="removeEwallet(<?php echo e($i); ?>)" class="p-1 text-slate-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
              </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
          </div>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </form>

    <aside class="space-y-4">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($activeTab, ['midtrans','xendit','tripay'])): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
          <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Test Balance / Info Saldo
          </div>
          <div class="space-y-1.5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab==='midtrans'): ?>
              <button wire:click="testMidtransBalance" class="w-full px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Cek Balance Midtrans
              </button>
            <?php elseif($activeTab==='xendit'): ?>
              <button wire:click="testXenditBalance" class="w-full px-3 py-1.5 text-xs bg-lime-600 hover:bg-lime-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Cek Balance Xendit
              </button>
            <?php elseif($activeTab==='tripay'): ?>
              <button wire:click="testTripayBalance" class="w-full px-3 py-1.5 text-xs bg-rose-600 hover:bg-rose-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Cek Balance Tripay
              </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="flex items-center gap-1.5">
              <input type="text" wire:model="testAmount" placeholder="Test amount (Rp)" class="flex-1 px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono">
            </div>
          </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($balanceInfo): ?>
            <div class="mt-3 p-2 rounded border text-[11px] font-mono whitespace-pre-wrap break-words max-h-40 overflow-auto bg-slate-50 dark:bg-slate-700/40 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200"><?php echo e($balanceInfo); ?></div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Mode PG Aktif</div>
        <div class="space-y-1.5 text-xs">
          <?php
            $pgs = [
              ['midtrans.enabled', 'Midtrans', $midtrans['enabled'] ?? false],
              ['xendit.enabled', 'Xendit', $xendit['enabled'] ?? false],
              ['duitku.enabled', 'Duitku', $duitku['enabled'] ?? false],
              ['tripay.enabled', 'Tripay', $tripay['enabled'] ?? false],
              ['manual.enabled', 'Bank Transfer', $manualBank['enabled'] ?? false],
              ['ewallet', 'e-Wallet Manual', $ewalletManual['enabled'] ?? false],
            ];
          ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pgs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k,$l,$on]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="flex items-center justify-between">
              <span class="text-slate-700 dark:text-slate-200"><?php echo e($l); ?></span>
              <span class="inline-flex items-center gap-1 <?php echo e($on ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-400'); ?>">
                <span class="inline-block w-2 h-2 rounded-full <?php echo e($on ? 'bg-emerald-500' : 'bg-slate-300'); ?>"></span>
                <?php echo e($on ? 'AKTIF' : 'OFF'); ?>

              </span>
            </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Status Simpan</div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savedStatus): ?>
          <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs">Berhasil disimpan pada <?php echo e(now()->format('d/m/Y H:i:s')); ?></div>
        <?php else: ?>
          <div class="text-xs text-slate-500 dark:text-slate-400">Belum ada perubahan disimpan.</div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </aside>
  </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\pengaturan\payment-gateway\index.blade.php ENDPATH**/ ?>