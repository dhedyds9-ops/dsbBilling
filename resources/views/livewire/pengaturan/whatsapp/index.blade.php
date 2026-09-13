<div>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700">
    @section('page_title')
  <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan WhatsApp</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Integrasi WA Gateway untuk notifikasi tagihan, reminder, dan broadcast</div>
    </div>
    @endsection
    <div class="flex items-center gap-2 flex-wrap">
      @if ($savedStatus)
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium">
          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">check</span>
          Tersimpan
        </span>
      @endif
      <button wire:click="checkDeviceStatus" class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">health_and_safety</span>
        Status Device
      </button>
      <button wire:click="save" class="px-4 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
        Simpan
      </button>
    </div>
  </div>

  <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <form class="xl:col-span-2 space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Koneksi Provider WA Gateway</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Provider *</label>
            <select wire:model="connection.provider" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm dark:bg-slate-900 dark:text-slate-100">
              @foreach($providers as $p)
                <option value="{{ $p['code'] }}">{{ $p['name'] }} · {{ $p['type'] }}</option>
              @endforeach
            </select>
          </div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Mode</label><select wire:model="connection.mode" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm dark:bg-slate-900 dark:text-slate-100"><option value="sandbox">Sandbox / Testing</option><option value="production">Production</option></select></div>
          <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Base URL Endpoint Send Message *</label><input type="url" wire:model="connection.base_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100"></div>
          <div class="md:col-span-2" x-data="{ show: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Token / Key *</label>
    <div class="relative">
        <input x-bind:type="show ? 'text' : 'password'" wire:model="connection.api_token" autocomplete="new-password" class="w-full pl-3 pr-10 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100">
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
        </button>
    </div>
</div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Device ID</label><input type="text" wire:model="connection.device_id" autocomplete="off" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="(opsional, Whacenter / multi-device)"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Sender Number</label><input type="text" wire:model="connection.sender_number" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="628xx-xxxx-xxxx"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Admin / CS Number</label><input type="text" wire:model="connection.admin_number" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="628xx (untuk notifikasi internal)"></div>
          <div class="md:col-span-2" x-data="{ copied: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Inbound (Callback URL)</label>
    <div class="relative">
        <input type="url" id="webhook_url_input" wire:model="connection.webhook_url" class="w-full pl-3 pr-12 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100" placeholder="https://billing.domain.com/api/v1/whatsapp/webhook">
        <button type="button" @click="navigator.clipboard.writeText(document.getElementById('webhook_url_input').value); copied = true; setTimeout(() => copied = false, 2000)" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none" title="Copy URL">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
        </button>
    </div>
    <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
        <h4 class="text-[12px] font-semibold text-blue-800 dark:text-blue-300 flex items-center gap-1.5 mb-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:16px">help</span> Panduan Konfigurasi Webhook
        </h4>
        <ul class="list-disc pl-4 text-[11px] text-blue-700 dark:text-blue-400 space-y-1">
            <li><strong>Copy URL</strong> di atas dan paste ke menu <strong>Webhook</strong> atau <strong>Callback</strong> di dashboard provider WA Gateway Anda.</li>
            <li>Jika provider meminta metode HTTP, pilih <strong>POST</strong>.</li>
            <li>Fitur ini berguna untuk Auto-Reply, Auto-Respond cek tagihan pelanggan, dan update status pesan.</li>
            <li>Pastikan <em>"Aktifkan Webhook"</em> di bawah tercentang agar sistem dsBilling memproses pesan masuk.</li>
        </ul>
    </div>
</div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Secret</label><input type="password" wire:model="connection.webhook_secret" autocomplete="new-password" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Delay per pesan (detik)</label><input type="number" wire:model="connection.delay_per_message" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Max Retry</label><input type="number" wire:model="connection.max_retry" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
          <div class="md:col-span-2 flex items-center gap-6">
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="connection.use_webhook" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktifkan Webhook untuk pesan masuk</label>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="connection.enable_url_verification" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Strict URL verification</label>
          </div>
        </div>
        @if($deviceStatus)
          <div class="px-4 pb-4">
            <div class="p-2 rounded border text-[11px] font-mono whitespace-pre-wrap break-words max-h-40 overflow-auto {{ str_contains(strtolower($deviceStatus),'terhubung') || str_contains(strtolower($deviceStatus),'connected') || str_contains(strtolower($deviceStatus),'active') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' }}">{{ $deviceStatus }}</div>
          </div>
        @endif
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Template Pesan (8 Tipe Event)</div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
          @foreach($templates as $i => $t)
            <div class="p-4">
              <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="templates.{{$i}}.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktif</label>
                  <span class="text-[11px] px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-mono">{{ $t['code'] }}</span>
                  <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm">{{ $t['name'] }}</span>
                </div>
              </div>
              <textarea wire:model="templates.{{$i}}.content" rows="5" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono leading-relaxed whitespace-pre dark:bg-slate-900 dark:text-slate-100"></textarea>
              <div class="mt-1 text-[10px] text-slate-500 dark:text-slate-400 font-mono">Variabel: @{{member_name}} · @{{invoice_number}} · @{{package_name}} · @{{invoice_total}} · @{{due_date}} · @{{payment_time}} · @{{company_name}}</div>
            </div>
          @endforeach
        </div>
      </div>
    </form>

    <aside class="space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Kirim Test WA</div>
        <div class="space-y-2 text-sm">
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">No. Tujuan</label><input type="text" wire:model="testPhone" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="08xx atau 628xx"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Isi Pesan</label><textarea wire:model="testMessage" rows="5" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></textarea></div>
          <button wire:click="sendTest" class="w-full px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md inline-flex items-center justify-center gap-1.5 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">send</span>
            Kirim Test WA
          </button>
          @if($testResult)
            <div class="p-2 rounded border text-[11px] font-mono whitespace-pre-wrap break-words max-h-40 overflow-auto {{ str_contains(strtolower($testResult),'success') || str_contains(strtolower($testResult),'true') || str_contains(strtolower($testResult),'200') || str_contains(strtolower($testResult),'sukses') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' }}">{{ $testResult }}</div>
          @endif
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700">
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
