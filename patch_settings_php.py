file_php = 'D:/dsBilling/app/Livewire/Admin/Settings/Index.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

php_logic = """
    public $app_name;
    public $company_name;
    public $company_address;
    public $company_phone;
    public $company_email;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'settings';
        
        $this->app_name = \\App\\Models\\Setting::getValue('app_name', 'dsBilling');
        $this->company_name = \\App\\Models\\Setting::getValue('company_name', 'PT DSBilling');
        $this->company_address = \\App\\Models\\Setting::getValue('company_address', '');
        $this->company_phone = \\App\\Models\\Setting::getValue('company_phone', '');
        $this->company_email = \\App\\Models\\Setting::getValue('company_email', '');
    }

    public function rules()
    {
        return [
            'app_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
        ];
    }

    public function save()
    {
        $this->validate();
        
        \\App\\Models\\Setting::setMany([
            'app_name' => $this->app_name,
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'company_phone' => $this->company_phone,
            'company_email' => $this->company_email,
        ], 'general');
        
        session()->flash('success', 'Pengaturan sistem berhasil disimpan.');
    }
"""

# Insert into class body before render
content = content.replace('public function mount()', php_logic.strip() + '\n\n    public function mount_override()')
content = content.replace('parent::mount();', '')
content = content.replace('public function mount_override()', '/* old mount */ public function mount_old()')
with open(file_php, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated Index.php")
