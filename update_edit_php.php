<?php
$file = 'D:/dsBilling/app/Livewire/ACS/Device/Edit.php';
$content = file_get_contents($file);

// Add properties
if (strpos($content, 'public $wifi_ssid;') === false) {
    $content = str_replace('public $acs_error = \'\';', "public \$acs_error = '';\n\n    public \$wifi_ssid;\n    public \$wifi_password;", $content);
}

// Update syncFromGenieACS
$syncCode = <<<'PHP'
            $this->acs_error = '';

            // === WIFI CREDENTIALS ===
            try {
                $vendorName = $this->device->vendor?->name ?? 'default';
                $creds = $driver->getWifiCredentials($this->device->uuid, $vendorName);
                if (!empty($creds['ssid'])) {
                    $this->wifi_ssid = $creds['ssid'];
                }
                if (!empty($creds['password'])) {
                    $this->wifi_password = $creds['password'];
                }
            } catch (\Exception $e) {
                // Ignore wifi fetch errors
            }

        } catch (\Exception $e) {
PHP;
$content = preg_replace('/\$this->acs_error = \'\';\s*\} catch \(\\\\Exception \$e\) \{/', $syncCode, $content);

// Update save()
$saveCode = <<<'PHP'
        $validated = $this->validate([
            'serial_number' => 'nullable|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'model' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'wifi_ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
        ]);

        // Status, IP, dan MAC diisi otomatis dari GenieACS, bukan manual
        $validated['status'] = $this->status; // dari syncFromGenieACS
        $validated['ip_address'] = $this->ip_address;
        $validated['mac_address'] = $this->mac_address;
        $validated['updated_by'] = auth()->id();

        $this->device->update([
            'serial_number' => $validated['serial_number'],
            'vendor_id' => $validated['vendor_id'],
            'model' => $validated['model'],
            'notes' => $validated['notes'],
            'status' => $validated['status'],
            'ip_address' => $validated['ip_address'],
            'mac_address' => $validated['mac_address'],
            'updated_by' => $validated['updated_by'],
        ]);
        
        // Push to GenieACS if changed
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $vendorName = $this->device->vendor?->name ?? 'default';
            
            if ($this->wifi_ssid) {
                $driver->updateWifiSsid($this->device->uuid, $this->wifi_ssid, $vendorName);
            }
            if ($this->wifi_password) {
                $driver->updateWifiPassword($this->device->uuid, $this->wifi_password, $vendorName);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Device tersimpan, tapi gagal mengirim task WiFi ke GenieACS: ' . $e->getMessage());
            return redirect()->route('acs.devices.show', $this->deviceId);
        }

        session()->flash('success', 'Device dan pengaturan WiFi berhasil diperbarui!');
PHP;
$content = preg_replace('/\$validated = \$this->validate\(\[.*?session\(\)->flash\(\'success\', \'Device berhasil diperbarui!\'\);/s', $saveCode, $content);

file_put_contents($file, $content);
echo "Updated Edit.php";
?>
