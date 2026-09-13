file_blade = 'D:/dsBilling/resources/views/livewire/pengaturan/whatsapp/index.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace <input type="password" wire:model="connection.api_token" ...> with autocomplete="new-password"
content = content.replace('wire:model="connection.api_token"', 'wire:model="connection.api_token" autocomplete="new-password"')

# Replace device_id with autocomplete="off"
content = content.replace('wire:model="connection.device_id"', 'wire:model="connection.device_id" autocomplete="off"')

# Might as well do webhook_secret
content = content.replace('wire:model="connection.webhook_secret"', 'wire:model="connection.webhook_secret" autocomplete="new-password"')

with open(file_blade, 'w', encoding='utf-8') as f:
    f.write(content)
print("Autocomplete attributes added!")
