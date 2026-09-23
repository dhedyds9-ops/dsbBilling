<div>
  <div class="px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan Payment Gateway</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Midtrans, Xendit, Duitku, Tripay, Manual Bank Transfer & e-Wallet</div>
    </div>
    <div class="flex items-center gap-2">
      @if ($savedStatus === 'saved')
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium">
          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">check</span>
          Tersimpan
        </span>
      @elseif ($savedStatus === 'error')
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 text-xs font-medium">
          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">error</span>
          Gagal Disimpan
        </span>
      @endif
      <span class="text-xs text-slate-400 dark:text-slate-500 italic">Simpan dilakukan per tab ↓</span>
    </div>
  </div>

  <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <nav class="flex items-center gap-1 text-sm font-medium overflow-x-auto flex-wrap">
      @foreach(['midtrans'=>'Midtrans','xendit'=>'Xendit','duitku'=>'Duitku','tripay'=>'Tripay','ipaymu'=>'iPaymu','manual'=>'Bank Manual','ewallet'=>'e-Wallet Manual','general'=>'Umum'] as $k=>$l)
        <button wire:click="setActiveTab('{{$k}}')" class="whitespace-nowrap px-3 py-1.5 rounded-md transition-colors {{ $activeTab===$k ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700' }}">{{ $l }}</button>
      @endforeach
    </nav>
  </div>

  <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <form class="xl:col-span-2 space-y-4 text-sm">
      @if($activeTab === 'general')
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Pengaturan Umum Payment</div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Mode</label><select wire:model="general.mode" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm dark:bg-slate-900 dark:text-slate-100"><option value="sandbox">Sandbox</option><option value="production">Production</option></select></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Default Expiry (jam)</label><input type="number" wire:model="general.default_expiry_hours" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Invoice Prefix</label><input type="text" wire:model="general.invoice_prefix" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Min. Topup (Rp)</label><input type="number" wire:model="general.minimum_topup_min_amount" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Success URL</label><input type="text" wire:model="general.success_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Pending URL</label><input type="text" wire:model="general.pending_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Error URL</label><input type="text" wire:model="general.error_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Base URL (untuk Callback / Notification)</label><input type="url" wire:model="general.webhook_base_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100" placeholder="https://billing.domain.com/api/v1/pg/"></div>
            <div class="md:col-span-2 flex flex-wrap items-center gap-6">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="general.auto_confirm_payment" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Auto-confirm on Valid Callback</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="general.send_receipt_on_success" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Kirim WA/Email Tanda Terima</label>
            </div>
          </div>
        </div>

      @elseif($activeTab === 'midtrans')
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Midtrans (Pembayaran Indonesia)</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="midtrans.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktifkan Midtrans</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Merchant ID</label><input type="text" wire:model="midtrans.merchant_id" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Server Key Sandbox</label><input type="password" wire:model="midtrans.server_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Server Key Production</label><input type="password" wire:model="midtrans.server_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Client Key Sandbox</label><input type="text" wire:model="midtrans.client_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Client Key Production</label><input type="text" wire:model="midtrans.client_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry (satuan)</label><select wire:model="midtrans.expiry_unit" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm dark:bg-slate-900 dark:text-slate-100"><option value="minute">Menit</option><option value="hour">Jam</option><option value="day">Hari</option></select></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Durasi Expiry</label><input type="number" wire:model="midtrans.expiry_duration" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2 flex flex-wrap items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="midtrans.enable_3ds" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Enable 3DS Credit Card</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="midtrans.custom_expiry" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Custom Expiry</label>
            </div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Enabled Payment Channels</div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach(['bank_transfer'=>'Bank Transfer','credit_card'=>'Credit Card','ewallet'=>'e-Wallet','qris'=>'QRIS','direct_debit'=>'Direct Debit','store'=>'Indomaret/Alfamart','cardless_credit'=>'Cardless (Kredivo/Akulaku)'] as $k=>$l)
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-2 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="midtrans.enabled_channels.{{$k}}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> {{ $l }}</label>
                @endforeach
              </div>
            </div>
            <div class="md:col-span-2 flex justify-end pt-2 border-t border-slate-100 dark:border-slate-700">
              <button type="button" wire:click="saveMidtrans" wire:loading.attr="disabled" wire:target="saveMidtrans" class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="saveMidtrans" class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
                <span wire:loading wire:target="saveMidtrans" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:16px">sync</span>
                <span wire:loading.remove wire:target="saveMidtrans">Simpan Midtrans</span>
                <span wire:loading wire:target="saveMidtrans">Menyimpan...</span>
              </button>
            </div>
          </div>
        </div>

      @elseif($activeTab === 'xendit')
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Xendit Payment Gateway</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="xendit.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktifkan Xendit</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Secret Key Sandbox</label><input type="password" wire:model="xendit.secret_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Secret Key Production</label><input type="password" wire:model="xendit.secret_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Public Key Sandbox</label><input type="text" wire:model="xendit.public_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Public Key Production</label><input type="text" wire:model="xendit.public_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Verify Token</label><input type="password" wire:model="xendit.webhook_token" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Virtual Account Banks</div>
              <div class="grid grid-cols-3 gap-1.5">
                @foreach(array_keys($xendit['va_banks']) as $b)
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="xendit.va_banks.{{$b}}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> {{ $b }}</label>
                @endforeach
              </div>
            </div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">e-Wallet Providers</div>
              <div class="grid grid-cols-3 gap-1.5">
                @foreach(array_keys($xendit['ewallet_providers']) as $p)
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="xendit.ewallet_providers.{{$p}}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> {{ $p }}</label>
                @endforeach
              </div>
            </div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Retail Outlets</div>
              <div class="grid grid-cols-3 gap-1.5">
                @foreach(array_keys($xendit['retail_providers']) as $p)
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="xendit.retail_providers.{{$p}}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> {{ $p }}</label>
                @endforeach
              </div>
            </div>
            <div class="md:col-span-2 flex justify-end pt-2 border-t border-slate-100 dark:border-slate-700">
              <button type="button" wire:click="saveXendit" wire:loading.attr="disabled" wire:target="saveXendit" class="px-5 py-2 text-sm bg-lime-600 hover:bg-lime-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="saveXendit" class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
                <span wire:loading wire:target="saveXendit" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:16px">sync</span>
                <span wire:loading.remove wire:target="saveXendit">Simpan Xendit</span>
                <span wire:loading wire:target="saveXendit">Menyimpan...</span>
              </button>
            </div>
          </div>
        </div>

      @elseif($activeTab === 'duitku')
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Duitku (PT. Dukita Indonesia)</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="duitku.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktifkan Duitku</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Merchant Code</label><input type="text" wire:model="duitku.merchant_code" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry Period (menit)</label><input type="number" wire:model="duitku.expiry_period" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Sandbox</label><input type="password" wire:model="duitku.api_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Production</label><input type="password" wire:model="duitku.api_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Callback URL</label><input type="text" wire:model="duitku.callback_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Return URL</label><input type="text" wire:model="duitku.return_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs"></div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Aktif Channels (Kode Duitku)</div>
              @php $duitkuLabels = ['VC'=>'Kartu Kredit','M2'=>'Mandiri VA','BT'=>'Permata VA','B1'=>'CIMB VA','I1'=>'BNI VA','VA'=>'Maybank VA','A1'=>'Alfamart','DN'=>'DANA','OV'=>'OVO','SFP'=>'ShopeePay','LA'=>'LinkAja','GR'=>'GrabPay','NQ'=>'DOKU Wallet','QR'=>'QRIS','FT'=>'Pegadaian/Pos']; @endphp
              <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5">
                @foreach(array_keys($duitku['enabled_channels']) as $p)
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40">
                    <input type="checkbox" wire:model="duitku.enabled_channels.{{$p}}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                    <span><span class="font-mono font-bold">{{ $p }}</span> <span class="text-slate-400 dark:text-slate-500">· {{ $duitkuLabels[$p] ?? $p }}</span></span>
                  </label>
                @endforeach
              </div>
            </div>
            <div class="md:col-span-2 flex justify-end pt-2 border-t border-slate-100 dark:border-slate-700">
              <button type="button" wire:click="saveDuitku" wire:loading.attr="disabled" wire:target="saveDuitku" class="px-5 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="saveDuitku" class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
                <span wire:loading wire:target="saveDuitku" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:16px">sync</span>
                <span wire:loading.remove wire:target="saveDuitku">Simpan Duitku</span>
                <span wire:loading wire:target="saveDuitku">Menyimpan...</span>
              </button>
            </div>
          </div>
        </div>

      @elseif($activeTab === 'tripay')
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Tripay (PT. Lini Masa Triyas)</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="tripay.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktifkan Tripay</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Merchant Code</label><input type="text" wire:model="tripay.merchant_code" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Private Key</label><input type="password" wire:model="tripay.private_key" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Sandbox</label><input type="password" wire:model="tripay.api_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Production</label><input type="password" wire:model="tripay.api_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry Hours</label><input type="number" wire:model="tripay.expiry_hours" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Aktif Channels (Tripay Kode)</div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5">
                @foreach(array_keys($tripay['enabled_channels']) as $p)
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="tripay.enabled_channels.{{$p}}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> {{ $p }}</label>
                @endforeach
              </div>
            </div>
            <div class="md:col-span-2 flex justify-end pt-2 border-t border-slate-100 dark:border-slate-700">
              <button type="button" wire:click="saveTripay" wire:loading.attr="disabled" wire:target="saveTripay" class="px-5 py-2 text-sm bg-rose-600 hover:bg-rose-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="saveTripay" class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
                <span wire:loading wire:target="saveTripay" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:16px">sync</span>
                <span wire:loading.remove wire:target="saveTripay">Simpan Tripay</span>
                <span wire:loading wire:target="saveTripay">Menyimpan...</span>
              </button>
            </div>
          </div>
        </div>

      @elseif($activeTab === 'ipaymu')
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
            <span class="font-semibold text-slate-900 dark:text-slate-100">iPaymu Payment Gateway</span>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="ipaymu.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktifkan iPaymu</label>
          </div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">VA Number</label><input type="text" wire:model="ipaymu.va_number" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Sandbox</label><input type="password" wire:model="ipaymu.api_key_sandbox" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key Production</label><input type="password" wire:model="ipaymu.api_key_production" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
            
            <div class="md:col-span-2">
              <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Aktif Channels (iPaymu)</div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5">
                @foreach(array_keys($ipaymu['enabled_channels']) as $p)
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 p-1.5 rounded bg-slate-50 dark:bg-slate-700/40"><input type="checkbox" wire:model="ipaymu.enabled_channels.{{$p}}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> {{ $p }}</label>
                @endforeach
              </div>
            </div>
            
            <div class="md:col-span-2 flex justify-end pt-2 border-t border-slate-100 dark:border-slate-700">
              <button type="button" wire:click="saveIpaymu" wire:loading.attr="disabled" wire:target="saveIpaymu" class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="saveIpaymu" class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
                <span wire:loading wire:target="saveIpaymu" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:16px">sync</span>
                <span wire:loading.remove wire:target="saveIpaymu">Simpan iPaymu</span>
                <span wire:loading wire:target="saveIpaymu">Menyimpan...</span>
              </button>
            </div>
          </div>
        </div>

      @elseif($activeTab === 'manual')
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-slate-900 dark:text-slate-100">Rekening Bank Manual (Transfer)</span>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="manualBank.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktifkan</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="manualBank.require_attachment" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Wajib bukti transfer</label>
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="manualBank.auto_approve_manual" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Auto-approve</label>
              <button type="button" wire:click="addBankAccount" class="text-xs px-2.5 py-1 rounded bg-indigo-600 hover:bg-indigo-700 transition-colors text-white inline-flex items-center gap-1">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">add</span>
                Tambah Rekening
              </button>
            </div>
          </div>
          <div class="p-4 space-y-3 bg-slate-50 dark:bg-slate-900/10">
            @foreach($manualBank['accounts'] ?? [] as $i => $ac)
              <div class="grid grid-cols-12 gap-2 items-center" wire:key="bank-{{$i}}">
                <div class="col-span-3"><input type="text" wire:model="manualBank.accounts.{{$i}}.bank_name" placeholder="BCA / Mandiri" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase"></div>
                <div class="col-span-3"><input type="text" wire:model="manualBank.accounts.{{$i}}.account_number" placeholder="No Rekening" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
                <div class="col-span-5"><input type="text" wire:model="manualBank.accounts.{{$i}}.account_holder" placeholder="Atas Nama" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
                <div class="col-span-1 flex items-center gap-1 justify-end">
                  <label class="inline-flex text-xs"><input type="checkbox" wire:model="manualBank.accounts.{{$i}}.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></label>
                  <button type="button" wire:click="removeBankAccount({{$i}})" class="p-1 text-slate-400 hover:text-red-600 transition-colors"><span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">close</span></button>
                </div>
              </div>
            @endforeach
            <div class="flex justify-end pt-2 border-t border-slate-200 dark:border-slate-700">
              <button type="button" wire:click="saveManualTransfer" wire:loading.attr="disabled" wire:target="saveManualTransfer" class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="saveManualTransfer" class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
                <span wire:loading wire:target="saveManualTransfer" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:16px">sync</span>
                <span wire:loading.remove wire:target="saveManualTransfer">Simpan Rekening Bank</span>
                <span wire:loading wire:target="saveManualTransfer">Menyimpan...</span>
              </button>
            </div>
          </div>
        </div>

      @elseif($activeTab === 'ewallet')
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
          <div class="px-4 py-3 border-y border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-slate-900 dark:text-slate-100">e-Wallet Manual (GoPay / OVO / DANA / ShopeePay)</span>
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="ewalletManual.require_attachment" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Wajib bukti</label>
              <button type="button" wire:click="addEwallet" class="text-xs px-2.5 py-1 rounded bg-indigo-600 hover:bg-indigo-700 transition-colors text-white inline-flex items-center gap-1">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">add</span>
                Tambah
              </button>
            </div>
          </div>
          <div class="p-4 space-y-3 bg-slate-50 dark:bg-slate-900/10">
            @foreach($ewalletManual['providers'] ?? [] as $i => $ew)
              <div class="grid grid-cols-12 gap-2 items-center" wire:key="ewallet-{{$i}}">
                <div class="col-span-3"><input type="text" wire:model="ewalletManual.providers.{{$i}}.name" placeholder="GOPAY / OVO" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase dark:bg-slate-900 dark:text-slate-100"></div>
                <div class="col-span-3"><input type="text" wire:model="ewalletManual.providers.{{$i}}.number" placeholder="Nomor / ID" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
                <div class="col-span-5"><input type="text" wire:model="ewalletManual.providers.{{$i}}.holder" placeholder="Atas Nama" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
                <div class="col-span-1 flex items-center gap-1 justify-end">
                  <label class="inline-flex text-xs"><input type="checkbox" wire:model="ewalletManual.providers.{{$i}}.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></label>
                  <button type="button" wire:click="removeEwallet({{$i}})" class="p-1 text-slate-400 hover:text-red-600 transition-colors"><span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">close</span></button>
                </div>
              </div>
            @endforeach
            <div class="flex justify-end pt-2 border-t border-slate-200 dark:border-slate-700">
              <button type="button" wire:click="saveEwallet" wire:loading.attr="disabled" wire:target="saveEwallet" class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="saveEwallet" class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
                <span wire:loading wire:target="saveEwallet" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:16px">sync</span>
                <span wire:loading.remove wire:target="saveEwallet">Simpan e-Wallet</span>
                <span wire:loading wire:target="saveEwallet">Menyimpan...</span>
              </button>
            </div>
          </div>
        </div>
      @endif
    </form>

    <aside class="space-y-4">
        @if(in_array($activeTab, ['midtrans','xendit','tripay','ipaymu']))
          <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2 flex items-center gap-1.5">
              <span class="material-symbols-outlined notranslate text-indigo-600" translate="no" style="font-size:16px">account_balance_wallet</span>
              Test Balance / Info Saldo
            </div>
            <div class="space-y-1.5">
              @if($activeTab==='midtrans')
                <button wire:click="testMidtransBalance" wire:loading.attr="disabled" wire:target="testMidtransBalance" class="w-full px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-md inline-flex items-center justify-center gap-1.5 transition-colors disabled:opacity-60">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">monitoring</span>
                  Cek Koneksi Midtrans
                </button>
              @elseif($activeTab==='xendit')
                <button wire:click="testXenditBalance" wire:loading.attr="disabled" wire:target="testXenditBalance" class="w-full px-3 py-1.5 text-xs bg-lime-600 hover:bg-lime-700 text-white rounded-md inline-flex items-center justify-center gap-1.5 transition-colors disabled:opacity-60">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">monitoring</span>
                  Cek Koneksi Xendit
                </button>
              @elseif($activeTab==='tripay')
                <button wire:click="testTripayBalance" wire:loading.attr="disabled" wire:target="testTripayBalance" class="w-full px-3 py-1.5 text-xs bg-rose-600 hover:bg-rose-700 text-white rounded-md inline-flex items-center justify-center gap-1.5 transition-colors disabled:opacity-60">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">monitoring</span>
                  Cek Koneksi Tripay
                </button>
              @elseif($activeTab==='ipaymu')
                <button wire:click="testIpaymuBalance" wire:loading.attr="disabled" wire:target="testIpaymuBalance" class="w-full px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-md inline-flex items-center justify-center gap-1.5 transition-colors disabled:opacity-60">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">monitoring</span>
                  Cek Koneksi iPaymu
                </button>
              @endif
            </div>
            {{-- ✅ Fixed: balanceInfo sekarang di dalam card, bukan di luar --}}
            @if($balanceInfo)
              <div class="mt-3 p-2 rounded border text-[11px] font-mono whitespace-pre-wrap break-words max-h-40 overflow-auto bg-slate-50 dark:bg-slate-700/40 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">{{ $balanceInfo }}</div>
            @endif
          </div>
        @endif

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Mode PG Aktif</div>
        <div class="space-y-1.5 text-xs">
          @php
            $pgs = [
              ['midtrans.enabled', 'Midtrans', $midtrans['enabled'] ?? false],
              ['xendit.enabled', 'Xendit', $xendit['enabled'] ?? false],
              ['duitku.enabled', 'Duitku', $duitku['enabled'] ?? false],
              ['tripay.enabled', 'Tripay', $tripay['enabled'] ?? false],
              ['ipaymu.enabled', 'iPaymu', $ipaymu['enabled'] ?? false],
              ['manual.enabled', 'Bank Transfer', $manualBank['enabled'] ?? false],
              ['ewallet', 'e-Wallet Manual', $ewalletManual['enabled'] ?? false],
            ];
          @endphp
          @foreach($pgs as [$k,$l,$on])
            <div class="flex items-center justify-between">
              <span class="text-slate-700 dark:text-slate-200">{{ $l }}</span>
              <span class="inline-flex items-center gap-1 {{ $on ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-400' }}">
                <span class="inline-block w-2 h-2 rounded-full {{ $on ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                {{ $on ? 'AKTIF' : 'OFF' }}
              </span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Status Simpan</div>
        @if($savedStatus)
          <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs">Berhasil disimpan pada {{ now()->format('d/m/Y H:i:s') }}</div>
        @else
          <div class="text-xs text-slate-500 dark:text-slate-400">Belum ada perubahan disimpan.</div>
        @endif
      </div>
    </aside>
  </div>
</div>
