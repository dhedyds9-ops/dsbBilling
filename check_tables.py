import os
import glob
import re

total_tables = 0
tables_without_overflow = 0

for filepath in glob.glob('D:/dsBilling/resources/views/livewire/**/*.blade.php', recursive=True):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if '<table' in content:
        total_tables += content.count('<table')
        
        # Simple check if "overflow-x-auto" is present before the table
        # A more robust check is whether the direct parent has overflow-x-auto.
        # Let's just find instances of table and see if "overflow-x-auto" is somewhere nearby
        if 'overflow-x-auto' not in content:
            tables_without_overflow += 1
            print(f"No overflow-x-auto found in {filepath}")
            
print(f"Total tables: {total_tables}, Files with table but no overflow-x-auto: {tables_without_overflow}")
