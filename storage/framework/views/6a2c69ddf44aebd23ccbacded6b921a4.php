<div>
  <?php
    ob_start();
  ?>
  <button wire:click="saveGenieAcs" class="px-3 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">
    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
    Simpan Konfigurasi
  </button>
  <?php
    $actions = ob_get_clean();
  ?>

  <?php echo $__env->make('livewire.acs._tabs', ['actions' => $actions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="xl:col-span-2 space-y-4">
      
      <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <div class="text-indigo-600 dark:text-indigo-400 mt-0.5">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">info</span>
          </div>
          <div class="text-sm text-indigo-900 dark:text-indigo-200">
            <h4 class="font-semibold mb-1">Panduan Konfigurasi GenieACS</h4>
            <ul class="list-disc pl-4 space-y-1 text-xs">
              <li><strong>Base URL (UI/API)</strong>: Alamat server GenieACS Anda (biasanya port <strong>7557</strong>).</li>
              <li><strong>File Server URL (FS)</strong>: Alamat server file GenieACS untuk <i>Auto Provisioning</i> dan <i>Firmware Upgrade</i> (biasanya port <strong>7567</strong>).</li>
              <li><strong>Username & Password</strong>: Isikan jika API GenieACS Anda diproteksi. Jika tidak, kosongkan saja.</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100 text-sm">
            Kredensial & URL Server
        </div>
        <div class="p-4 space-y-4">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->has('genieAcsForm')): ?>
            <div class="p-3 text-xs text-rose-700 bg-rose-50 dark:bg-rose-900/20 dark:text-rose-300 border-l-4 border-rose-500"><?php echo e($errors->first('genieAcsForm')); ?></div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Base URL (UI/API) <span class="text-rose-500">*</span></label>
              <input type="text" wire:model="genieAcsForm.base_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 font-mono text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="http://127.0.0.1:7557">
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">File Server URL (FS) <span class="text-rose-500">*</span></label>
              <input type="text" wire:model="genieAcsForm.fs_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 font-mono text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="http://127.0.0.1:7567">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Username API</label>
              <input type="text" wire:model="genieAcsForm.username" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm " placeholder="(Opsional)">
            </div>
            <div x-data="{ show: false }">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Password API</label>
              <div class="relative">
                  <input x-bind:type="show ? 'text' : 'password'" wire:model="genieAcsForm.password" class="w-full px-3 py-2 pr-10 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="(Opsional)">
                  <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                      <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                  </button>
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-3 pt-2">
            <button wire:click="testGenieAcs" type="button" class="px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors inline-flex items-center gap-1.5">
              <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">network_ping</span>
              Test Koneksi
            </button>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
              <input type="checkbox" wire:model="genieAcsForm.ssl_verify" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 "> 
              Verify SSL
            </label>
          </div>
          
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($genieAcsTestResult): ?>
            <div class="p-2 rounded text-xs font-mono <?php echo e(str_contains(strtolower($genieAcsTestResult), 'ok') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800/50 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400'); ?>">
              <?php echo e($genieAcsTestResult); ?>

            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>
      
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100 text-sm">
            Parameter TR-069
        </div>
        <div class="p-4 grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Port TR-069 (CPE)</label>
              <input type="number" wire:model="genieAcsForm.tr069_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 font-mono text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="7547">
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CWMP Version</label>
              <select wire:model="genieAcsForm.cwmp_version" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 ">
                <option value="1-0">1.0</option>
                <option value="1-1">1.1</option>
                <option value="1-2">1.2</option>
                <option value="1-3">1.3</option>
                <option value="1-4">1.4</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Timeout (detik)</label>
              <input type="number" wire:model="genieAcsForm.timeout" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 font-mono text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="10">
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Retry Count</label>
              <input type="number" wire:model="genieAcsForm.retry_count" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 font-mono text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="3">
            </div>
            <div class="col-span-2">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook URL (dsBilling Ingest)</label>
              <input type="text" wire:model="genieAcsForm.webhook_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 font-mono text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="https://billing.domain.com/api/v1/acs/events">
              <div class="mt-2">
                <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                  <input type="checkbox" wire:model="genieAcsForm.webhook_enabled" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 "> 
                  Enable Webhook Events
                </label>
              </div>
            </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 h-fit sticky top-4">
      <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3 text-sm">Informasi Parameter Default</div>
      <div class="space-y-3">
          <div>
            <label class="block text-[11px] uppercase tracking-wider font-medium text-slate-500 dark:text-slate-400 mb-1">Default OUI</label>
            <input type="text" wire:model="genieAcsForm.default_oui" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="001122">
          </div>
          <div>
            <label class="block text-[11px] uppercase tracking-wider font-medium text-slate-500 dark:text-slate-400 mb-1">Product Class</label>
            <input type="text" wire:model="genieAcsForm.default_product_class" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="HG8245H">
          </div>
          <div>
            <label class="block text-[11px] uppercase tracking-wider font-medium text-slate-500 dark:text-slate-400 mb-1">Software Version</label>
            <input type="text" wire:model="genieAcsForm.default_software_version" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 " placeholder="V3R015C10S103">
          </div>
      </div>
      <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
        Nilai ini akan digunakan sebagai *fallback* ketika menambahkan CPE secara manual tanpa data registrasi awal (Inform).
      </div>
    </div>
  </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\acs\settings.blade.php ENDPATH**/ ?>