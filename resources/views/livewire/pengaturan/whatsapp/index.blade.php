<div>
  <div class="px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan WhatsApp</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Integrasi WA Gateway untuk notifikasi tagihan, reminder, dan broadcast</div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      @if ($savedStatus)
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Tersimpan
        </span>
      @endif
      <button wire:click="checkDeviceStatus" class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        Status Device
      </button>
      <button wire:click="save" class="px-4 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
        Simpan
      </button>
    </div>
  </div>

  <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <form class="xl:col-span-2 space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Koneksi Provider WA Gateway</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Provider *</label>
            <select wire:model="connection.provider" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm">
              @foreach($providers as $p)
                <option value="{{ $p['code'] }}">{{ $p['name'] }} · {{ $p['type'] }}</option>
              @endforeach
            </select>
          </div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Mode</label><select wire:model="connection.mode" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm"><option value="sandbox">Sandbox / Testing</option><option value="production">Production</option></select></div>
          <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Base URL Endpoint Send Message *</label><input type="url" wire:model="connection.base_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs"></div>
          <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Token / Key *</label><input type="password" wire:model="connection.api_token" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Device ID</label><input type="text" wire:model="connection.device_id" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="(opsional, Whacenter / multi-device)"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Sender Number</label><input type="text" wire:model="connection.sender_number" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="628xx-xxxx-xxxx"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Admin / CS Number</label><input type="text" wire:model="connection.admin_number" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="628xx (untuk notifikasi internal)"></div>
          <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Inbound (Callback URL)</label><input type="url" wire:model="connection.webhook_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs" placeholder="https://billing.domain.com/api/v1/whatsapp/webhook"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Secret</label><input type="password" wire:model="connection.webhook_secret" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Delay per pesan (detik)</label><input type="number" wire:model="connection.delay_per_message" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Max Retry</label><input type="number" wire:model="connection.max_retry" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
          <div class="md:col-span-2 flex items-center gap-6">
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="connection.use_webhook" class="rounded border-slate-300"> Aktifkan Webhook untuk pesan masuk</label>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="connection.enable_url_verification" class="rounded border-slate-300"> Strict URL verification</label>
          </div>
        </div>
        @if($deviceStatus)
          <div class="px-4 pb-4">
            <div class="p-2 rounded border text-[11px] font-mono whitespace-pre-wrap break-words max-h-40 overflow-auto {{ str_contains(strtolower($deviceStatus),'terhubung') || str_contains(strtolower($deviceStatus),'connected') || str_contains(strtolower($deviceStatus),'active') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' }}">{{ $deviceStatus }}</div>
          </div>
        @endif
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Template Pesan (8 Tipe Event)</div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
          @foreach($templates as $i => $t)
            <div class="p-4">
              <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="templates.{{$i}}.enabled" class="rounded border-slate-300"> Aktif</label>
                  <span class="text-[11px] px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-mono">{{ $t['code'] }}</span>
                  <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm">{{ $t['name'] }}</span>
                </div>
              </div>
              <textarea wire:model="templates.{{$i}}.content" rows="5" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono leading-relaxed whitespace-pre"></textarea>
              <div class="mt-1 text-[10px] text-slate-500 dark:text-slate-400 font-mono">Variabel: @{{member_name}} · @{{invoice_number}} · @{{package_name}} · @{{invoice_total}} · @{{due_date}} · @{{payment_time}} · @{{company_name}}</div>
            </div>
          @endforeach
        </div>
      </div>
    </form>

    <aside class="space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Kirim Test WA</div>
        <div class="space-y-2 text-sm">
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">No. Tujuan</label><input type="text" wire:model="testPhone" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="08xx atau 628xx"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Isi Pesan</label><textarea wire:model="testMessage" rows="5" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></textarea></div>
          <button wire:click="sendTest" class="w-full px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Kirim Test WA
          </button>
          @if($testResult)
            <div class="p-2 rounded border text-[11px] font-mono whitespace-pre-wrap break-words max-h-40 overflow-auto {{ str_contains(strtolower($testResult),'success') || str_contains(strtolower($testResult),'true') || str_contains(strtolower($testResult),'200') || str_contains(strtolower($testResult),'sukses') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' }}">{{ $testResult }}</div>
          @endif
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Panduan Provider</div>
        <div class="space-y-2 text-xs">
          @foreach($providers as $p)
            <div class="p-2 rounded bg-slate-50 dark:bg-slate-700/40">
              <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $p['name'] }}</div>
              <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">{{ $p['default_base_url'] }}</div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4 text-xs text-emerald-800 dark:text-emerald-200 space-y-1.5">
        <div class="font-semibold text-sm text-emerald-900 dark:text-emerald-100">💡 Tips Anti-Banned</div>
        <ol class="list-decimal pl-4 space-y-0.5">
          <li>Delay min. 3 detik per pesan; 5 detik lebih aman</li>
          <li>Jangan spam template sama persis > 30 per jam; variatifkan sapaan</li>
          <li>Gunakan nomor lama / real identity, bukan nomor baru beli</li>
          <li>Kirim pesan aktif balas antar member (chat-op) sebelum blast</li>
          <li>Device Official Cloud API (Meta) direkomendasikan untuk volume > 1000/hari</li>
        </ol>
      </div>
    </aside>
  </div>
</div>
