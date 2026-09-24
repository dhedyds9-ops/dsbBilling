<?php
$file = 'app/Livewire/NOC/Onu/Index.php';
$content = file_get_contents($file);

$search = <<<PHP
                if (\$service) {
                    \$service->onu_id = \$onu->id;
                    \$service->save();
                    session()->flash('success', 'ONU berhasil dipasangkan ke pelanggan ' . \$service->customer->name);
                } else {
                    // Create a placeholder service if none exists
                    \$service = \App\Models\Customer\CustomerService::create([
                        'customer_id' => \$this->selectedCustomerId,
                        'onu_id' => \$onu->id,
                        'status' => 'active',
                    ]);
                    session()->flash('success', 'ONU dipasangkan ke pelanggan baru');
                }
PHP;

$replace = <<<PHP
                if (\$service) {
                    \$service->onu_id = \$onu->id;
                    \$service->save();
                    session()->flash('success', 'ONU berhasil dipasangkan ke pelanggan ' . \$service->customer->name);
                } else {
                    throw new \Exception('Pelanggan ini belum memiliki Layanan (Internet/Hotspot). Silakan buat layanan untuk pelanggan ini terlebih dahulu di menu Pelanggan.');
                }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed logic\n";
