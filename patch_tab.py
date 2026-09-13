file = 'D:/dsBilling/resources/views/livewire/admin/settings/index.blade.php'
with open(file, 'r', encoding='utf-8') as f:
    c = f.read()
c = c.replace("activeTab", "settingsTab")
with open(file, 'w', encoding='utf-8') as f:
    f.write(c)
print('Done')
