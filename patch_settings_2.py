import re

filepath = 'D:/dsBilling/resources/views/livewire/acs/settings.blade.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

password_html2 = r'''
            <div x-data="{ show: false }">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Password TR-069</label>
              <div class="relative">
                  <input x-bind:type="show ? 'text' : 'password'" wire:model="genieAcsForm.tr069_password" class="w-full px-3 py-2 pr-10 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="dsbilling">
                  <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                      <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                  </button>
              </div>
            </div>
'''

content = re.sub(
    r'<div>\s*<label class="block text-xs[^"]*">Password TR-069</label>\s*<input type="password" wire:model="genieAcsForm.tr069_password"[^>]*>\s*</div>',
    password_html2.strip(),
    content,
    flags=re.DOTALL
)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated settings.blade.php again")
