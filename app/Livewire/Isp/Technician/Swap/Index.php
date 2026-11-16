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
    public string $transaction_id = '';

    public function mount()
    {
        $this->transaction_id = 'SWAP-' . (string) Str::uuid();

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
            $oldSn = $this->oldSerialNumber ?: ($this->oldOnu?->serial_number ?? 'ONT-LAMA');
            $photoBeforePath = $this->photoBefore ? $this->photoBefore->store('technician/swap', 'public') : null;
            $photoAfterPath = $this->photoAfter ? $this->photoAfter->store('technician/swap', 'public') : null;

            app(\App\Services\ISP\OntLifecycleService::class)->executeSwap([
                'transaction_id' => $this->transaction_id ?: ('SWAP-' . (string) Str::uuid()),
                'old_onu_id' => $this->oldOnu?->id,
                'old_serial_number' => $oldSn,
                'new_serial_number' => $newSn,
                'new_mac_address' => $this->newMacAddress,
                'new_model' => $this->newModel,
                'new_vendor_id' => $this->newVendorId,
                'new_wifi_ssid' => $this->newWifiSsid,
                'new_wifi_password' => $this->newWifiPassword,
                'pppoe_username' => $this->oldPppoeUsername,
                'pppoe_password' => $this->oldPppoePassword,
                'optical_power' => $this->opticalPower,
                'replacement_reason' => $this->replacementReason,
                'old_device_condition' => $this->oldDeviceCondition === 'rusak' ? 'damaged' : 'good',
                'selected_material_id' => $this->selectedMaterialId,
                'notes' => $this->notes,
                'customer_id' => $this->selectedCustomerId,
                'customer_service_id' => $this->customerService?->id,
                'ticket_id' => $this->ticket?->id,
                'photo_before_path' => $photoBeforePath,
                'photo_after_path' => $photoAfterPath,
                'digital_signature' => $this->digitalSignature,
            ], $user);

            $this->completedSummary = "ONT berhasil ditukar dari [{$oldSn}] ke [{$newSn}]. Konfigurasi Wi-Fi & PPPoE telah ditransfer secara otomatis.";
            $this->swapCompleted = true;

            session()->flash('success', 'Ganti ONT berhasil diselesaikan dan dicatat ke sistem!');
        } catch (\InvalidArgumentException $e) {
            $this->addError('newSerialNumber', $e->getMessage());
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
        $this->transaction_id = 'SWAP-' . (string) Str::uuid();
    }

    public function render()
    {
        $user = Auth::user();

        $ontMaterials = collect();

        // 1. Utamakan stok ONT dari Enterprise InventoryCustody milik teknisi
        if ($user && Schema::hasTable('inventory_custodies')) {
            $custodies = \App\Models\Inventory\InventoryCustody::withoutGlobalScopes()
                ->with(['item', 'itemSerial'])
                ->where('holder_id', $user->id)
                ->where('status', 'in_custody')
                ->whereRaw('(quantity - used_quantity - returned_quantity) > 0')
                ->whereHas('item', function ($q) {
                    $q->where('name', 'like', '%ONU%')
                      ->orWhere('name', 'like', '%ONT%')
                      ->orWhere('name', 'like', '%Modem%')
                      ->orWhere('sku', 'like', '%ONU%')
                      ->orWhere('sku', 'like', '%ONT%');
                })
                ->get();

            foreach ($custodies as $c) {
                $avail = max(0, (int) ((float) $c->quantity - (float) $c->used_quantity - (float) $c->returned_quantity));
                $snSuffix = $c->itemSerial?->serial_number ? " [SN: {$c->itemSerial->serial_number}]" : '';
                $ontMaterials->push((object) [
                    'id' => $c->id,
                    'name' => ($c->item?->name ?? 'Modem ONT') . $snSuffix,
                    'sku' => $c->item?->sku ?? 'ONT',
                    'stock' => $avail,
                    'unit' => $c->item?->unit ?? 'unit',
                    'source' => 'custody',
                ]);
            }
        }

        // 2. Fallback / penggabungan dengan support_maintenance_materials untuk backward compatibility
        if ($ontMaterials->isEmpty() && Schema::hasTable('support_maintenance_materials')) {
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
