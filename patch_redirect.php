<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/CreateHotspot.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Pass invoice_id to toast if possible
    // We'll replace the dispatch with an array payload
    $search1 = "session()->flash('success', 'Pelanggan & PPPoE User berhasil dibuat dan disinkronkan ke Router!');\n            \$this->dispatch('toast', type: 'success', message: 'Pendaftaran berhasil!');\n            return redirect()->route('reseller-portal.customers.index');";
    $replace1 = "\$this->dispatch('toast', [\n                'type' => 'success',\n                'message' => 'Pelanggan & PPPoE User berhasil dibuat!',\n                'invoice_id' => \$result['invoice']->id ?? null\n            ]);";
    
    $search2 = "session()->flash('success', 'Pelanggan & Hotspot User berhasil dibuat!');\n            \$this->dispatch('toast', type: 'success', message: 'Pendaftaran berhasil!');\n            return redirect()->route('reseller-portal.customers.hotspot');";
    $replace2 = "\$this->dispatch('toast', [\n                'type' => 'success',\n                'message' => 'Pelanggan & Hotspot User berhasil dibuat!',\n                'invoice_id' => \$result['invoice']->id ?? null\n            ]);";
    
    $content = str_replace($search1, $replace1, $content);
    $content = str_replace($search2, $replace2, $content);
    
    file_put_contents($file, $content);
}
echo "Removed backend redirects.\n";
?>
