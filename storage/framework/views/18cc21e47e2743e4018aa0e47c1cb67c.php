<div>
  <div class="px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan Koneksi</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Router API, Radius Engine, TR-069 GenieACS & Client</div>
    </div>
    <div class="flex items-center gap-2">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savedStatus): ?>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md <?php echo e($savedStatus==='saved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'); ?> text-xs font-medium">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($savedStatus==='saved' ? 'M5 13l4 4L19 7' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'); ?>"/></svg>
        <?php echo e($savedStatus==='saved' ? 'Berhasil disimpan' : 'Gagal'); ?>

      </span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <button wire:click="save" class="px-4 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
        Simpan
      </button>
    </div>
  </div>

  <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <nav class="flex items-center gap-1 text-sm font-medium overflow-x-auto">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['router_api'=>'Router API','radius'=>'Radius Engine','genieacs'=>'TR-069 GenieACS','radius_client'=>'Radius Client']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <button wire:click="setActiveTab('<?php echo e($k); ?>')" class="whitespace-nowrap px-3 py-1.5 rounded-md transition-colors <?php echo e($activeTab===$k ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'); ?>"><?php echo e($l); ?></button>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </nav>
  </div>

  <div class="p-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'router_api'): ?>
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <div class="font-semibold text-slate-900 dark:text-slate-100">Daftar Router</div>
            <button wire:click="newRouter" class="text-xs px-2.5 py-1 rounded bg-blue-600 text-white inline-flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              Tambah Router
            </button>
          </div>
          <div class="max-h-[640px] overflow-auto divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $routers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
              <div class="px-4 py-3 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-900/30">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex w-2 h-2 rounded-full <?php echo e(!empty($r['active']) ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-500'); ?>"></span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100"><?php echo e($r['name']); ?></span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 uppercase font-mono"><?php echo e($r['brand'] ?? 'mikrotik'); ?></span>
                  </div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5"><?php echo e($r['ip_address'] ?? $r['host']); ?>:<?php echo e($r['api_port'] ?? 8728); ?> · NAS: <?php echo e($r['nas_identifier'] ?? '-'); ?></div>
                </div>
                <div class="flex items-center gap-1">
                  <button wire:click="testRouterConnection(<?php echo e($r['id']); ?>)" title="Test Koneksi" class="p-1.5 rounded text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:text-slate-400 dark:hover:bg-blue-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></button>
                  <button wire:click="selectRouter(<?php echo e($r['id']); ?>)" title="Edit" class="p-1.5 rounded text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:text-slate-400 dark:hover:bg-indigo-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button wire:click="deleteRouter(<?php echo e($r['id']); ?>)" title="Hapus" class="p-1.5 rounded text-slate-500 hover:text-red-600 hover:bg-red-50 dark:text-slate-400 dark:hover:bg-red-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/></svg></button>
                </div>
              </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($routers)): ?>
              <div class="p-12 text-center text-slate-500 dark:text-slate-400 text-sm">Belum ada router. Klik "Tambah Router" untuk mendaftarkan router baru.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Form Router</div>
          <div class="p-4 space-y-2.5 text-sm">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Router *</label><input type="text" wire:model="formRouter.name" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Host / IP *</label><input type="text" wire:model="formRouter.host" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Port</label><input type="number" wire:model="formRouter.api_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API SSL Port</label><input type="number" wire:model="formRouter.api_ssl_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Brand</label><select wire:model="formRouter.brand" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm"><option value="mikrotik">MikroTik RouterOS</option><option value="cisco">Cisco IOS</option><option value="huawei">Huawei VRP</option><option value="juniper">Juniper Junos</option><option value="fiberhome">FiberHome</option><option value="other">Other</option></select></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Username API</label><input type="text" wire:model="formRouter.api_user" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Password API</label><input type="password" wire:model="formRouter.api_password" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
            </div>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="formRouter.use_ssl" class="rounded border-slate-300"> Gunakan SSL (API-SSL)</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="formRouter.active" class="rounded border-slate-300"> Aktif</label>
            </div>
            <div class="grid grid-cols-3 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Radius Port</label><input type="number" wire:model="formRouter.radius_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CoA Port (PoD)</label><input type="number" wire:model="formRouter.coa_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Timeout (detik)</label><input type="number" wire:model="formRouter.timeout" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            </div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NAS Identifier</label><input type="text" wire:model="formRouter.nas_identifier" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="NAS-<?php echo e($formRouter['name'] ?? '001'); ?>"></div>
            <div class="flex items-center gap-2 pt-1">
              <button wire:click="saveRouter" class="flex-1 px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?php echo e($formRouter['id'] ?? null ? 'Update' : 'Simpan'); ?> Router
              </button>
              <button wire:click="testRouterConnection(0)" class="px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Test
              </button>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($testRouterResult): ?>
              <div class="p-2 rounded <?php echo e(str_contains(strtolower($testRouterResult),'sukses') || str_contains(strtolower($testRouterResult),'success') || str_contains(strtolower($testRouterResult),'connected') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800'); ?> text-xs font-mono whitespace-pre-wrap break-words max-h-32 overflow-auto"><?php echo e($testRouterResult); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>
      </div>

    <?php elseif($activeTab === 'radius'): ?>
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Radius Engine (FreeRADIUS / MikroTik)</div>
          <div class="p-4 space-y-2.5 text-sm">
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Auth Host</label><input type="text" wire:model="radius.auth_host" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Auth Port (UDP)</label><input type="number" wire:model="radius.auth_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Accounting Host</label><input type="text" wire:model="radius.acct_host" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Acct Port (UDP)</label><input type="number" wire:model="radius.acct_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CoA / PoD Host (RFC 5176)</label><input type="text" wire:model="radius.coa_host" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CoA Port (UDP 3799)</label><input type="number" wire:model="radius.coa_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            </div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Shared Secret (NAS & FreeRADIUS)</label><input type="password" wire:model="radius.shared_secret" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="*****"></div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Auth Protocol</label><select wire:model="radius.proto" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm"><option value="pap">PAP</option><option value="chap">CHAP</option><option value="mschapv2">MS-CHAPv2</option><option value="eap-peap">EAP-PEAP</option><option value="eap-tls">EAP-TLS</option></select></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NAS Port Type</label><select wire:model="radius.nas_port_type" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm"><option value="Ethernet">Ethernet (15)</option><option value="Wireless-802.11">Wireless 802.11 (19)</option><option value="PPPoE">PPPoE (32)</option><option value="GPON">GPON/xPON</option><option value="Virtual-VPN">VPN Virtual</option></select></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Session Timeout (menit)</label><input type="number" wire:model="radius.session_timeout" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Interim-Update (detik)</label><input type="number" wire:model="radius.interim_interval" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            </div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Service Type Default</label><input type="text" wire:model="radius.service_type" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" placeholder="Framed-User / Login-User"></div>
            <div class="flex flex-wrap items-center gap-4 pt-1">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radius.require_message_auth" class="rounded border-slate-300"> Require Message-Authenticator</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radius.acct_enabled" class="rounded border-slate-300"> Enable Accounting (RFC 2866)</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radius.acct_interim" class="rounded border-slate-300"> Acct Interim Enabled</label>
            </div>
            <div class="flex items-center gap-2 pt-1">
              <button wire:click="saveRadius" class="flex-1 px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Radius
              </button>
              <button wire:click="testRadius" class="px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Test Probe
              </button>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($testRadiusResult): ?>
              <div class="p-2 rounded <?php echo e(str_contains(strtolower($testRadiusResult),'sukses') || str_contains(strtolower($testRadiusResult),'berhasil') || str_contains(strtolower($testRadiusResult),'reachable') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800'); ?> text-xs font-mono whitespace-pre-wrap break-words max-h-32 overflow-auto"><?php echo e($testRadiusResult); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>

        <div class="space-y-4">
          <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Ringkasan Koneksi Radius</div>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">Auth Endpoint</div><div class="font-mono mt-0.5 text-slate-800 dark:text-slate-100 truncate"><?php echo e($radius['auth_host'] ?? '-'); ?>:<?php echo e($radius['auth_port'] ?? '1812'); ?></div></div>
              <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">Acct Endpoint</div><div class="font-mono mt-0.5 text-slate-800 dark:text-slate-100 truncate"><?php echo e($radius['acct_host'] ?? '-'); ?>:<?php echo e($radius['acct_port'] ?? '1813'); ?></div></div>
              <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">CoA/PoD (RFC 5176)</div><div class="font-mono mt-0.5 text-slate-800 dark:text-slate-100 truncate"><?php echo e($radius['coa_host'] ?? '-'); ?>:<?php echo e($radius['coa_port'] ?? '3799'); ?></div></div>
              <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">Protocol Stack</div><div class="font-mono mt-0.5 text-slate-800 dark:text-slate-100"><?php echo e(strtoupper($radius['proto'] ?? 'PAP')); ?> · <?php echo e($radius['nas_port_type'] ?? 'PPPoE'); ?></div></div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
              ⚠️ Pastikan firewall mengizinkan UDP port 1812 (Auth), 1813 (Acct), dan 3799 (CoA/PoD) both-way antara app server dan Radius/Router.
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Konfigurasi Quick NAS Client</div>
            <div class="text-[11px] font-mono bg-slate-900 text-emerald-200 p-3 rounded-md overflow-auto max-h-56 whitespace-pre-wrap">nas dsBilling-APP {
  ipaddr = <?php echo e(request()->getClientIp()); ?>

  secret = <?php echo e($radius['shared_secret'] ? '****' : '<shared_secret>'); ?>

  shortname = dsBilling-APP
  nastype = other
  ports = 1812,1813,3799
}

