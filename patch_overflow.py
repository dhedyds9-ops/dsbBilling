import os
import glob
import re

count = 0
for filepath in glob.glob('D:/dsBilling/resources/views/**/*.blade.php', recursive=True):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    modified = False
    
    # Replace bootstrap class
    if 'table-responsive' in content:
        content = content.replace('class="table-responsive"', 'class="w-full overflow-x-auto"')
        content = content.replace("class='table-responsive'", 'class="w-full overflow-x-auto"')
        content = content.replace('table-responsive', 'w-full overflow-x-auto')
        modified = True
        
    # Fix Jetstream/Livewire stubs that use overflow-hidden on table wrappers
    if 'overflow-hidden' in content and '<table' in content:
        # We replace overflow-hidden with overflow-x-auto for the main wrapper
        # Often looks like: class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
        # We'll just do a regex to replace overflow-hidden with overflow-x-auto on divs that contain tables
        content = content.replace('overflow-hidden shadow-xl', 'overflow-x-auto shadow-xl')
        content = content.replace('overflow-hidden sm:rounded-lg', 'overflow-x-auto sm:rounded-lg')
        content = content.replace('overflow-hidden rounded', 'overflow-x-auto rounded')
        modified = True

    if modified:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        count += 1

print(f"Patched {count} files for responsive tables")
