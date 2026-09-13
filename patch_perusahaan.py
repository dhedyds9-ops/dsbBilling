import sys

file_blade = 'D:/dsBilling/resources/views/livewire/pengaturan/perusahaan/index.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = {
    'wire:model="company.legal"': 'wire:model="company.legal_name"',
    'wire:model="company.alamat"': 'wire:model="company.address"',
    'wire:model="company.kelurahan"': 'wire:model="company.village"',
    'wire:model="company.kecamatan"': 'wire:model="company.district"',
    'wire:model="company.kota"': 'wire:model="company.city"',
    'wire:model="company.provinsi"': 'wire:model="company.province"',
    'wire:model="company.kodepos"': 'wire:model="company.postal_code"',
    'wire:model="company.billing_support_email"': 'wire:model="company.billing_email"',
    'wire:model="company.ceo"': 'wire:model="company.ceo_name"',
    'wire:model="company.director"': 'wire:model="company.director_name"',
    'wire:model="company.finance"': 'wire:model="company.finance_name"',
    'wire:model="company.head_noc"': 'wire:model="company.head_noc_name"',
    'wire:model="company.established"': 'wire:model="company.established_date"',
    'wire:model="company.bank_1_no"': 'wire:model="company.bank_1_account"',
    'wire:model="company.bank_2_no"': 'wire:model="company.bank_2_account"',
    'wire:model="company.bank_3_no"': 'wire:model="company.bank_3_account"',
}

for old, new in replacements.items():
    content = content.replace(old, new)

with open(file_blade, 'w', encoding='utf-8') as f:
    f.write(content)

print("Perusahaan bindings fixed!")
