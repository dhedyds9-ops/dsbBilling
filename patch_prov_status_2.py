file_php = 'D:/dsBilling/app/Services/Provisioning/ProvisioningStatusService.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = "$cs = $pipeline->serviceInstance->customerService;"
replace = "$cs = $pipeline->serviceInstance?->customerService;"

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fixed serviceInstance null access")
else:
    print("Search block not found.")
