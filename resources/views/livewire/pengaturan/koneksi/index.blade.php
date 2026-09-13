<div>
  <div class="px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan Koneksi</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Radius Engine & Client</div>
    </div>
    <div class="flex items-center gap-2">
      @if ($savedStatus)
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md {{ $savedStatus==='saved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }} text-xs font-medium">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">{{ $savedStatus==='saved' ? 'check' : 'warning' }}</span>
        {{ $savedStatus==='saved' ? 'Berhasil disimpan' : 'Gagal' }}
      </span>
      @endif
      <button wire:click="save" class="px-4 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
        Simpan
      </button>
    </div>
  </div>

  <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <nav class="flex items-center gap-1 text-sm font-medium overflow-x-auto">
      @foreach(['radius'=>'Radius Engine','radius_client'=>'Radius Client'] as $k=>$l)
        <button wire:click="setActiveTab('{{$k}}')" class="whitespace-nowrap px-3 py-1.5 rounded-md transition-colors {{ $activeTab===$k ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700' }}">{{ $l }}</button>
      @endforeach
    </nav>
  </div>

  <div class="p-4">
    @if($activeTab === 'radius')
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Radius Engine (FreeRADIUS / MikroTik)</div>
          <div class="p-4 space-y-2.5 text-sm">
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Auth Host</label><input type="text" wire:model="radiusForm.auth_host" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Auth Port (UDP)</label><input type="number" wire:model="radiusForm.auth_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Accounting Host</label><input type="text" wire:model="radiusForm.acct_host" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Acct Port (UDP)</label><input type="number" wire:model="radiusForm.acct_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CoA / PoD Host (RFC 5176)</label><input type="text" wire:model="radiusForm.coa_host" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CoA Port (UDP 3799)</label><input type="number" wire:model="radiusForm.coa_port" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            </div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Shared Secret (NAS & FreeRADIUS)</label><input type="password" wire:model="radiusForm.shared_secret" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="*****"></div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Auth Protocol</label><select wire:model="radiusForm.proto" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm dark:bg-slate-900 dark:text-slate-100"><option value="pap">PAP</option><option value="chap">CHAP</option><option value="mschapv2">MS-CHAPv2</option><option value="eap-peap">EAP-PEAP</option><option value="eap-tls">EAP-TLS</option></select></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NAS Port Type</label><select wire:model="radiusForm.nas_port_type" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm dark:bg-slate-900 dark:text-slate-100"><option value="Ethernet">Ethernet (15)</option><option value="Wireless-802.11">Wireless 802.11 (19)</option><option value="PPPoE">PPPoE (32)</option><option value="GPON">GPON/xPON</option><option value="Virtual-VPN">VPN Virtual</option></select></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Session Timeout (menit)</label><input type="number" wire:model="radiusForm.session_timeout" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Interim-Update (detik)</label><input type="number" wire:model="radiusForm.interim_interval" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            </div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Service Type Default</label><input type="text" wire:model="radiusForm.service_type" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Framed-User / Login-User"></div>
            <div class="flex flex-wrap items-center gap-4 pt-1">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radiusForm.require_message_auth" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Require Message-Authenticator</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radiusForm.acct_enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Enable Accounting (RFC 2866)</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radiusForm.acct_interim" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Acct Interim Enabled</label>
            </div>
            <div class="flex items-center gap-2 pt-1">
              <button type="button" wire:click="saveRadius" class="px-4 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
                Simpan Radius
              </button>
              <button type="button" wire:click="testRadius" class="px-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                Test Probe
              </button>
            </div>
            @if($radiusTestResult)
              <div class="p-2 rounded {{ str_contains(strtolower($radiusTestResult),'sukses') || str_contains(strtolower($radiusTestResult),'berhasil') || str_contains(strtolower($radiusTestResult),'reachable') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }} text-xs font-mono whitespace-pre-wrap break-words max-h-32 overflow-auto">{{ $radiusTestResult }}</div>
            @endif
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 h-fit sticky top-4">
          <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Ringkasan Koneksi Radius</div>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">Auth Endpoint</div><div class="font-mono mt-0.5 text-slate-800 dark:text-slate-100 truncate">{{ $radiusForm['auth_host'] ?? '-' }}:{{ $radiusForm['auth_port'] ?? '1812' }}</div></div>
            <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">Acct Endpoint</div><div class="font-mono mt-0.5 text-slate-800 dark:text-slate-100 truncate">{{ $radiusForm['acct_host'] ?? '-' }}:{{ $radiusForm['acct_port'] ?? '1813' }}</div></div>
            <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">CoA/PoD (RFC 5176)</div><div class="font-mono mt-0.5 text-slate-800 dark:text-slate-100 truncate">{{ $radiusForm['coa_host'] ?? '-' }}:{{ $radiusForm['coa_port'] ?? '3799' }}</div></div>
            <div class="rounded bg-slate-50 dark:bg-slate-700/40 p-2.5"><div class="text-[10px] text-slate-500 dark:text-slate-400">Protocol Stack</div><div class="font-mono mt-0.5 text-slate-800 dark:text-slate-100">{{ strtoupper($radiusForm['proto'] ?? 'PAP') }} · {{ $radiusForm['nas_port_type'] ?? 'PPPoE' }}</div></div>
          </div>
          <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
            ⚠️ Pastikan firewall mengizinkan UDP port 1812 (Auth), 1813 (Acct), dan 3799 (CoA/PoD) both-way antara app server dan Radius/Router.
          </div>
        </div>
      </div>

    @elseif($activeTab === 'radius_client')
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Radius Client Mode (Outbound Dial)</div>
          <div class="p-4 space-y-2.5 text-sm">
            <div class="rounded-md bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-3 text-xs text-indigo-700 dark:text-indigo-300">
              Gunakan tab ini apabila mode otentikasi PPPoE/Hotspot dari dsBilling ke Server Radius eksternal (contoh: RadiusLabs, CloudRadius, dll).
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Client Identifier</label><input type="text" wire:model="radiusClient.identifier" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="dsBilling-CLIENT-01"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Default Realm</label><input type="text" wire:model="radiusClient.realm" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="@billing.local"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NAS IP (Dikirim ke Radius)</label><input type="text" wire:model="radiusClient.nas_ip" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
              <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Shared Secret</label><input type="password" wire:model="radiusClient.secret" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            </div>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="radiusClient.acct_interim_on_update" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Enable Acct Interim</label>
            </div>
            <button type="button" wire:click="saveRadiusClient" class="w-full px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-md inline-flex items-center justify-center gap-1.5 transition-colors">
              <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
              Simpan Radius Client
            </button>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
