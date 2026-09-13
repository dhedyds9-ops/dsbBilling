file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Index.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """    public function delete($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->delete();
        session()->flash('success', 'Data slip gaji berhasil dihapus. Anda bisa me-generate ulang.');
    }

    public function render()"""
    
replace = """    public function render()"""

if search in content:
    content = content.replace(search, replace)
    
    # Let's also translate the original delete method flash message
    search2 = """    public function delete($id)
    {
        Payroll::findOrFail($id)->delete();
        session()->flash('message', 'Payroll record deleted successfully.');
    }"""
    replace2 = """    public function delete($id)
    {
        Payroll::findOrFail($id)->delete();
        session()->flash('success', 'Data slip gaji berhasil dihapus. Anda dapat men-generate ulangnya.');
    }"""
    content = content.replace(search2, replace2)
    
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Fixed duplicate delete method")
else:
    print("Search block not found.")
