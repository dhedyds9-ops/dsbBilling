<div>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
    <?php $__env->startSection('page_title'); ?>
    <div>
      <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Pengaturan Telegram</h1>
      <div class="text-xs text-slate-500 dark:text-slate-400">Bot Telegram untuk notifikasi billing, alarm NOC, dan laporan harian</div>
    </div>
    <?php $__env->stopSection(); ?>
    <div class="flex items-center gap-2 flex-wrap">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savedStatus): ?>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium">
          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">check</span>
          Tersimpan
        </span>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <button wire:click="getBotInfo" class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">info</span>
        Bot Info
      </button>
      <button wire:click="setWebhook" class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">link</span>
        Set Webhook
      </button>
      <button wire:click="deleteWebhook" class="text-xs px-2.5 py-1.5 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-300 rounded-md hover:bg-red-50 dark:bg-red-900/30 dark:hover:bg-red-900/20 inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">link_off</span>
        Del Webhook
      </button>
      <button wire:click="getWebhookInfo" class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">webhook</span>
        Webhook Status
      </button>
      <button wire:click="save" class="px-4 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium inline-flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">save</span>
        Simpan
      </button>
    </div>
  </div>

  <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <form class="xl:col-span-2 space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Konfigurasi Bot</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div class="md:col-span-2" x-data="{ show: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bot Token *</label>
    <div class="relative">
        <input x-bind:type="show ? 'text' : 'password'" wire:model="bot.bot_token" autocomplete="new-password" class="w-full pl-3 pr-10 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="123456789:AAGf...">
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
        </button>
    </div>
</div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bot Username</label><input type="text" wire:model="bot.bot_username" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="@nama_bot_bot"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Parse Mode</label><select wire:model="bot.parse_mode" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm dark:bg-slate-900 dark:text-slate-100"><option value="HTML">HTML</option><option value="Markdown">Markdown v1</option><option value="MarkdownV2">Markdown v2</option></select></div>
          <div x-data="{ copied: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Callback URL</label>
    <div class="relative">
        <input type="text" id="webhook_url_tg_input" wire:model="bot.webhook_url" autocomplete="off" class="w-full pl-3 pr-12 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100" placeholder="https://billing.domain.com/api/v1/telegram/webhook">
        <button type="button" @click="navigator.clipboard.writeText(document.getElementById('webhook_url_tg_input').value); copied = true; setTimeout(() => copied = false, 2000)" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none" title="Copy URL">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
        </button>
    </div>
    <div class="mt-3 p-3 bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 rounded-md">
        <h4 class="text-[12px] font-semibold text-sky-800 dark:text-sky-300 flex items-center gap-1.5 mb-1.5">
            <span class="material-symbols-outlined notranslate" style="font-size:16px">help</span> Panduan Konfigurasi Webhook
        </h4>
        <ul class="list-disc pl-4 text-[11px] text-sky-700 dark:text-sky-400 space-y-1">
            <li>Untuk Telegram, Anda tidak perlu meng-copy paste URL ini secara manual ke @BotFather.</li>
            <li>Cukup klik tombol <strong>"Set Webhook"</strong> di bagian atas menu ini, dan dsBilling akan mendaftarkannya secara otomatis.</li>
            <li>Pastikan dsBilling dapat diakses dari internet menggunakan <strong>HTTPS (SSL)</strong> agar webhook diterima oleh server Telegram.</li>
        </ul>
    </div>
</div>
          <div x-data="{ show: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Secret</label>
    <div class="relative">
        <input x-bind:type="show ? 'text' : 'password'" wire:model="bot.webhook_secret" autocomplete="new-password" class="w-full pl-3 pr-10 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100">
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
        </button>
    </div>
</div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Polling Interval (detik)</label><input type="number" wire:model="bot.polling_interval" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
          <div class="md:col-span-2 flex items-center gap-6">
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="radio" wire:model="bot.use_webhook" value="1"> Webhook (Direkomendasikan untuk Production)</label>
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="radio" wire:model="bot.use_webhook" value="0"> Long Polling (getUpdates loop via Queue)</label>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 flex items-center justify-between">
          <div class="font-semibold text-slate-900 dark:text-slate-100">Daftar Chat ID Tujuan</div>
          <button type="button" wire:click="addChatId" class="text-xs px-2.5 py-1 rounded bg-indigo-600 text-white inline-flex items-center gap-1">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">add</span>
            Tambah Chat ID
          </button>
        </div>
        <div class="p-4 space-y-2">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $chatIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="grid grid-cols-12 gap-2 items-center p-2 rounded bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700">
              <div class="col-span-4"><input type="text" wire:model="chatIds.<?php echo e($i); ?>.id" placeholder="-100xxxxxx (Group) / id pribadi" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>
              <div class="col-span-3"><input type="text" wire:model="chatIds.<?php echo e($i); ?>.name" placeholder="Label (Nama Group)" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
              <div class="col-span-2"><select wire:model="chatIds.<?php echo e($i); ?>.type" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"><option value="supergroup">Supergroup</option><option value="group">Group</option><option value="private">Private</option><option value="channel">Channel</option></select></div>
              <div class="col-span-2"><input type="text" wire:model="chatIds.<?php echo e($i); ?>.events" placeholder="billing,ticket,alarm" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></div>
              <div class="col-span-1 flex items-center gap-1 justify-end">
                <label class="inline-flex text-xs"><input type="checkbox" wire:model="chatIds.<?php echo e($i); ?>.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></label>
                <button type="button" wire:click="removeChatId(<?php echo e($i); ?>)" class="p-1 text-slate-400 hover:text-red-600 transition-colors">
                  <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">close</span>
                </button>
              </div>
            </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/30 font-semibold text-slate-900 dark:text-slate-100">Template Pesan</div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="p-4">
              <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                  <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300"><input type="checkbox" wire:model="templates.<?php echo e($i); ?>.enabled" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"> Aktif</label>
                  <span class="text-[11px] px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-mono"><?php echo e($t['code']); ?></span>
                  <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm"><?php echo e($t['name']); ?></span>
                </div>
              </div>
              <textarea wire:model="templates.<?php echo e($i); ?>.content" rows="4" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono leading-relaxed dark:bg-slate-900 dark:text-slate-100"></textarea>
              <div class="mt-1 text-[10px] text-slate-500 dark:text-slate-400 font-mono">Mendukung variabel: {{member_name}} · {{invoice_total}} · {{due_date}} · {{company_name}} · {{customer_portal_url}}</div>
            </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
      </div>
    </form>

    <aside class="space-y-4">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-3">Kirim Test Message</div>
        <div class="space-y-2 text-sm">
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Chat ID Tujuan</label><input type="text" wire:model="testChatId" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="-100xxxx / id user"></div>
          <div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Pesan</label><textarea wire:model="testMessage" rows="3" class="w-full px-3 py-2 text-xs border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></textarea></div>
          <button wire:click="sendTest" class="w-full px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-md inline-flex items-center justify-center gap-1.5 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">send</span>
            Kirim Test
          </button>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($testResult): ?>
            <div class="p-2 rounded <?php echo e(str_contains(strtolower($testResult),'ok') || str_contains(strtolower($testResult),'true') || str_contains(strtolower($testResult),'berhasil') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800'); ?> text-[11px] font-mono whitespace-pre-wrap break-words max-h-40 overflow-auto"><?php echo e($testResult); ?></div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($botInfo): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
          <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Bot Info (getMe)</div>
          <pre class="text-[11px] font-mono text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-700/40 rounded p-2 overflow-auto max-h-40 whitespace-pre-wrap"><?php echo e($botInfo); ?></pre>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($webhookInfo): ?>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
          <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Webhook Info</div>
          <pre class="text-[11px] font-mono text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-700/40 rounded p-2 overflow-auto max-h-40 whitespace-pre-wrap"><?php echo e($webhookInfo); ?></pre>
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
<?php /**PATH D:\dsBilling\resources\views\livewire\pengaturan\telegram\index.blade.php ENDPATH**/ ?>