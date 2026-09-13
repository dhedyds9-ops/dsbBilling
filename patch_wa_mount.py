import sys

file_php = 'D:/dsBilling/app/Livewire/Pengaturan/WhatsApp/Index.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = """    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage = 'whatsapp';
    }"""

replace = """    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage = 'whatsapp';

        // Load existing data from DB and merge with defaults
        $dbConn = \\App\\Models\\Setting::getValue('whatsapp.connection', []);
        $this->connection = array_merge($this->connection, $dbConn);

        $dbTemplates = \\App\\Models\\Setting::getValue('whatsapp.templates', []);
        if (!empty($dbTemplates)) {
            // Merge to preserve missing keys if we added new templates
            foreach ($this->templates as $i => $defaultTpl) {
                foreach ($dbTemplates as $dbTpl) {
                    if ($dbTpl['code'] === $defaultTpl['code']) {
                        $this->templates[$i] = array_merge($defaultTpl, $dbTpl);
                        break;
                    }
                }
            }
        }
    }"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("WhatsApp mount fixed!")
else:
    print("WhatsApp mount block not found.")
