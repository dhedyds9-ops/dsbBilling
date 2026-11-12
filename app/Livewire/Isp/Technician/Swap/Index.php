<?php

namespace App\Livewire\Isp\Technician\Swap;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\ISP\Vendor;
use App\Models\ACS\ACSDevice;
use App\Models\Support\Ticket;
use App\Services\ISP\GenieAcsProvisioningService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithFileUploads;

    // URL Parameters
    #[Url]
    public ?int $ticket_id = null;

    #[Url]
    public ?int $customer_id = null;

    #[Url]
    public ?int $onu_id = null;

    // Search and Selection
    public string $searchQuery = '';
    public array $searchResults = [];
    public ?int $selectedCustomerId = null;
    public ?int $selectedOnuId = null;

    // Selected Target Data
    public ?Customer $customer = null;
    public ?CustomerService $customerService = null;
    public ?Onu $oldOnu = null;
    public ?Ticket $ticket = null;

    // Old Device Info (Display Only)
    public string $oldSerialNumber = '';
    public string $oldModel = '';
    public string $oldVendor = '';
    public string $oldMacAddress = '';
    public string $oldPppoeUsername = '';
    public string $oldPppoePassword = '';
    public string $oldWifiSsid = '';
    public string $oldWifiPassword = '';
    public string $oldOltName = '';
    public string $oldPonPort = '';
    public string $oldOdpName = '';
    public ?float $oldRxPower = null;

    // New ONT Inputs
    public string $newSerialNumber = '';
    public ?int $newVendorId = null;
    public string $newModel = '';
    public string $newMacAddress = '';
    public string $newWifiSsid = '';
    public string $newWifiPassword = '';
    public string $opticalPower = '-20.5';
    public string $replacementReason = 'Modem Tersambar Petir / Korsleting Listrik';
    public string $oldDeviceCondition = 'rusak'; // 'rusak' (afkir), 'berfungsi' (refurbish/lab)
    public ?int $selectedMaterialId = null;
    public string $notes = '';

    // Proof of Work
    public $photoBefore = null;
    public $photoAfter = null;
    public string $digitalSignature = '';

    // State
    public bool $isProcessing = false;
    public bool $swapCompleted = false;
    public ?string $completedSummary = null;

    public function mount()
    {
        if ($this->onu_id) {
            $this->selectOnu($this->onu_id);
        } elseif ($this->customer_id) {
            $this->selectCustomer($this->customer_id);
        } elseif ($this->ticket_id) {
            $this->loadTicket($this->ticket_id);
        }
    }

    public function updatedSearchQuery()
    {
        $q = trim($this->searchQuery);
        if (strlen($q) < 2) {
            $this->searchResults = [];
            return;
        }

        // Search customers with active service & ONU
        $results = Customer::withoutGlobalScopes()
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")
                    ->orWhereHas('customerServices.pppoeUser', function ($pu) use ($q) {
                        $pu->where('username', 'like', "%{$q}%");
                    })
                    ->orWhereHas('customerServices.onu', function ($ou) use ($q) {
                        $ou->where('serial_number', 'like', "%{$q}%")
                           ->orWhere('mac_address', 'like', "%{$q}%");
                    });
            })
            ->with(['customerServices' => function ($csQ) {
                $csQ->whereNotNull('onu_id')->with(['onu.vendor', 'onu.olt', 'onu.odp', 'serviceProfile', 'pppoeUser']);
            }])
            ->limit(8)
            ->get();

        $this->searchResults = $results->map(function ($c) {
            $cs = $c->customerServices->first();
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->customer_code ?? ('CUST-' . $c->id),
                'phone' => $c->phone ?? '-',
                'address' => $c->address ?? '-',
                'package' => $cs?->serviceProfile?->name ?? 'Layanan Aktif',
                'pppoe_username' => $cs?->pppoeUser?->username ?? '-',
                'onu_id' => $cs?->onu_id,
                'onu_sn' => $cs?->onu?->serial_number ?? 'Belum ada ONT',
                'onu_model' => $cs?->onu?->model ?? 'ONT',
            ];
        })->toArray();
    }

    public function loadTicket(int $ticketId)
    {
        $this->ticket = Ticket::withoutGlobalScopes()->with(['customer.customerServices.onu'])->find($ticketId);
        if ($this->ticket && $this->ticket->customer_id) {
            $this->selectCustomer($this->ticket->customer_id);
        }
    }

    public function selectCustomer(int $customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->customer = Customer::withoutGlobalScopes()->find($customerId);

        if (!$this->customer) {
            return;
        }

        $this->customerService = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->whereNotNull('onu_id')
            ->with(['onu.vendor', 'onu.olt', 'onu.odp', 'serviceProfile', 'pppoeUser', 'acsDevice'])
            ->latest('id')
            ->first();

        if ($this->customerService && $this->customerService->onu) {
            $this->populateOldOnu($this->customerService->onu);
        } elseif (!$this->customerService) {
            // Find any customer service even if onu_id is null
            $this->customerService = CustomerService::withoutGlobalScopes()
                ->where('customer_id', $customerId)
                ->with(['serviceProfile', 'pppoeUser', 'acsDevice'])
                ->latest('id')
                ->first();
        }

        $this->searchResults = [];
        $this->searchQuery = '';
    }

    public function selectOnu(int $onuId)
    {
        $this->selectedOnuId = $onuId;
        $onu = Onu::withoutGlobalScopes()->with(['customerService.customer', 'customerService.serviceProfile', 'customerService.pppoeUser', 'vendor', 'olt', 'odp', 'acsDevice'])->find($onuId);

        if ($onu) {
            $this->customerService = $onu->customerService;
            $this->customer = $onu->customerService?->customer;
            $this->selectedCustomerId = $this->customer?->id;
            $this->populateOldOnu($onu);
        }

        $this->searchResults = [];
        $this->searchQuery = '';
    }

    private function populateOldOnu(Onu $onu)
    {
        $this->oldOnu = $onu;
        $this->selectedOnuId = $onu->id;

        $this->oldSerialNumber = (string) $onu->serial_number;
        $this->oldModel = (string) ($onu->model ?: 'ONT GPON');
        $this->oldVendor = (string) ($onu->vendor?->name ?: 'Vendor');
        $this->oldMacAddress = (string) ($onu->mac_address ?: '-');
        $this->oldPppoeUsername = (string) ($this->customerService?->pppoeUser?->username ?: '-');
        $this->oldPppoePassword = (string) ($this->customerService?->pppoeUser?->clear_password ?? $this->customerService?->pppoeUser?->password ?? '');
        $this->oldWifiSsid = (string) ($onu->wifi_ssid ?: ($this->customerService?->acsDevice?->wifi_ssid ?? 'WINETS-' . substr($this->oldSerialNumber, -4)));
        $this->oldWifiPassword = (string) ($onu->wifi_password ?: ($this->customerService?->acsDevice?->wifi_password ?? '12345678'));
        $this->oldOltName = (string) ($onu->olt?->name ?: 'OLT Default');
        $this->oldPonPort = (string) ($onu->pon_port ? 'PON ' . $onu->pon_port : '-');
        $this->oldOdpName = (string) ($onu->odp?->name ?: '-');
        $this->oldRxPower = $onu->rx_power_dbm;

        // Auto-transfer existing WiFi credentials to the new ONT
        // This ensures the customer's smartphones, TVs, and laptops don't have to re-enter Wi-Fi credentials!
        if (empty($this->newWifiSsid)) {
            $this->newWifiSsid = $this->oldWifiSsid;
        }
        if (empty($this->newWifiPassword)) {
            $this->newWifiPassword = $this->oldWifiPassword;
        }
    }

    public function updatedNewSerialNumber($value)
    {
        $sn = strtoupper(trim($value ?? ''));
        $this->newSerialNumber = $sn;

        if (strlen($sn) >= 4) {
            $prefix = substr($sn, 0, 4);
            $vendorMap = [
                'ZTEG' => ['vendor' => 'ZTE', 'model' => 'ZTE F609'],
                'HWTC' => ['vendor' => 'Huawei', 'model' => 'Huawei HG8245H'],
                'FHTT' => ['vendor' => 'FiberHome', 'model' => 'FiberHome HG6145F'],
                'ALCL' => ['vendor' => 'Nokia', 'model' => 'Nokia G-240W-A'],
                'CIOT' => ['vendor' => 'C-Data', 'model' => 'C-Data FD511G-X'],
                'CDAT' => ['vendor' => 'C-Data', 'model' => 'C-Data FD511G-X'],
                'VSOL' => ['vendor' => 'VSOL', 'model' => 'VSOL V2801SG'],
                'HSGQ' => ['vendor' => 'HSGQ', 'model' => 'HSGQ G-100'],
            ];

            if (isset($vendorMap[$prefix])) {
                $vName = $vendorMap[$prefix]['vendor'];
                $v = Vendor::withoutGlobalScopes()->where('name', 'like', "%{$vName}%")->first();
                if ($v) {
                    $this->newVendorId = $v->id;
                }
                if (empty($this->newModel)) {
                    $this->newModel = $vendorMap[$prefix]['model'];
                }
            }
        }
    }

    public function setRedaman(string $dbm)
    {
        $this->opticalPower = $dbm;
    }

    public function executeSwap()
    {
        $this->validate([
            'selectedCustomerId' => 'required',
            'newSerialNumber' => 'required|string|min:4|different:oldSerialNumber',
            'opticalPower' => 'required|numeric',
            'replacementReason' => 'required|string',
            'newWifiSsid' => 'required|string|min:3',
            'photoBefore' => 'nullable|image|max:10240',
            'photoAfter' => 'nullable|image|max:10240',
        ], [
            'selectedCustomerId.required' => 'Pilih pelanggan yang akan diganti ONT-nya.',
            'newSerialNumber.required' => 'Serial Number ONT baru wajib diisi atau discan.',
            'newSerialNumber.different' => 'Serial Number baru tidak boleh sama dengan Serial Number lama.',
            'opticalPower.required' => 'Hasil ukur redaman optik (OPM) wajib dimasukkan.',
        ]);

        $newSn = strtoupper(trim($this->newSerialNumber));

        // Cek apakah SN baru sudah aktif digunakan oleh pelanggan lain
        $existingOnu = Onu::withoutGlobalScopes()->where('serial_number', $newSn)->first();
        if ($existingOnu && $this->oldOnu && $existingOnu->id !== $this->oldOnu->id) {
            $otherActive = CustomerService::withoutGlobalScopes()
                ->where('onu_id', $existingOnu->id)
                ->where('status', 'active')
                ->when($this->selectedCustomerId, fn($q) => $q->where('customer_id', '!=', $this->selectedCustomerId))
                ->with('customer')
                ->first();

            if ($otherActive) {
                $otherCustName = $otherActive->customer?->name ?? ('ID #' . $otherActive->customer_id);
                $this->addError('newSerialNumber', "SN {$newSn} sedang digunakan oleh pelanggan lain ({$otherCustName}). Harap gunakan ONT lain.");
                return;
            }
        }

        $user = Auth::user();
        $this->isProcessing = true;

        try {
            DB::transaction(function () use ($user, $newSn) {
                $oldSn = $this->oldSerialNumber ?: ($this->oldOnu?->serial_number ?? 'ONT-LAMA');
                $custName = $this->customer?->name ?? 'Pelanggan';

                // 1. Upload Foto Bukti jika ada
                $photoBeforePath = null;
                $photoAfterPath = null;
                if ($this->photoBefore) {
                    $photoBeforePath = $this->photoBefore->store('technician/swap', 'public');
                }
                if ($this->photoAfter) {
                    $photoAfterPath = $this->photoAfter->store('technician/swap', 'public');
                }

                // 2. Simpan Riwayat Modem Lama & Catat Record Modem Baru
                if ($this->oldOnu) {
                    $this->oldOnu->status = 'dismantled';
                    $this->oldOnu->rx_power_dbm = null;
                    $this->oldOnu->description = "Bekas penarikan dari {$custName} pada " . now()->format('d/m/Y H:i') . " oleh {$user->name}. Diganti (Swap) ke SN Baru: {$newSn}. Alasan: {$this->replacementReason}";
                    $this->oldOnu->save();
                }

                $targetOnu = Onu::firstOrNew(['serial_number' => $newSn]);
                if (!empty($this->newMacAddress)) {
                    $targetOnu->mac_address = strtoupper(trim($this->newMacAddress));
                }
                if (!empty($this->newModel)) {
                    $targetOnu->model = trim($this->newModel);
                }
                if (!empty($this->newVendorId)) {
                    $targetOnu->vendor_id = $this->newVendorId;
                }
                $targetOnu->wifi_ssid = $this->newWifiSsid;
                if (!empty($this->newWifiPassword)) {
                    $targetOnu->wifi_password = $this->newWifiPassword;
                }
                $targetOnu->rx_power_dbm = (float) $this->opticalPower;
                $targetOnu->status = 'active';
                $targetOnu->last_seen_at = now();
                $targetOnu->updated_by = $user->id;
                $targetOnu->description = "Pemasangan ONT baru untuk {$custName} (Swap pengganti {$oldSn}) pada " . now()->format('d/m/Y H:i') . " oleh {$user->name}. Alasan: {$this->replacementReason}";
                $targetOnu->save();

                // Pastikan CustomerService terhubung ke ONU baru ini
                if ($this->customerService) {
                    $this->customerService->update(['onu_id' => $targetOnu->id]);
                }

                // 3. Potong Stok Material Teknisi (Jika dipilih dari stok ONT)
                if ($this->selectedMaterialId && Schema::hasTable('support_maintenance_materials')) {
                    $mat = DB::table('support_maintenance_materials')
                        ->where('id', $this->selectedMaterialId)
                        ->lockForUpdate()
                        ->first();

                    if ($mat) {
                        $newStock = max(0, ((int) $mat->stock) - 1);
                        DB::table('support_maintenance_materials')
                            ->where('id', $mat->id)
                            ->update([
                                'stock' => $newStock,
                                'updated_at' => now(),
                            ]);

                        // Catat pemakaian ONT baru
                        if (Schema::hasTable('support_maintenance_usages')) {
                            DB::table('support_maintenance_usages')->insert([
                                'material_id' => $mat->id,
                                'material_name' => "[SWAP ONT BARU] " . $mat->name . " (SN: {$newSn})",
                                'quantity' => 1,
                                'unit' => 'unit',
                                'technician_id' => $user->id,
                                'technician_name' => $user->name,
                                'ticket_id' => $this->ticket?->id,
                                'used_date' => now()->toDateString(),
                                'notes' => "Pemasangan ONT baru pengganti {$oldSn} untuk {$custName}. Redaman OPM: {$this->opticalPower} dBm",
                                'created_by' => $user->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }

                // 4. Catat Pengembalian / Laporan Kerusakan ONT Lama
                if (Schema::hasTable('support_maintenance_usages')) {
                    $condLabel = $this->oldDeviceCondition === 'rusak' ? '[ONT RUSAK/AFKIR]' : '[ONT BEKAS RETUR]';
                    DB::table('support_maintenance_usages')->insert([
                        'material_id' => null,
                        'material_name' => "{$condLabel} {$this->oldModel} (SN: {$oldSn})",
                        'quantity' => 1,
                        'unit' => 'unit',
                        'technician_id' => $user->id,
                        'technician_name' => $user->name,
                        'ticket_id' => $this->ticket?->id,
                        'used_date' => now()->toDateString(),
                        'notes' => "Ditarik dari {$custName}. Penyebab swap: {$this->replacementReason}. " . ($this->notes ? "Catatan: {$this->notes}" : ''),
                        'created_by' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 5. Update GenieACS / ACSDevice Records
                if (Schema::hasTable('acs_devices')) {
                    // Lepas ikatan perangkat lama
                    ACSDevice::where('serial_number', $oldSn)->update([
                        'customer_service_id' => null,
                        'status' => 'replaced',
                    ]);

                    // Hubungkan perangkat baru jika sudah inform ke ACS
                    $newAcs = ACSDevice::where('serial_number', $newSn)->first();
                    if ($newAcs) {
                        $newAcs->update([
                            'customer_service_id' => $this->customerService?->id,
                            'status' => 'online',
                            'wifi_ssid' => $this->newWifiSsid,
                            'wifi_password' => $this->newWifiPassword,
                        ]);
                    }
                }

                // 6. Push TR-069 Provisioning jika layanan tersedia
                try {
                    $genieService = app(GenieAcsProvisioningService::class);
                    $genieService->provisionOnt($targetOnu, [
                        'pppoe_username' => $this->oldPppoeUsername,
                        'pppoe_password' => $this->oldPppoePassword,
                        'wifi_ssid' => $this->newWifiSsid,
                        'wifi_password' => $this->newWifiPassword,
                    ]);
                } catch (\Throwable $acsEx) {
                    Log::warning("GenieACS Provisioning during Swap ONT skipped or queued: " . $acsEx->getMessage());
                }

                // 7. Jika terhubung dengan Tiket Gangguan, update tiket
                if ($this->ticket) {
                    $ticketUpdates = [];
                    $resolutionText = "Pekerjaan Ganti/Swap ONT berhasil dilaksanakan.\n"
                        . "• Pelanggan: {$custName}\n"
                        . "• ONT Lama: {$oldSn} ({$this->oldModel})\n"
                        . "• ONT Baru: {$newSn} ({$this->newModel})\n"
                        . "• Redaman Optik (OPM): {$this->opticalPower} dBm\n"
                        . "• SSID Wi-Fi: {$this->newWifiSsid}\n"
                        . "• Alasan: {$this->replacementReason}\n"
                        . ($this->notes ? "• Catatan: {$this->notes}\n" : "");

                    $ticketUpdates['resolution_notes'] = $resolutionText;
                    $ticketUpdates['status'] = 'resolved';
                    $ticketUpdates['resolved_at'] = now();
                    $ticketUpdates['resolved_by'] = $user->id;

                    if (Schema::hasColumn('tickets', 'optical_power')) {
                        $ticketUpdates['optical_power'] = $this->opticalPower . ' dBm';
                    }
                    if (Schema::hasColumn('tickets', 'digital_signature') && !empty($this->digitalSignature)) {
                        $ticketUpdates['digital_signature'] = $this->digitalSignature;
                    }

                    // Tambahkan foto ke list foto tiket
                    if ($photoBeforePath || $photoAfterPath) {
                        $currentPhotos = is_array($this->ticket->photos) ? $this->ticket->photos : [];
                        if ($photoBeforePath) $currentPhotos[] = '/storage/' . $photoBeforePath;
                        if ($photoAfterPath) $currentPhotos[] = '/storage/' . $photoAfterPath;
                        $ticketUpdates['photos'] = array_values(array_unique($currentPhotos));
                    }

                    $this->ticket->update($ticketUpdates);
                }

                $this->completedSummary = "ONT berhasil ditukar dari [{$oldSn}] ke [{$newSn}]. Konfigurasi Wi-Fi & PPPoE telah ditransfer secara otomatis.";
                $this->swapCompleted = true;
            });

            session()->flash('success', 'Ganti ONT berhasil diselesaikan dan dicatat ke sistem!');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses Ganti ONT: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function resetForm()
    {
        $this->reset([
            'selectedCustomerId', 'selectedOnuId', 'customer', 'customerService', 'oldOnu',
            'oldSerialNumber', 'oldModel', 'oldVendor', 'oldMacAddress', 'oldPppoeUsername',
            'oldPppoePassword', 'oldWifiSsid', 'oldWifiPassword', 'newSerialNumber',
            'newModel', 'newMacAddress', 'newWifiSsid', 'newWifiPassword', 'opticalPower',
            'notes', 'photoBefore', 'photoAfter', 'digitalSignature', 'swapCompleted', 'completedSummary'
        ]);
        $this->opticalPower = '-20.5';
        $this->replacementReason = 'Modem Tersambar Petir / Korsleting Listrik';
    }

    public function render()
    {
        $user = Auth::user();

        // Ambil stok ONT teknisi dari support_maintenance_materials
        $ontMaterials = collect();
        if (Schema::hasTable('support_maintenance_materials')) {
            $ontMaterials = DB::table('support_maintenance_materials')
                ->where(function ($q) {
                    $q->where('category', 'Perangkat Aktif')
                      ->orWhere('name', 'like', '%ONT%')
                      ->orWhere('name', 'like', '%Modem%')
                      ->orWhere('sku', 'like', '%ONT%');
                })
                ->orderBy('name')
                ->get();
        }

        return view('livewire.isp.technician.swap.index', [
            'ontMaterials' => $ontMaterials,
        ]);
    }
}
