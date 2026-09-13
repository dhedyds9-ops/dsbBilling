file_php = 'D:/dsBilling/app/Services/Provisioning/ProvisioningStatusService.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = "'customer_service_status' => $cs->status,"
replace = "'customer_service_status' => $cs?->status ?? 'unknown',"

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fixed customer_service_status null access")
else:
    print("Search block not found.")