clients.conf entry di atas untuk FreeRADIUS 3.x (sites-available/default).
            </div>
          </div>
        </div>
      </div>

    <?php elseif($activeTab === 'genieacs'): ?>
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">TR-069 GenieACS CWMP Server</div>
          <div class="p-4 space-y-2.5 text-sm">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Base URL GenieACS API</label><input type="text" wire:model="genieacs.base_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="http://genieacs.local:7557"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key</label><input type="password" wire:model="genieacs.api_key" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CR Username</label><input type="text" wire:model="genieacs.cr_username" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CR Password</label><input type="password" wire:model="genieacs.cr_password" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">TR-069 CPE Port (CWMP)</label><input type="number" wire:model="genieacs.cwmp_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CWMP Version</label><select wire:model="genieacs.cwmp_version" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm"><option value="1-0">TR-069 Am1 (CWMP 1.0)</option><option value="1-1">CWMP 1.1 (Am2)</option><option value="1-2">CWMP 1.2 (Am3/Am4)</option><option value="1-3">CWMP 1.3 (Am5)</option><option value="1-4">CWMP 1.4 (TR-069 Issue 1 Cor1/2)</option></select></div>
            </div>
            <div class="grid grid-cols-3 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Default OUI</label><input type="text" wire:model="genieacs.default_oui" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono uppercase" placeholder="00259E"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Default ProductClass</label><input type="text" wire:model="genieacs.default_product_class" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" placeholder="HG8245H"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Default SoftwareVersion</label><input type="text" wire:model="genieacs.default_software_version" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            </div>
            <div class="grid grid-cols-3 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Timeout (ms)</label><input type="number" wire:model="genieacs.timeout" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Retry Max</label><input type="number" wire:model="genieacs.retry" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div class="flex items-end">
                <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="genieacs.ssl_verify" class="rounded border-slate-300"> SSL Verify</label>
              </div>
            </div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook dsBilling Ingest URL</label><input type="text" wire:model="genieacs.webhook_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="https://billing.domain.com/api/v1/acs/events"></div>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="genieacs.webhook_enabled" class="rounded border-slate-300"> Enable Webhook</label>
            </div>
            <div class="flex items-center gap-2 pt-1">
              <button wire:click="saveGenieAcs" class="flex-1 px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan GenieACS
              </button>
              <button wire:click="testGenieAcs" class="px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Ping Base URL
              </button>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($testGenieAcsResult): ?>
              <div class="p-2 rounded <?php echo e(str_contains(strtolower($testGenieAcsResult),'sukses') || str_contains(strtolower($testGenieAcsResult),'200') || str_contains(strtolower($testGenieAcsResult),'berhasil') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800'); ?> text-xs font-mono whitespace-pre-wrap break-words max-h-32 overflow-auto"><?php echo e($testGenieAcsResult); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </div>
        </div>

        <div class="space-y-4">
          <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Perangkat TR-069 (ONT/CPE) Didukung</div>
            <div class="space-y-1.5 text-xs">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [['Huawei','HG8245H5 / EG8141A5 / HG8145V5 / HG6145F'],['FiberHome','HG6145D / AN5506-02-F / HG6543C4'],['ZTE','F609v3/v5.2 / F670L / ZXHN H168N / H268A'],['TP-Link','XPON EPON/GPON family (TD-W9970 etc)'],['MikroTik','RouterOS cAP ac, hAP ac² (CWMP enable via /tr069)'],['TOTOLINK / D-Link','XPON ONT berbasis RTL960x']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$v,$m]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex items-start gap-2 py-1 border-b border-slate-100 dark:border-slate-700/60 last:border-0">
                  <span class="inline-block w-2 h-2 mt-1.5 rounded-full bg-emerald-500"></span>
                  <div>
                    <div class="font-semibold text-slate-800 dark:text-slate-100"><?php echo e($v); ?></div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400"><?php echo e($m); ?></div>
                  </div>
                </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    <?php elseif($activeTab === 'radius_client'): ?>
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Radius Client Mode (Outbound Dial)</div>
          <div class="p-4 space-y-2.5 text-sm">
            <div class="rounded-md bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 text-xs text-blue-700 dark:text-blue-300">
              Gunakan tab ini apabila mode otentikasi PPPoE/Hotspot dari dsBilling ke Server Radius eksternal (contoh: RadiusLabs, CloudRadius, dll).
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Client Identifier</label><input type="text" wire:model="radiusClient.identifier" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="dsBilling-CLIENT-01"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Default Realm</label><input type="text" wire:model="radiusClient.realm" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" placeholder="@billing.local"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NAS IP (Dikirim ke Radius)</label><input type="text" wire:model="radiusClient.nas_ip" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Acct Interim (detik)</label><input type="number" wire:model="radiusClient.interim_interval" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            </div>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radiusClient.send_nas_port_id" class="rounded border-slate-300"> Kirim NAS-Port-Id</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radiusClient.send_class" class="rounded border-slate-300"> Kirim Class Attribute</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radiusClient.enable_dynamic_vlan" class="rounded border-slate-300"> Support Dynamic VLAN</label>
            </div>
            <button wire:click="saveRadiusClient" class="w-full px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Simpan Radius Client
            </button>
          </div>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\pengaturan\koneksi\index.blade.php ENDPATH**/ ?>