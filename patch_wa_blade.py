file_blade = 'D:/dsBilling/resources/views/livewire/pengaturan/whatsapp/index.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

# Make sure all inputs have consistent dark mode styling
import re

# Match <input type="text|password|number..." class="..."> and <select class="..."> and <textarea class="...">
# We can just inject "dark:bg-slate-900 dark:text-slate-100" if they are missing.
# Let's just find and replace common patterns without it.

# The inputs often have: bg-white text-slate-900
# We can search for bg-white and replace it if it's inside an input, but the safest way is a regex for classes.

# Replace bg-white that doesn't have dark:bg-slate-900 (for inputs usually they have border border-slate-200)
# Instead of doing it blind, I will just rewrite the specific inputs since it's a single file.

def fix_class(match):
    cls = match.group(1)
    if 'dark:bg-slate-900' not in cls and ('border-slate-200' in cls or 'border-gray-300' in cls):
        cls += ' dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700'
    # cleanup duplicates if any
    return f'class="{cls}"'

content = re.sub(r'class="([^"]*)"', fix_class, content)

with open(file_blade, 'w', encoding='utf-8') as f:
    f.write(content)
print("WhatsApp settings view patched!")
