import os

files = [
    'D:/dsBilling/app/Models/Employee.php',
    'D:/dsBilling/app/Models/Payroll.php'
]

for file in files:
    with open(file, 'rb') as f:
        content = f.read()
    
    # Check for UTF-8 BOM
    if content.startswith(b'\xef\xbb\xbf'):
        content = content[3:]
        with open(file, 'wb') as f:
            f.write(content)
        print(f"Removed BOM from {file}")
