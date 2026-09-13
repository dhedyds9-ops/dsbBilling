import os
import glob
import re

total = 0
for filepath in glob.glob('D:/dsBilling/resources/views/livewire/**/*.blade.php', recursive=True):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if '<table' in content:
        if 'overflow' not in content:
            print(f"No overflow class AT ALL in {filepath}")
            total += 1

print(f"Total without any overflow: {total}")
