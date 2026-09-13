import sys

file_blade = 'D:/dsBilling/resources/views/livewire/pengaturan/telegram/index.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Replace Bot Token
search_bot = '<div class="md:col-span-2"><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bot Token *</label><input type="password" wire:model="bot.bot_token" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="123456789:AAGf..."></div>'

replace_bot = """<div class="md:col-span-2" x-data="{ show: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bot Token *</label>
    <div class="relative">
        <input x-bind:type="show ? 'text' : 'password'" wire:model="bot.bot_token" autocomplete="new-password" class="w-full pl-3 pr-10 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="123456789:AAGf...">
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
        </button>
    </div>
</div>"""


# 2. Replace Webhook URL
search_webhook_url = '<div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Callback URL</label><input type="text" wire:model="bot.webhook_url" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="https://billing.domain.com/api/v1/telegram/webhook"></div>'

replace_webhook_url = """<div x-data="{ copied: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Callback URL</label>
    <div class="relative">
        <input type="text" id="webhook_url_tg_input" wire:model="bot.webhook_url" autocomplete="off" class="w-full pl-3 pr-12 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-xs dark:bg-slate-900 dark:text-slate-100" placeholder="https://billing.domain.com/api/v1/telegram/webhook">
        <button type="button" @click="navigator.clipboard.writeText(document.getElementById('webhook_url_tg_input').value); copied = true; setTimeout(() => copied = false, 2000)" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none" title="Copy URL">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
        </button>
    </div>
</div>"""

# 3. Replace Webhook Secret
search_webhook_secret = '<div><label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Secret</label><input type="password" wire:model="bot.webhook_secret" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100"></div>'

replace_webhook_secret = """<div x-data="{ show: false }">
    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Secret</label>
    <div class="relative">
        <input x-bind:type="show ? 'text' : 'password'" wire:model="bot.webhook_secret" autocomplete="new-password" class="w-full pl-3 pr-10 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100">
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
            <span class="material-symbols-outlined notranslate" style="font-size:18px" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
        </button>
    </div>
</div>"""

changed = False
if search_bot in content:
    content = content.replace(search_bot, replace_bot)
    changed = True
else:
    print("WARNING: Bot Token not found. (might be already replaced or spacing differs)")

if search_webhook_url in content:
    content = content.replace(search_webhook_url, replace_webhook_url)
    changed = True
else:
    print("WARNING: Webhook URL not found.")

if search_webhook_secret in content:
    content = content.replace(search_webhook_secret, replace_webhook_secret)
    changed = True
else:
    print("WARNING: Webhook Secret not found.")

if changed:
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Changes applied!")
else:
    print("No changes made. Check HTML strings.")

