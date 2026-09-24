<?php
$file = 'app/Livewire/NOC/Onu/Index.php';
$content = file_get_contents($file);

$search = "    #[\Livewire\Attributes\Url]\n    public int \$perPage = 25;";
$replace = <<<PHP
    #[\Livewire\Attributes\Url]
    public int \$perPage = 25;

    public \$editingOnuId = null;
    public \$selectedCustomerId = '';

    #[Computed]
    public function allCustomers()
    {
        return \App\Models\CRM\Customer::select('id', 'name', 'code')->orderBy('name')->get();
    }

    public function editCustomer(\$onuId, \$currentCustomerId = null)
    {
        \$this->editingOnuId = \$onuId;
        \$this->selectedCustomerId = \$currentCustomerId ?? '';
    }

    public function cancelEditCustomer()
    {
        \$this->editingOnuId = null;
        \$this->selectedCustomerId = '';
    }

    public function assignCustomer(\$onuId)
    {
        try {
            \$onu = \App\Models\ISP\Onu::findOrFail(\$onuId);
            
            // Unassign current service if exists
            if (\$onu->customerService) {
                \$onu->customerService->onu_id = null;
                \$onu->customerService->save();
            }

            if (\$this->selectedCustomerId) {
                // Assign to the first active service of the selected customer
                \$service = \App\Models\Customer\CustomerService::where('customer_id', \$this->selectedCustomerId)
                    ->orderBy('id', 'desc')
                    ->first();
                
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
            } else {
                session()->flash('success', 'ONU berhasil dilepas dari pelanggan');
            }
            
            \$this->editingOnuId = null;
            \$this->selectedCustomerId = '';
            
        } catch (\Throwable \$e) {
            session()->flash('error', 'Gagal mengupdate pelanggan: ' . \$e->getMessage());
        }
    }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added assignCustomer logic\n";
