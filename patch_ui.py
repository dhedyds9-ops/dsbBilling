import os
import re

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    
    # Text colors
    patterns = [
        (r'\btext-(slate|gray)-900\b(?!.*?dark:text-)', r'text-\1-900 dark:text-\1-100'),
        (r'\btext-(slate|gray)-800\b(?!.*?dark:text-)', r'text-\1-800 dark:text-\1-200'),
        (r'\btext-(slate|gray)-700\b(?!.*?dark:text-)', r'text-\1-700 dark:text-\1-300'),
        (r'\btext-(slate|gray)-600\b(?!.*?dark:text-)', r'text-\1-600 dark:text-\1-400'),
        (r'\btext-(slate|gray)-500\b(?!.*?dark:text-)', r'text-\1-500 dark:text-\1-400'),
        
        # Borders
        (r'\bborder-(slate|gray)-200\b(?!.*?dark:border-)', r'border-\1-200 dark:border-\1-700'),
        (r'\bborder-(slate|gray)-300\b(?!.*?dark:border-)', r'border-\1-300 dark:border-\1-600'),
        (r'\bdivide-(slate|gray)-200\b(?!.*?dark:divide-)', r'divide-\1-200 dark:divide-\1-700'),
        (r'\bdivide-(slate|gray)-100\b(?!.*?dark:divide-)', r'divide-\1-100 dark:divide-\1-700'),
        
        # Backgrounds
        (r'\bbg-white\b(?!.*?dark:bg-)', r'bg-white dark:bg-slate-800'),
        (r'\bbg-(slate|gray)-50\b(?!.*?dark:bg-)', r'bg-\1-50 dark:bg-\1-900/50'),
        (r'\bbg-(slate|gray)-100\b(?!.*?dark:bg-)', r'bg-\1-100 dark:bg-\1-800'),
        
        # Soft color backgrounds (icons, badges)
        (r'\bbg-(blue|green|red|amber|yellow|indigo|emerald|rose|teal)-50\b(?!.*?dark:bg-)', r'bg-\1-50 dark:bg-\1-900/30'),
        (r'\bbg-(blue|green|red|amber|yellow|indigo|emerald|rose|teal)-100\b(?!.*?dark:bg-)', r'bg-\1-100 dark:bg-\1-900/50'),
        
        # Form Elements / Inputs specifically
        (r'(\binput\b.*?\b)(border-(?:slate|gray)-200\b)(?!.*?dark:border-)', r'\1\2 dark:border-slate-700'),
        (r'(\bselect\b.*?\b)(border-(?:slate|gray)-200\b)(?!.*?dark:border-)', r'\1\2 dark:border-slate-700'),
    ]

    for p, repl in patterns:
        content = re.sub(p, repl, content)

    # Some targeted fixes for inputs missing bg or text definitions entirely
    content = re.sub(r'(\bclass="[^"]*\bform-input\b[^"]*)(")', r'\1 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700\2', content)
    content = re.sub(r'(\bclass="[^"]*\bform-select\b[^"]*)(")', r'\1 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700\2', content)
    
    # Basic inputs missing dark classes
    content = re.sub(r'(<input[^>]+class="[^"]*)(bg-white)([^"]*)(")', r'\1\2 dark:bg-slate-900\3\4', content)
    content = re.sub(r'(<select[^>]+class="[^"]*)(bg-white)([^"]*)(")', r'\1\2 dark:bg-slate-900\3\4', content)
    content = re.sub(r'(<textarea[^>]+class="[^"]*)(bg-white)([^"]*)(")', r'\1\2 dark:bg-slate-900\3\4', content)

    if original != content:
        try:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Patched: {filepath}")
        except Exception as e:
            print(f"Failed to patch {filepath}: {e}")

for root, _, files in os.walk('resources/views'):
    for file in files:
        if file.endswith('.blade.php'):
            path = os.path.join(root, file).replace('\\', '/')
            process_file(path)

print("Done patching.")
