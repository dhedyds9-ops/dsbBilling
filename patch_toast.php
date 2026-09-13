<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/CreateHotspot.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    $search = "public function save(ProvisioningService \$provisioningService)\n    {";
    $replace = "public function save(ProvisioningService \$provisioningService)\n    {\n        try {\n            \$this->validate();\n        } catch (\\Illuminate\\Validation\\ValidationException \$e) {\n            \$this->dispatch('toast', type: 'error', message: 'Periksa kembali isian form Anda!');\n            throw \$e;\n        }";
    
    // Wait, they have explicit validate calls: $this->validate([...])
    // I need to intercept it. Let's just use regex.
    $content = preg_replace(
        '/(\$this->validate\(\[.*?\]\);)/s',
        "try {\n            $1\n        } catch (\\Illuminate\\Validation\\ValidationException \$e) {\n            \$this->dispatch('toast', type: 'error', message: 'Ada kolom wajib yang belum diisi atau salah!');\n            throw \$e;\n        }",
        $content
    );
    
    // Replace session flash with toast
    $content = str_replace(
        "session()->flash('success', 'Pelanggan & PPPoE User berhasil dibuat dan disinkronkan ke Router!');",
        "session()->flash('success', 'Pelanggan & PPPoE User berhasil dibuat dan disinkronkan ke Router!');\n            \$this->dispatch('toast', type: 'success', message: 'Pendaftaran berhasil!');",
        $content
    );
    
    $content = str_replace(
        "session()->flash('success', 'Pelanggan & Hotspot User berhasil dibuat!');",
        "session()->flash('success', 'Pelanggan & Hotspot User berhasil dibuat!');\n            \$this->dispatch('toast', type: 'success', message: 'Pendaftaran berhasil!');",
        $content
    );
    
    $content = str_replace(
        "session()->flash('error', 'Gagal menyimpan: ' . \$e->getMessage());",
        "\$this->dispatch('toast', type: 'error', message: 'Gagal: ' . \$e->getMessage());",
        $content
    );

    file_put_contents($file, $content);
    echo "Updated save method with toast in $file\n";
}
?>
