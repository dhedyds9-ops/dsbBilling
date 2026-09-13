import os
import re

filepath = 'D:/dsBilling/resources/views/livewire/acs/device/show.blade.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix table header typo
content = content.replace('dark:bg-slate-900/50/50', 'dark:bg-slate-900/50')

# Fix summary card gradients
content = content.replace('from-indigo-50 to-white', 'from-indigo-50 to-white dark:from-indigo-900/40 dark:to-slate-800')
content = content.replace('from-sky-50 to-white', 'from-sky-50 to-white dark:from-sky-900/40 dark:to-slate-800')
content = content.replace('from-emerald-50 to-white', 'from-emerald-50 to-white dark:from-emerald-900/40 dark:to-slate-800')
content = content.replace('from-purple-50 to-white', 'from-purple-50 to-white dark:from-purple-900/40 dark:to-slate-800')

# Also text colors in those cards
content = content.replace('text-indigo-700', 'text-indigo-700 dark:text-indigo-300')
content = content.replace('text-sky-700', 'text-sky-700 dark:text-sky-300')
content = content.replace('text-emerald-700', 'text-emerald-700 dark:text-emerald-300')
content = content.replace('text-purple-700', 'text-purple-700 dark:text-purple-300')

# Titles in cards
content = content.replace('text-indigo-500', 'text-indigo-500 dark:text-indigo-400')
content = content.replace('text-sky-500', 'text-sky-500 dark:text-sky-400')
content = content.replace('text-emerald-500', 'text-emerald-500 dark:text-emerald-400')
content = content.replace('text-purple-500', 'text-purple-500 dark:text-purple-400')

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
