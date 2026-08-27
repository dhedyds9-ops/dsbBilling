<div>
  <div class="px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan Telegram</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Bot Telegram untuk notifikasi billing, alarm NOC, dan laporan harian</div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      @if ($savedStatus)
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Tersimpan
        </span>
      @endif
      <button wire:click="getBotInfo" class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Bot Info
      </button>
      <button wire:click="setWebhook" class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        Set Webhook
      </button>
      <button wire:click="deleteWebhook" class="text-xs px-2.5 py-1.5 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-300 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        Del Webhook
      </button>
      <button wire:click="getWebhookInfo" class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Webhook Status
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
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Konfigurasi Bot</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bot Token *</label><input type="password" wire:model="bot.bot_token" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="123456789:AAGf..."></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bot Username</label><input type="text" wire:model="bot.bot_username" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" placeholder="@nama_bot_bot"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Parse Mode</label><select wire:model="bot.parse_mode" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm"><option value="HTML">HTML</option><option value="Markdown">Markdown v1</option><option value="MarkdownV2">Markdown v2</option></select></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Callback URL</label><input type="text" wire:model="bot.webhook_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="https://billing.domain.com/api/v1/telegram/webhook"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Secret</label><input type="password" wire:model="bot.webhook_secret" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Polling Interval (detik)</label><input type="number" wire:model="bot.polling_interval" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
          <div class="md:col-span-2 flex items-center gap-6">
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="radio" wire:model="bot.use_webhook" value="1"> Webhook (Direkomendasikan untuk Production)</label>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="radio" wire:model="bot.use_webhook" value="0"> Long Polling (getUpdates loop via Queue)</label>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
          <div class="font-semibold text-slate-900 dark:text-slate-100">Daftar Chat ID Tujuan</div>
          <button type="button" wire:click="addChatId" class="text-xs px-2.5 py-1 rounded bg-blue-600 text-white inline-flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Chat ID
          </button>
        </div>
        <div class="p-4 space-y-2">
          @foreach($chatIds as $i => $c)
            <div class="grid grid-cols-12 gap-2 items-center p-2 rounded bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700">
              <div class="col-span-4"><input type="text" wire:model="chatIds.{{$i}}.id" placeholder="-100xxxxxx (Group) / id pribadi" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"></div>
              <div class="col-span-3"><input type="text" wire:model="chatIds.{{$i}}.name" placeholder="Label (Nama Group)" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
              <div class="col-span-2"><select wire:model="chatIds.{{$i}}.type" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"><option value="supergroup">Supergroup</option><option value="group">Group</option><option value="private">Private</option><option value="channel">Channel</option></select></div>
              <div class="col-span-2"><input type="text" wire:model="chatIds.{{$i}}.events" placeholder="billing,ticket,alarm" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></div>
              <div class="col-span-1 flex items-center gap-1 justify-end">
                <label class="inline-flex text-xs"><input type="checkbox" wire:model="chatIds.{{$i}}.enabled" class="rounded border-slate-300"></label>
                <button type="button" wire:click="removeChatId({{$i}})" class="p-1 text-slate-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Template Pesan</div>
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
              <textarea wire:model="templates.{{$i}}.content" rows="4" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono leading-relaxed"></textarea>
              <div class="mt-1 text-[10px] text-slate-500 dark:text-slate-400 font-mono">Mendukung variabel: @{{member_name}} · @{{invoice_total}} · @{{due_date}} · @{{company_name}} · @{{customer_portal_url}}</div>
            </div>
          @endforeach
        </div>
      </div>
    </form>

    <aside class="space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Kirim Test Message</div>
        <div class="space-y-2 text-sm">
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Chat ID Tujuan</label><input type="text" wire:model="testChatId" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono" placeholder="-100xxxx / id user"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Pesan</label><textarea wire:model="testMessage" rows="3" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"></textarea></div>
          <button wire:click="sendTest" class="w-full px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md inline-flex items-center justify-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Kirim Test
          </button>
          @if($testResult)
            <div class="p-2 rounded {{ str_contains(strtolower($testResult),'ok') || str_contains(strtolower($testResult),'true') || str_contains(strtolower($testResult),'berhasil') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }} text-[11px] font-mono whitespace-pre-wrap break-words max-h-40 overflow-auto">{{ $testResult }}</div>
          @endif
        </div>
      </div>

      @if($botInfo)
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
          <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Bot Info (getMe)</div>
          <pre class="text-[11px] font-mono text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-700/40 rounded p-2 overflow-auto max-h-40 whitespace-pre-wrap">{{ $botInfo }}</pre>
        </div>
      @endif

      @if($webhookInfo)
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
          <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Webhook Info</div>
          <pre class="text-[11px] font-mono text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-700/40 rounded p-2 overflow-auto max-h-40 whitespace-pre-wrap">{{ $webhookInfo }}</pre>
        </div>
      @endif

      <div class="bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 border border-sky-200 dark:border-sky-800 rounded-lg p-4 text-xs text-sky-800 dark:text-sky-200 space-y-1.5">
        <div class="font-semibold text-sm text-sky-900 dark:text-sky-100">💡 Catatan Penting</div>
        <ol class="list-decimal pl-4 space-y-0.5">
          <li>Buat bot via <span class="font-mono">@BotFather</span> Telegram</li>
          <li>Chat ID grup: tambahkan bot ke grup, kirim <span class="font-mono">/getidsbot</span> di grup</li>
          <li>Webhook wajib HTTPS port 443/80/88/8443 untuk Telegram API</li>
          <li>Long Polling cocok untuk dev/staging lokal (tanpa HTTPS)</li>
          <li>Format parse mode MarkdownV2 perlu escape untuk <span class="font-mono">_ * [] () ~ ></span> ` etc.</li>
        </ol>
      </div>
    </aside>
  </div>
</div>
