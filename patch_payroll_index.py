file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Index.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """    public function render()"""
replace = """    public function delete($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->delete();
        session()->flash('success', 'Data slip gaji berhasil dihapus. Anda bisa me-generate ulang.');
    }

    public function render()"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added delete method to Payroll Index")
else:
    print("Search block not found.")
