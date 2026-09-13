import sys

file_php = 'D:/dsBilling/app/Livewire/Pengaturan/WhatsApp/Index.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

search = """    public function save(): void
    {
        $this->validate();
        try {
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi WhatsApp berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }"""

replace = """    public function save(): void
    {
        $this->validate();
        try {
            \\App\\Models\\Setting::setValue('whatsapp.connection', $this->connection);
            \\App\\Models\\Setting::setValue('whatsapp.templates', $this->templates);
            $this->savedStatus = 'saved';
            session()->flash('success', 'Konfigurasi WhatsApp berhasil disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("WhatsApp save fixed!")
else:
    print("WhatsApp save block not found.")

