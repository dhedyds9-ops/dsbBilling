import sys

file_blade = 'D:/dsBilling/resources/views/livewire/pengaturan/whatsapp/index.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace API Token
search_api = '<div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Token / Key *</label><input type="password" wire:model="connection.api_token" autocomplete="new-password" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>'

replace_api = """<div class="md:col-span-2" x-data="{ show: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Token / Key *</label>
    <div class="relative">
        <input x-bind:type="show ? 'text' : 'password'" wire:model="connection.api_token" autocomplete="new-password" class="w-full pl-3 pr-10 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100">
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
        </button>
    </div>
</div>"""

if search_api in content:
    content = content.replace(search_api, replace_api)
else:
    print("API token string not found. Please check exact spacing.")
    sys.exit(1)


# Replace Webhook
search_webhook = '<div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Inbound (Callback URL)</label><input type="url" wire:model="connection.webhook_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100" placeholder="https://billing.domain.com/api/v1/whatsapp/webhook"></div>'

replace_webhook = """<div class="md:col-span-2" x-data="{ copied: false }">
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
</div>"""

if search_webhook in content:
    content = content.replace(search_webhook, replace_webhook)
else:
    print("Webhook string not found. Please check exact spacing.")
    sys.exit(1)

with open(file_blade, 'w', encoding='utf-8') as f:
    f.write(content)
print("Changes applied!")
