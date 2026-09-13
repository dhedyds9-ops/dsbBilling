import sys

file_blade = 'D:/dsBilling/resources/views/livewire/pengaturan/telegram/index.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

# Add the guide box after the webhook url input div
search = """<div x-data="{ copied: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Callback URL</label>
    <div class="relative">
        <input type="text" id="webhook_url_tg_input" wire:model="bot.webhook_url" autocomplete="off" class="w-full pl-3 pr-12 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100" placeholder="https://billing.domain.com/api/v1/telegram/webhook">
        <button type="button" @click="navigator.clipboard.writeText(document.getElementById('webhook_url_tg_input').value); copied = true; setTimeout(() => copied = false, 2000)" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none" title="Copy URL">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
        </button>
    </div>
</div>"""

replace = """<div x-data="{ copied: false }">
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
</div>"""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Guide added!")
else:
    print("Search block not found.")

