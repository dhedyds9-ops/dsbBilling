<?php

namespace App\Livewire\ResellerPortal\Network;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryUsage;
use App\Models\Inventory\InventoryUsageItem;
use App\Models\Inventory\Item;
use App\Models\ISP\Onu;
use App\Models\ISP\Vendor;
use App\Models\Support\Ticket;
use App\Services\ISP\OntLifecycleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class SwapOnt extends AdminComponent
{
    use WithFileUploads;

    // URL Parameters for deep-linking
    #[Url]
    public string $tab = 'swap'; // 'swap' | 'dismantle'

    #[Url]
    public ?int $customer_id = null;

    #[Url]
    public ?int $onu_id = null;

    #[Url]
    public ?int $ticket_id = null;

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

    // Mode Penggantian: 'ont' = Ganti Unit Modem (Swap ONT), 'accessory' = Ganti Adaptor / Aksesoris Saja (Modem Tetap)
    public string $swapMode = 'ont';

    // Aksesoris / Material Input (Ketika swapMode = 'accessory')
    public string $accessoryType = 'adaptor'; // 'adaptor', 'fatcore', 'patchcord', 'other'
    public ?int $selectedAccessoryItemId = null;
    public float $accessoryQuantity = 1.0;
    public string $accessoryReason = 'Adaptor Rusak / Tidak Menyala (Mati Total)';
    public string $accessoryNotes = '';

    // New ONT Inputs
    public string $newSerialNumber = '';
    public ?int $newVendorId = null;
    public string $newModel = '';
    public string $newMacAddress = '';
    public string $newWifiSsid = '';
    public string $newWifiPassword = '';
    public string $opticalPower = '-20.5';
    public string $replacementReason = 'Modem Rusak / Tidak Menyala';
    public string $oldDeviceCondition = 'rusak'; // 'rusak' (afkir), 'berfungsi' (refurbish/lab)
    public ?int $selectedMaterialId = null;
    public string $notes = '';

    // Proof of Work
    public $photoBefore = null;
    public $photoAfter = null;

    // State
    public bool $isProcessing = false;
    public bool $swapCompleted = false;
    public ?string $completedSummary = null;
    public string $transaction_id = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller';
        $this->activePage = 'network-swap-ont';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.onus.index')],
            ['label' => 'Ganti & Cabut', 'url' => '#'],
        ];

        $this->transaction_id = 'SWAP-' . (string) Str::uuid();

        if ($this->onu_id) {
            $this->selectOnu($this->onu_id);
        } elseif ($this->customer_id) {
            $this->selectCustomer($this->customer_id);
        } elseif ($this->ticket_id) {
            $this->loadTicket($this->ticket_id);
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['swap', 'dismantle']) ? $tab : 'swap';
    }

    /**
     * Dapatkan ID Reseller dari user login (SSOT Tenancy Guard).
     */
    protected function getResellerId(): ?int
    {
        $user = Auth::user();
        return $user ? ($user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null)) : null;
    }

    public function updatedSearchQuery()
    {
        $q = trim($this->searchQuery);
        if (strlen($q) < 2) {
            $this->searchResults = [];
            return;
        }

        $resellerId = $this->getResellerId();

        // Cari pelanggan terdaftar dengan layanan aktif & ONU (dibatasi scope reseller)
        $query = Customer::withoutGlobalScopes()
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
            });

        // Isolasi Reseller: Reseller hanya dapat mencari pelanggannya sendiri
        if ($resellerId) {
            $query->where(function ($resQ) use ($resellerId) {
                $resQ->where('reseller_id', $resellerId)
                     ->orWhere('created_by', $resellerId);
            });
        }

        $results = $query->with(['customerServices' => function ($csQ) {
            $csQ->whereNotNull('onu_id')->with(['onu.vendor', 'onu.olt', 'onu.odp', 'serviceProfile', 'pppoeUser']);
        }])
        ->limit(8)
        ->get();

        $this->searchResults = $results->map(function ($c) {
            $cs = $c->customerServices->first();
            return [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->customer_code ?? $c->code ?? ('CUST-' . $c->id),
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
        $resellerId = $this->getResellerId();
        $query = Ticket::withoutGlobalScopes()->with(['customer.customerServices.onu'])->where('id', $ticketId);

        if ($resellerId) {
            $query->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhereHas('customer', function ($cQ) use ($resellerId) {
                      $cQ->where('reseller_id', $resellerId);
                  });
            });
        }

        $this->ticket = $query->first();
        if ($this->ticket && $this->ticket->customer_id) {
            $this->selectCustomer($this->ticket->customer_id);
        }
    }

    public function selectCustomer(int $customerId)
    {
        $resellerId = $this->getResellerId();
        $query = Customer::withoutGlobalScopes()->where('id', $customerId);

        if ($resellerId) {
            $query->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhere('created_by', $resellerId);
            });
        }

        $this->customer = $query->first();
        if (!$this->customer) {
            session()->flash('error', 'Pelanggan tidak ditemukan atau berada di luar cakupan reseller Anda.');
            return;
        }

        $this->selectedCustomerId = $this->customer->id;

        $this->customerService = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->whereNotNull('onu_id')
            ->with(['onu.vendor', 'onu.olt', 'onu.odp', 'serviceProfile', 'pppoeUser', 'acsDevice'])
            ->latest('id')
            ->first();

        if ($this->customerService && $this->customerService->onu) {
            $this->populateOldOnu($this->customerService->onu);
        } elseif (!$this->customerService) {
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
        $resellerId = $this->getResellerId();
        $query = Onu::withoutGlobalScopes()
            ->with(['customerService.customer', 'customerService.serviceProfile', 'customerService.pppoeUser', 'vendor', 'olt', 'odp', 'acsDevice'])
            ->where('id', $onuId);

        if ($resellerId) {
            $query->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhereHas('customerService.customer', function ($cQ) use ($resellerId) {
                      $cQ->where('reseller_id', $resellerId);
                  });
            });
        }

        $onu = $query->first();
        if (!$onu) {
            session()->flash('error', 'Perangkat ONT tidak ditemukan atau berada di luar cakupan reseller Anda.');
            return;
        }

        $this->selectedOnuId = $onu->id;
        $this->customerService = $onu->customerService;
        $this->customer = $onu->customerService?->customer;
        $this->selectedCustomerId = $this->customer?->id;
        $this->populateOldOnu($onu);

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

        // Otomatis transfer SSID dan Password WiFi ke ONT Baru agar perangkat pelanggan langsung terhubung
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
            'newSerialNumber.required' => 'Serial Number ONT baru wajib diisi.',
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
                $this->addError('newSerialNumber', "SN {$newSn} sedang aktif digunakan oleh pelanggan ({$otherCustName}). Harap gunakan ONT lain.");
                return;
            }
        }

        $user = Auth::user();
        $this->isProcessing = true;

        try {
            $oldSn = $this->oldSerialNumber ?: ($this->oldOnu?->serial_number ?? 'ONT-LAMA');
            $photoBeforePath = $this->photoBefore ? $this->photoBefore->store('reseller/swap', 'public') : null;
            $photoAfterPath = $this->photoAfter ? $this->photoAfter->store('reseller/swap', 'public') : null;

            app(OntLifecycleService::class)->executeSwap([
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

    public function executeAccessoryReplacement()
    {
        $this->validate([
            'selectedCustomerId' => 'required',
            'accessoryType' => 'required|string',
            'accessoryQuantity' => 'required|numeric|min:0.1',
            'accessoryReason' => 'required|string',
        ], [
            'selectedCustomerId.required' => 'Pilih pelanggan yang akan diganti aksesorisnya.',
            'accessoryType.required' => 'Pilih jenis aksesoris yang diganti.',
            'accessoryQuantity.required' => 'Jumlah aksesoris wajib diisi.',
            'accessoryQuantity.min' => 'Jumlah minimal 0.1.',
            'accessoryReason.required' => 'Alasan penggantian wajib dipilih.',
        ]);

        $customer = $this->customer;
        if (!$customer) {
            session()->flash('error', 'Pelanggan tidak valid.');
            return;
        }

        $user = Auth::user();
        $this->isProcessing = true;

        try {
            $qtyNeeded = (float) $this->accessoryQuantity;
            $matType = strtolower(trim($this->accessoryType));
            $targetItemId = $this->selectedAccessoryItemId;
            $snClean = $this->oldSerialNumber ?: ($this->oldOnu?->serial_number ?? '');

            // 1. Cari custody match di tangan reseller
            $custody = null;
            if ($targetItemId) {
                $custody = InventoryCustody::withoutGlobalScopes()
                    ->where('holder_id', $user->id)
                    ->where('item_id', $targetItemId)
                    ->where('status', 'in_custody')
                    ->whereRaw('(quantity - COALESCE(used_quantity, 0) - COALESCE(returned_quantity, 0)) >= ' . (float) $qtyNeeded)
                    ->first();
            }

            if (!$custody) {
                $custody = InventoryCustody::withoutGlobalScopes()
                    ->where('holder_id', $user->id)
                    ->where('status', 'in_custody')
                    ->whereHas('item', function ($q) use ($matType) {
                        $q->where(function ($sub) use ($matType) {
                            if ($matType === 'adaptor') {
                                $sub->where('name', 'like', '%Adaptor%')->orWhere('sku', 'like', '%ADP%')->orWhere('name', 'like', '%Power%');
                            } elseif (in_array($matType, ['fatcore', 'fast_connector', 'connector', 'fc'])) {
                                $sub->where('name', 'like', '%Fast Connector%')->orWhere('name', 'like', '%Fatcore%')->orWhere('name', 'like', '%Konektor%');
                            } elseif (in_array($matType, ['patchcord', 'dropcore', 'kabel', 'dc'])) {
                                $sub->where('name', 'like', '%Patchcord%')->orWhere('name', 'like', '%Dropcore%')->orWhere('name', 'like', '%Drop Core%');
                            }
                        });
                    })
                    ->whereRaw('(quantity - COALESCE(used_quantity, 0) - COALESCE(returned_quantity, 0)) >= ' . (float) $qtyNeeded)
                    ->first();
            }

            $item = $custody?->item;
            if (!$item) {
                if ($targetItemId) {
                    $item = Item::withoutGlobalScopes()->find($targetItemId);
                } else {
                    $item = Item::withoutGlobalScopes()
                        ->where(function ($q) use ($matType) {
                            if ($matType === 'adaptor') {
                                $q->where('name', 'like', '%Adaptor%')->orWhere('sku', 'like', '%ADP%');
                            } elseif (in_array($matType, ['fatcore', 'fast_connector', 'connector', 'fc'])) {
                                $q->where('name', 'like', '%Fast Connector%')->orWhere('name', 'like', '%Fatcore%')->orWhere('name', 'like', '%Konektor%');
                            } elseif (in_array($matType, ['patchcord', 'dropcore', 'kabel', 'dc'])) {
                                $q->where('name', 'like', '%Patchcord%')->orWhere('name', 'like', '%Dropcore%');
                            }
                        })
                        ->first();
                }
            }

            $matLabel = match($matType) {
                'adaptor' => 'Adaptor 12V',
                'fatcore' => 'Fast Connector (Fatcore)',
                'patchcord' => 'Patchcord Fiber Optic',
                default => $item?->name ?? 'Aksesoris ONT',
            };

            DB::transaction(function () use ($user, $customer, $custody, $item, $qtyNeeded, $snClean, $matLabel) {
                if ($custody) {
                    $newUsed = (float) $custody->used_quantity + $qtyNeeded;
                    $isFullyUsed = ($newUsed + (float) $custody->returned_quantity) >= (float) $custody->quantity;
                    $custody->update([
                        'used_quantity' => $newUsed,
                        'status' => $isFullyUsed ? 'used' : 'in_custody',
                        'customer_id' => $customer->id,
                    ]);
                }

                $snTag = $snClean !== '' ? " [SN Modem: {$snClean}]" : '';
                $notes = "Penggantian {$matLabel} ({$this->accessoryReason}). " . ($this->accessoryNotes ?: '') . "{$snTag} oleh Reseller: {$user->name}";

                $usage = InventoryUsage::create([
                    'uuid' => (string) Str::uuid(),
                    'usage_number' => 'USG-REP-' . strtoupper(Str::random(8)),
                    'technician_id' => $user->id,
                    'customer_id' => $customer->id,
                    'ticket_id' => $this->ticket?->id,
                    'work_order_type' => 'repair_replacement',
                    'usage_date' => now()->toDateString(),
                    'notes' => $notes,
                    'branch_id' => $customer->branch_id ?? $user->branch_id,
                ]);

                InventoryUsageItem::create([
                    'usage_id' => $usage->id,
                    'custody_id' => $custody?->id,
                    'item_id' => $item?->id ?? ($custody?->item_id ?? 1),
                    'item_serial_id' => $custody?->item_serial_id,
                    'quantity' => $qtyNeeded,
                    'notes' => $notes,
                ]);
            });

            $this->completedSummary = "Penggantian {$matLabel} sebanyak {$qtyNeeded} unit berhasil dicatat untuk pelanggan [{$customer->name}]. Modem [{$snClean}] tetap aktif tanpa perubahan konfigurasi.";
            $this->swapCompleted = true;

            session()->flash('success', $this->completedSummary);
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses penggantian aksesoris: ' . $e->getMessage());
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
            'notes', 'photoBefore', 'photoAfter', 'swapCompleted', 'completedSummary',
            'accessoryNotes', 'selectedAccessoryItemId'
        ]);
        $this->swapMode = 'ont';
        $this->accessoryType = 'adaptor';
        $this->accessoryQuantity = 1.0;
        $this->accessoryReason = 'Adaptor Rusak / Tidak Menyala (Mati Total)';
        $this->opticalPower = '-20.5';
        $this->replacementReason = 'Modem Rusak / Tidak Menyala';
        $this->transaction_id = 'SWAP-' . (string) Str::uuid();
    }

    public function render()
    {
        $user = Auth::user();
        $ontMaterials = collect();
        $availableAdaptorStock = 0;
        $availableFatcoreStock = 0;
        $availablePatchcordStock = 0;
        $custodyAccessories = collect();

        // 1. Stok ONT & Aksesoris dari InventoryCustody milik reseller/staf
        if ($user && Schema::hasTable('inventory_custodies')) {
            $allCustodies = InventoryCustody::withoutGlobalScopes()
                ->with(['item', 'itemSerial'])
                ->where('holder_id', $user->id)
                ->where('status', 'in_custody')
                ->whereRaw('(quantity - COALESCE(used_quantity, 0) - COALESCE(returned_quantity, 0)) > 0')
                ->get();

            foreach ($allCustodies as $c) {
                $avail = max(0, (int) ((float) $c->quantity - (float) $c->used_quantity - (float) $c->returned_quantity));
                $name = strtolower($c->item?->name ?? '');
                $sku = strtolower($c->item?->sku ?? '');

                if (str_contains($name, 'onu') || str_contains($name, 'ont') || str_contains($name, 'modem') || str_contains($sku, 'onu') || str_contains($sku, 'ont')) {
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

                if (str_contains($name, 'adaptor') || str_contains($name, 'power') || str_contains($sku, 'adp')) {
                    $availableAdaptorStock += $avail;
                } elseif (str_contains($name, 'fast') || str_contains($name, 'connector') || str_contains($name, 'fatcore')) {
                    $availableFatcoreStock += $avail;
                } elseif (str_contains($name, 'patchcord') || str_contains($name, 'dropcore') || str_contains($name, 'drop core')) {
                    $availablePatchcordStock += $avail;
                }

                $custodyAccessories->push((object) [
                    'id' => $c->id,
                    'item_id' => $c->item_id,
                    'name' => $c->item?->name ?? 'Material',
                    'sku' => $c->item?->sku ?? '-',
                    'unit' => $c->item?->unit ?? 'unit',
                    'stock' => $avail,
                ]);
            }
        }

        // 2. Fallback untuk support_maintenance_materials
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

        return view('livewire.reseller-portal.network.swap-ont', [
            'ontMaterials' => $ontMaterials,
            'availableAdaptorStock' => $availableAdaptorStock,
            'availableFatcoreStock' => $availableFatcoreStock,
            'availablePatchcordStock' => $availablePatchcordStock,
            'custodyAccessories' => $custodyAccessories,
        ]);
    }
}
