import os
import re

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    
    # Fix repeated dark:bg-slate-900 dark:bg-slate-900
    content = re.sub(r'(dark:bg-slate-(?:800|900)\s+)+', r'\g<1>', content)
    # Fix dark:bg-slate-900 dark:bg-slate-800 next to each other
    content = re.sub(r'dark:bg-slate-900\s+dark:bg-slate-800', r'dark:bg-slate-900', content)
    content = re.sub(r'dark:bg-slate-800\s+dark:bg-slate-900', r'dark:bg-slate-900', content)
    
    if original != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)

for root, _, files in os.walk('resources/views'):
    for file in files:
        if file.endswith('.blade.php'):
            path = os.path.join(root, file).replace('\\', '/')
            process_file(path)
