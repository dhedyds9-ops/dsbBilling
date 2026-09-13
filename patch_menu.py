import re

filepath = 'D:/dsBilling/app/Navigation/MenuRegistry.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

replacement = r'''
                      [
                          'label' => 'Kepegawaian',
                          'icon' => 'badge',
                          'items' => [
                              ['label' => 'Data Pegawai', 'route' => 'admin.employees.index', 'active' => 'admin.employees.*'],
                              ['label' => 'Rekap Absensi', 'route' => 'admin.attendance.index', 'active' => 'admin.attendance.*'],
                              ['label' => 'Rekap Gaji', 'route' => 'admin.payrolls.index', 'active' => 'admin.payrolls.*'],
                          ]
                      ],
'''

content = re.sub(
    r"\s*\[\s*'label'\s*=>\s*'Kepegawaian',\s*'icon'\s*=>\s*'badge',\s*'items'\s*=>\s*\[\s*\['label'\s*=>\s*'Rekap Absensi',\s*'route'\s*=>\s*'admin\.attendance\.index',\s*'active'\s*=>\s*'admin\.attendance\.\*'\],\s*\]\s*\],",
    replacement,
    content,
    flags=re.DOTALL
)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated MenuRegistry.php")
