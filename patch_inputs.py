import os
import re

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    
    # Catch ALL basic inputs/selects/textareas and ensure they have dark mode classes
    # If they are standard form elements, they need dark mode explicitly.
    content = re.sub(
        r'(<(?:input|select|textarea)[^>]+class="[^"]*)(?<!dark:bg-slate-900)([^"]*)(")',
        r'\1 dark:bg-slate-900 dark:text-slate-100\2\3',
        content
    )
    
    # Clean up duplicate classes we might have just added
    content = re.sub(r'(dark:bg-slate-900\s+)+', 'dark:bg-slate-900 ', content)
    content = re.sub(r'(dark:text-slate-100\s+)+', 'dark:text-slate-100 ', content)
    content = re.sub(r'(dark:border-slate-700\s+)+', 'dark:border-slate-700 ', content)
    
    # Border 100 which is very light
    content = re.sub(r'\bborder-(slate|gray)-100\b(?!.*?dark:border-)', r'border-\1-100 dark:border-slate-700', content)

    if original != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Patched: {filepath}")

for root, _, files in os.walk('resources/views'):
    for file in files:
        if file.endswith('.blade.php'):
            path = os.path.join(root, file).replace('\\', '/')
            try:
                process_file(path)
            except:
                pass

print("Done patching.")
