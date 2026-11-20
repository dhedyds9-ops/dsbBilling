<?php

namespace App\Livewire\Isp\Technician\Installation;


use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use App\Models\CRM\Customer;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\Provisioning\NetworkProfile;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Support\Str;

#[Layout('layouts.technician-app')]
class Wizard extends Component
{
    public $breadcrumbs = [];
    public $currentStep = 1;

    // Search Fields
    public $searchCustomer = '';
    public $searchOdp = '';

    // Form State
    public $is_new_customer = false;
    public $new_customer_name;
    public $new_customer_phone;
    public $new_customer_address;
    public $new_customer_reseller_id;

    public $customer_id;
    public string $customer_search = '';
    public $service_profile_id;
    public $service_type = 'pppoe'; // Default to PPPoE
    public string $package_filter_type = 'all'; // all, pppoe, hotspot
    public string $package_search = '';
    public $odp_id;
    public $olt_id;
    public $pon_port;
    public $onu_sn;
    public $onu_vendor_id;
    public $onu_model;

    // Deteksi ONT Unconfigured dari OLT
    public array $discoveredOnus = [];
    public bool $isScanningOlt = false;
    public string $oltScanMessage = '';

    public $custom_username = '';
    public $custom_password = '';

    public $wifi_ssid = '';
    public $wifi_password = '';

    // Konfigurasi IP Host TR-069 & OMCI OLT (Khusus C-Data & GPON/EPON)
    public bool $enable_tr069_iphost = true;
    public string $tr069_mode = 'dhcp'; // 'dhcp', 'static', 'pppoe'
    public string $tr069_vlan = '901';
    public string $tr069_static_ip = '';
    public string $tr069_netmask = '255.255.255.0';
    public string $tr069_gateway = '';
    public string $tr069_acs_url = '';

    // Field Service Proof & Material Consumption (P1-1B)
    public $optical_power = '';
    public $dropcore_meters = 0;
    public $fast_connector_qty = 0;

    // Step 7 Result Properties (P1-1A)
    public $provisioning_status = 'pending';
    public $provisioning_message = '';
    public $completed_pipeline_id = null;
    public $completed_onu_id = null;
    public $last_provisioned_at = null;

    public $network_profile_id;

    // Work Order & Security Enforcement
    public $ticket_id = null;
    public $installation_id = null;
    public $requisition_id = null;
    public $activeWorkOrder = null;
    public $isRestrictedTechnician = false;
    public $hasAccess = true;
    public $requireWorkOrderSelection = false;
    public $pendingTickets = [];
    public $pendingInstallations = [];
    public $pendingRequisitions = [];

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Technician Dashboard', 'url' => route('technician.dashboard')],
            ['label' => 'Pemasangan Baru', 'url' => '#'],
        ];

        $this->network_profile_id = NetworkProfile::withoutGlobalScopes()->where('type', 'pppoe')->first()?->id
            ?? NetworkProfile::withoutGlobalScopes()->first()?->id;

        $acsSettings = \App\Models\Setting::getValue('connection.acs', []);
        $this->tr069_acs_url = self::resolveAcsServerUrl();
        $this->tr069_vlan = (string) ($acsSettings['management_vlan'] ?? '901');

        $user = auth()->user();
        $isAdmin = $user && ($user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin', 'admin', 'manager', 'dispatcher']));
        $this->isRestrictedTechnician = !$isAdmin; // Staf lapangan terkunci pada SPK resmi; Admin/Dispatcher memiliki akses penuh mandiri

        // Auto pre-populate from query parameters (PSB, Work Order, CRM Installation, or Ticket)
        $customerId = request()->query('customer_id');
        $ticketId = request()->query('ticket');
        $installationId = request()->query('installation_id');
        $requisitionId = request()->query('requisition_id');

        if ($user) {
            // Cek seluruh tugas PSB aktif (Untuk teknisi: tugas dirinya. Untuk admin penguji: tugas sistem atau dirinya)
            $ticketQuery = \App\Models\Support\Ticket::withoutGlobalScopes()
                ->whereIn('category', ['psb', 'installation'])
                ->whereIn('status', ['open', 'in_progress', 'assigned'])
                ->with([
                    'customer' => fn($q) => $q->withoutGlobalScopes()->with('reseller'),
                    'reseller' => fn($q) => $q->withoutGlobalScopes(),
                ]);

            if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'installation_id')) {
                $ticketQuery->whereNull('installation_id');
            }

            $installationQuery = \App\Models\CRM\Installation::withoutGlobalScopes()
                ->whereIn('status', ['pending', 'scheduled', 'in_progress', 'assigned'])
                ->with([
                    'customer' => fn($q) => $q->withoutGlobalScopes()->with('reseller'),
                    'survey.prospect',
                ]);

            $hasRequisitionsTable = \Illuminate\Support\Facades\Schema::hasTable('inventory_requisitions');
            $requisitions = collect();
            if ($hasRequisitionsTable) {
                $requisitionQuery = \App\Models\Inventory\InventoryRequisition::withoutGlobalScopes()
                    ->where('work_order_type', 'psb')
                    ->whereIn('status', ['approved', 'dispatched'])
                    ->with(['customer' => fn($q) => $q->withoutGlobalScopes()]);
            }

            $hasTicketTechIds = \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'technician_ids');
            $hasInstTechIds = \Illuminate\Support\Facades\Schema::hasColumn('installations', 'technician_ids');

            $viewAll = request()->query('view_all', '0') === '1' && $isAdmin;
            if (!$viewAll) {
                // Kunci penugasan ke akun yang login (baik teknisi tunggal maupun anggota tim di technician_ids)
                $ticketQuery->where(function($q) use ($user, $hasTicketTechIds) {
                    $q->where('assigned_to', $user->id);
                    if ($hasTicketTechIds) {
                        $q->orWhereJsonContains('technician_ids', $user->id);
                    }
                });
                $installationQuery->where(function($q) use ($user, $hasInstTechIds) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('technician_id', $user->id);
                    if ($hasInstTechIds) {
                        $q->orWhereJsonContains('technician_ids', $user->id);
                    }
                });
                if ($hasRequisitionsTable) {
                    $requisitionQuery->where('designated_technician_id', $user->id);
                }
            }

            $tickets = $ticketQuery->latest()->get();
            $installations = $installationQuery->latest()->get();
            if ($hasRequisitionsTable) {
                $requisitions = $requisitionQuery->latest()->get();
            }

            $totalWorkOrders = $tickets->count() + $installations->count() + $requisitions->count();

            // 1. KUNCI TOTAL: Jika tidak ada Surat Tugas / SPK PSB apapun di sistem / untuk teknisi:
            if ($totalWorkOrders === 0 && empty($ticketId) && empty($installationId) && empty($requisitionId)) {
                if ($isAdmin) {
                    $this->hasAccess = true;
                    $this->requireWorkOrderSelection = false;
                } else {
                    $this->hasAccess = false;
                    $this->requireWorkOrderSelection = false;
                    return;
                }
            }

            // 2. Jika ada parameter tiket / SPK di URL:
            if ($ticketId) {
                $validTicket = $tickets->firstWhere('id', (int) $ticketId)
                    ?? \App\Models\Support\Ticket::withoutGlobalScopes()->where('id', $ticketId)->first();
                $isTicketAssigned = $validTicket && ($validTicket->assigned_to == $user->id || in_array($user->id, (array) ($validTicket->technician_ids ?? [])));
                if (!$validTicket || (!$isAdmin && !$isTicketAssigned)) {
                    $this->hasAccess = false;
                    session()->flash('error', 'Tiket Tugas PSB ini tidak ditugaskan kepada Anda atau statusnya sudah selesai.');
                    return;
                }
                $this->selectWorkOrder('ticket', $validTicket->id);
            } elseif ($installationId) {
                $validInst = $installations->firstWhere('id', (int) $installationId)
                    ?? \App\Models\CRM\Installation::withoutGlobalScopes()->where('id', $installationId)->first();
                $isInstAssigned = $validInst && ($validInst->assigned_to == $user->id || $validInst->technician_id == $user->id || in_array($user->id, (array) ($validInst->technician_ids ?? [])));
                if (!$validInst || (!$isAdmin && !$isInstAssigned)) {
                    $this->hasAccess = false;
                    session()->flash('error', 'SPK Pemasangan ini tidak valid atau ditugaskan kepada teknisi lain.');
                    return;
                }
                $this->selectWorkOrder('installation', $validInst->id);
            } elseif ($requisitionId) {
                $validReq = $requisitions->firstWhere('id', (int) $requisitionId);
                if (!$validReq) {
                    $this->hasAccess = false;
                    session()->flash('error', 'Permintaan Barang / SPK ini tidak ditemukan atau tidak valid.');
                    return;
                }
                $this->selectWorkOrder('requisition', $validReq->id);
            } else {
                // Ada tugas aktif: Wajibkan teknisi memilih tugas terlebih dahulu sebelum mulai
                $this->requireWorkOrderSelection = true;
                $this->pendingTickets = $tickets;
                $this->pendingInstallations = $installations;
                $this->pendingRequisitions = $requisitions;
            }
        }
    }

    public function selectWorkOrder(string $type, $id)
    {
        if ($type === 'ticket') {
            $ticket = \App\Models\Support\Ticket::withoutGlobalScopes()->with(['customer.reseller', 'reseller', 'resolvedBy'])->find($id);
            if ($ticket) {
                if (in_array($ticket->status, ['resolved', 'closed']) || !empty($ticket->resolved_by)) {
                    $completer = $ticket->resolvedBy?->name ?? 'teknisi lain';
                    $time = $ticket->resolved_at ? \Carbon\Carbon::parse($ticket->resolved_at)->format('d/m/Y H:i') : '';
                    session()->flash('error', "Tiket PSB #{$ticket->id} sudah diselesaikan oleh {$completer}" . ($time ? " pada {$time}" : "") . ". Tidak perlu dikerjakan ulang agar tidak terjadi duplikasi data.");
                    $this->hasAccess = false;
                    $this->requireWorkOrderSelection = false;
                    return;
                }

                $this->ticket_id = $ticket->id;
                $existingTicketCust = $ticket->customer_id ? Customer::withoutGlobalScopes()->find($ticket->customer_id) : null;
                if ($existingTicketCust) {
                    $this->customer_id = (string) $existingTicketCust->id;
                    $this->is_new_customer = false;
                } else {
                    $this->customer_id = null;
                    $this->is_new_customer = true;
                    $this->new_customer_name = $ticket->customer?->name ?? 'Pelanggan Baru';
                    $this->new_customer_phone = $ticket->customer?->phone ?? '-';
                    $this->new_customer_address = $ticket->customer?->address ?? '-';
                }

                $ticketResellerId = $ticket->reseller_id ?? $ticket->customer?->reseller_id;
                if ($ticketResellerId) {
                    $this->new_customer_reseller_id = (string) $ticketResellerId;
                }

                $this->activeWorkOrder = [
                    'type' => 'ticket',
                    'id' => $ticket->id,
                    'number' => $ticket->uuid ?? ('TIKET-PSB-' . $ticket->id),
                    'customer_name' => $ticket->customer?->name ?? 'Pelanggan #' . $ticket->customer_id,
                    'customer_phone' => $ticket->customer?->phone ?? '-',
                    'address' => $ticket->customer?->address ?? $ticket->description,
                    'reseller_name' => $ticket->reseller?->name ?? $ticket->customer?->reseller?->name ?? null,
                    'reseller_id' => $ticketResellerId,
                    'notes' => $ticket->description,
                ];
                $this->requireWorkOrderSelection = false;
                $this->hasAccess = true;
                $this->tryAutoDetectPackage($ticket->description, $ticket->customer_id);
            }
        } elseif ($type === 'installation') {
            $inst = \App\Models\CRM\Installation::withoutGlobalScopes()->with(['customer.reseller', 'survey.prospect.reseller', 'reseller', 'completedBy'])->find($id);
            if ($inst) {
                if ($inst->status === 'completed' || !empty($inst->completed_by)) {
                    $completer = $inst->completedBy?->name ?? 'teknisi lain';
                    $time = $inst->completed_at ? \Carbon\Carbon::parse($inst->completed_at)->format('d/m/Y H:i') : '';
                    session()->flash('error', "SPK Pemasangan #{$inst->id} sudah diselesaikan oleh {$completer}" . ($time ? " pada {$time}" : "") . ". Tidak perlu dikerjakan ulang agar tidak terjadi duplikasi data.");
                    $this->hasAccess = false;
                    $this->requireWorkOrderSelection = false;
                    return;
                }

                $this->installation_id = $inst->id;
                // Cek tiket yang tertaut ke SPK instalasi ini
                if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'installation_id')) {
                    $linkedTicket = \App\Models\Support\Ticket::withoutGlobalScopes()->where('installation_id', $inst->id)->first();
                    if ($linkedTicket) {
                        $this->ticket_id = $linkedTicket->id;
                    }
                }

                // Smart Fallback Parser dari notes jika kolom khusus kosong (SPK lama / manual legacy)
                $notesPhone = null;
                $notesAddress = null;
                $notesPackage = null;
                if (!empty($inst->notes)) {
                    if (preg_match('/(?:Telp|Phone|WA|WhatsApp):\s*([0-9+\s\-]+?)(?:\s*\||\n|$)/i', $inst->notes, $m)) {
                        $notesPhone = trim($m[1]);
                    }
                    if (preg_match('/(?:Alamat|Address):\s*([^|\n]+?)(?:\s*\||\n|$)/i', $inst->notes, $m)) {
                        $notesAddress = trim($m[1]);
                    }
                    if (preg_match('/(?:Paket|Package):\s*([^|\n]+?)(?:\s*\||\n|$)/i', $inst->notes, $m)) {
                        $notesPackage = trim($m[1]);
                    }
                }

                $instResellerId = $inst->reseller_id 
                    ?? $inst->customer?->reseller_id 
                    ?? $inst->survey?->prospect?->reseller_id;

                $resellerName = $inst->reseller?->name 
                    ?? $inst->customer?->reseller?->name 
                    ?? $inst->survey?->prospect?->reseller?->name 
                    ?? null;

                if ($instResellerId) {
                    $this->new_customer_reseller_id = (string) $instResellerId;
                }

                $resolvedPhone = $inst->customer?->phone 
                    ?: ($inst->customer_phone 
                    ?: ($inst->survey?->prospect?->phone 
                    ?: ($notesPhone ?? '')));

                $resolvedAddress = $inst->installation_address 
                    ?: ($inst->customer?->address 
                    ?: ($inst->survey?->address 
                    ?: ($inst->survey?->prospect?->address 
                    ?: ($notesAddress ?? ''))));

                $existingCust = $inst->customer_id ? Customer::withoutGlobalScopes()->find($inst->customer_id) : null;
                if ($existingCust) {
                    $this->customer_id = (string) $existingCust->id;
                    $this->is_new_customer = false;
                } elseif ($inst->survey && $inst->survey->prospect) {
                    $this->customer_id = null;
                    $this->is_new_customer = true;
                    $this->new_customer_name = $inst->survey->prospect->name;
                    $this->new_customer_phone = $resolvedPhone;
                    $this->new_customer_address = $resolvedAddress;
                } else {
                    $this->customer_id = null;
                    $this->is_new_customer = true;
                    $this->new_customer_name = $inst->customer_name ?: 'Pelanggan Baru';
                    $this->new_customer_phone = $resolvedPhone;
                    $this->new_customer_address = $resolvedAddress;
                }

                $this->activeWorkOrder = [
                    'type' => 'installation',
                    'id' => $inst->id,
                    'number' => $inst->uuid ?? ('SPK-INST-' . $inst->id),
                    'customer_name' => $inst->customer?->name ?? ($inst->survey?->prospect?->name ?? ($inst->customer_name ?: 'Pelanggan Baru')),
                    'customer_phone' => $resolvedPhone ?: '-',
                    'address' => $resolvedAddress ?: 'Alamat belum diatur',
                    'reseller_name' => $resellerName,
                    'reseller_id' => $instResellerId,
                    'notes' => $inst->notes,
                ];
                $this->requireWorkOrderSelection = false;
                $this->hasAccess = true;

                $packageToDetect = $inst->package_name ?: ($notesPackage ?: $inst->notes);
                $this->tryAutoDetectPackage($packageToDetect, $inst->customer_id);
            }
        } elseif ($type === 'requisition' && \Illuminate\Support\Facades\Schema::hasTable('inventory_requisitions')) {
            $req = \App\Models\Inventory\InventoryRequisition::withoutGlobalScopes()->with(['customer'])->find($id);
            if ($req) {
                $this->requisition_id = $req->id;
                if ($req->customer_id) {
                    $this->customer_id = (string) $req->customer_id;
                    $this->is_new_customer = false;
                }
                $this->activeWorkOrder = [
                    'type' => 'requisition',
                    'id' => $req->id,
                    'number' => $req->requisition_number,
                    'customer_name' => $req->customer?->name ?? 'Pelanggan #' . $req->customer_id,
                    'customer_phone' => $req->customer?->phone ?? '-',
                    'address' => $req->customer?->address ?? $req->notes,
                    'notes' => $req->notes,
                ];
                $this->requireWorkOrderSelection = false;
                $this->hasAccess = true;
            }
        }
    }

    public function clearWorkOrder()
    {
        if ($this->isRestrictedTechnician) {
            $this->requireWorkOrderSelection = true;
            $this->activeWorkOrder = null;
            $this->ticket_id = null;
            $this->installation_id = null;
            $this->requisition_id = null;
            $this->customer_id = null;
        } else {
            $this->activeWorkOrder = null;
            $this->ticket_id = null;
            $this->installation_id = null;
            $this->requisition_id = null;
        }
    }

    public function selectPackage($id)
    {
        $user = auth()->user();
        $profile = ServiceProfile::withoutGlobalScopes()->find($id);
        if ($profile && $profile->isFreePackage() && (!$user || !$user->isSuperAdmin())) {
            $this->dispatch('toast', type: 'error', message: 'Paket Gratis (0 Rupiah) hanya dapat dipilih oleh Administrator.');
            return;
        }

        $this->service_profile_id = (string) $id;
        $this->updatedServiceProfileId($id);
    }

    public function setPackageFilterType(string $type)
    {
        $this->package_filter_type = in_array($type, ['all', 'pppoe', 'hotspot', 'voucher']) ? $type : 'all';
    }

    protected function tryAutoDetectPackage(?string $notes = null, ?int $customerId = null): void
    {
        try {
            if ($this->service_profile_id) {
                return;
            }

            // 1. Cek dari customer service existing
            if ($customerId) {
                $existingCs = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                    ->where('customer_id', $customerId)
                    ->whereNotNull('service_profile_id')
                    ->latest()
                    ->first();
                if ($existingCs && $existingCs->service_profile_id) {
                    $this->selectPackage($existingCs->service_profile_id);
                    return;
                }
            }

            // 2. Cek dari teks notes SPK
            if (!empty($notes)) {
                $allProfiles = ServiceProfile::withoutGlobalScopes()->get(['id', 'name']);
                foreach ($allProfiles as $prof) {
                    if (stripos($notes, $prof->name) !== false) {
                        $this->selectPackage($prof->id);
                        return;
                    }
                }
            }

            // 3. Fallback: jika hanya ada 1 profil paket di database, pilih otomatis
            $profilesCount = ServiceProfile::withoutGlobalScopes()->count();
            if ($profilesCount === 1) {
                $single = ServiceProfile::withoutGlobalScopes()->first();
                if ($single) {
                    $this->selectPackage($single->id);
                }
            }
        } catch (\Throwable) {
            // silent ignore
        }
    }

    public function updatedServiceProfileId($value)
    {
        if ($value) {
            $profile = ServiceProfile::withoutGlobalScopes()->find($value);
            $rawType = strtolower($profile?->service_type ?? '');
            $this->service_type = in_array($rawType, ['hotspot', 'voucher']) ? 'hotspot' : 'pppoe';
            $this->network_profile_id = NetworkProfile::withoutGlobalScopes()
                ->where('type', $this->service_type)
                ->orderByDesc('vlan_id')
                ->first()?->id
                ?? NetworkProfile::withoutGlobalScopes()->first()?->id;
        }
    }

    public function updatedOdpId($value)
    {
        if ($value) {
            $odp = Odp::withoutGlobalScopes()->find($value);
            if ($odp && !empty($odp->olt_id) && empty($this->olt_id)) {
                $this->olt_id = (string) $odp->olt_id;
            }
            if ($odp && !empty($odp->pon_port_id) && empty($this->pon_port)) {
                $this->pon_port = (string) $odp->pon_port_id;
            }
        }
    }

    public function updatedOnuSn($value)
    {
        $sn = strtoupper(trim($value ?? ''));
        if (strlen($sn) >= 4) {
            $prefix = substr($sn, 0, 4);
            $vendorMap = [
                'ZTEG' => ['vendor' => 'ZTE', 'model' => 'F609'],
                'HWTC' => ['vendor' => 'Huawei', 'model' => 'HG8245H'],
                'FHTT' => ['vendor' => 'FiberHome', 'model' => 'HG6145F'],
                'ALCL' => ['vendor' => 'Nokia', 'model' => 'G-240W-A'],
                'CIOT' => ['vendor' => 'C-Data', 'model' => 'FD511G-X'],
            ];
            if (isset($vendorMap[$prefix])) {
                $vName = $vendorMap[$prefix]['vendor'];
                $v = \App\Models\ISP\Vendor::withoutGlobalScopes()->where('name', 'like', "%{$vName}%")->first();
                if ($v && empty($this->onu_vendor_id)) {
                    $this->onu_vendor_id = (string) $v->id;
                }
                if (empty($this->onu_model)) {
                    $this->onu_model = $vendorMap[$prefix]['model'];
                }
            }
            if (empty($this->wifi_ssid) && strlen($sn) >= 4) {
                $this->wifi_ssid = 'WINETS-' . substr($sn, -4);
            }
        }
    }

    public function updatedOltId($value)
    {
        $this->discoveredOnus = [];
        $this->oltScanMessage = '';
    }

    public function detectUnregisteredOnus(): void
    {
        $this->discoveredOnus = [];
        $this->oltScanMessage = '';

        if (empty($this->olt_id)) {
            $this->oltScanMessage = 'Pilih OLT terlebih dahulu sebelum memindai.';
            return;
        }

        $olt = Olt::withoutGlobalScopes()->find($this->olt_id);
        if (!$olt) {
            $this->oltScanMessage = 'Data OLT tidak ditemukan di sistem.';
            return;
        }

        $discovered = [];
        try {
            $registry = app(\App\Services\Adapters\Provisioning\OltRegistry::class);
            $driver = $registry->forOlt($olt);
            $rawDiscovered = $driver->discoverUnregisteredOnus();
            if (is_array($rawDiscovered) && !empty($rawDiscovered)) {
                $discovered = array_values(array_filter($rawDiscovered, function ($item) {
                    $sn = strtoupper(trim($item['serial_number'] ?? ''));
                    return \App\Services\Adapters\Provisioning\Drivers\BaseOltDriver::isValidOnuSerial($sn);
                }));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("[Wizard::detectUnregisteredOnus] OLT {$olt->id} discovery error: " . $e->getMessage());
            $this->oltScanMessage = 'Peringatan koneksi OLT: ' . $e->getMessage();
        }

        // Brownfield / Offline Inventory Fallback jika belum ada ont unconfigured realtime
        if (empty($discovered)) {
            $dbQuery = \App\Models\ISP\Onu::withoutGlobalScopes()
                ->where('olt_id', $olt->id)
                ->whereIn('provision_status', ['unprovisioned', 'pending', 'new']);
            
            $dbOnus = $dbQuery->limit(10)->get();
            foreach ($dbOnus as $dbOnu) {
                $sn = strtoupper(trim($dbOnu->serial_number));
                if (\App\Services\Adapters\Provisioning\Drivers\BaseOltDriver::isValidOnuSerial($sn)) {
                    $discovered[] = [
                        'pon_port' => (int) ($dbOnu->pon_port ?: 1),
                        'onu_id' => (int) ($dbOnu->onu_id_on_olt ?: 1),
                        'serial_number' => $sn,
                        'vendor_oui' => strtoupper(substr($sn, 0, 4)),
                        'source' => 'inventory',
                    ];
                }
            }
        }

        $this->discoveredOnus = $discovered;

        if (empty($this->discoveredOnus)) {
            $this->oltScanMessage = 'Tidak ditemukan ONT unconfigured (autofind) pada OLT ini. Pastikan modem ONT sudah terhubung ke kabel fiber dan lampu PON menyala.';
        } else {
            $this->oltScanMessage = '';
        }
    }

    public function selectDiscoveredOnu(string $sn, ?int $ponPort = null): void
    {
        $cleanSn = strtoupper(trim($sn));
        $this->onu_sn = $cleanSn;
        if (!empty($ponPort)) {
            $this->pon_port = (string) $ponPort;
        }
        $this->updatedOnuSn($cleanSn);
    }

    public function nextStep()
    {
        $this->validateStep();
        if ($this->currentStep < 6) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1 && $this->currentStep < 7) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step)
    {
        if ($step >= 1 && $step <= 6 && $this->currentStep < 7) {
            $this->currentStep = $step;
        }
    }

    protected function validateStep()
    {
        if ($this->currentStep === 1) {
            if ($this->isRestrictedTechnician && !$this->activeWorkOrder) {
                $this->addError('general', 'Anda wajib memilih Surat Perintah Kerja (SPK) / Tiket tugas yang ditugaskan sebelum melanjutkan instalasi.');
                return;
            }

            if ($this->is_new_customer) {
                if ($this->isRestrictedTechnician && empty($this->installation_id)) {
                    $this->addError('general', 'Teknisi lapangan tidak diizinkan membuat pelanggan baru di luar surat tugas resmi. Hubungi Admin/Dispatcher.');
                    return;
                }
                $this->validate([
                    'new_customer_name' => 'required|string|max:255',
                    'new_customer_phone' => 'required|numeric',
                    'new_customer_address' => 'required|string',
                    'new_customer_reseller_id' => 'nullable|exists:users,id',
                ]);

                $existing = Customer::withoutGlobalScopes()
                    ->where('phone', $this->new_customer_phone)
                    ->first();
                if ($existing && $existing->customerServices()->exists() && $existing->name !== $this->new_customer_name) {
                    $this->addError('new_customer_phone', "Nomor HP ini sudah aktif digunakan oleh pelanggan lain ({$existing->name}).");
                }
            } else {
                $this->validate(['customer_id' => 'required|exists:customers,id']);
            }
        } elseif ($this->currentStep === 2) {
            $this->validate(['service_profile_id' => 'required|exists:service_profiles,id']);
            $sp = ServiceProfile::withoutGlobalScopes()->find($this->service_profile_id);
            $user = auth()->user();
            if ($sp && $sp->isFreePackage() && (!$user || !$user->isSuperAdmin())) {
                $this->addError('service_profile_id', 'Hanya Administrator yang memiliki izin untuk memilih Paket Gratis (0 Rupiah).');
                return;
            }
        } elseif ($this->currentStep === 3) {
            // ODP bersifat opsional (jika belum ada data ODP atau sambungan langsung)
            if (!empty($this->odp_id)) {
                $this->validate(['odp_id' => 'nullable|exists:odps,id']);
                $odp = Odp::withoutGlobalScopes()->find($this->odp_id);
                if ($odp && $odp->availablePortCount <= 0) {
                    $this->addError('odp_id', "ODP {$odp->name} sudah penuh (kapasitas 0 port tersedia). Silakan pilih ODP lain atau lewati tanpa ODP.");
                }
            }
        } elseif ($this->currentStep === 4) {
            $this->validate([
                'olt_id' => 'required|exists:olts,id',
                'pon_port' => 'required|numeric|min:1',
            ]);
        } elseif ($this->currentStep === 5) {
            $this->validate([
                'onu_sn' => 'required|string|min:4',
                'onu_vendor_id' => 'required|exists:vendors,id',
                'onu_model' => 'required|string|max:255',
            ]);

            if (!empty($this->wifi_password) && strlen(trim($this->wifi_password)) < 8) {
                $this->addError('wifi_password', 'Password Wi-Fi minimal 8 karakter (standar keamanan WPA2).');
            }

            $sn = strtoupper(trim($this->onu_sn));
            $existingOnu = \App\Models\ISP\Onu::where('serial_number', $sn)->first();
            if ($existingOnu) {
                $activeOtherCs = \App\Models\Customer\CustomerService::where('onu_id', $existingOnu->id)
                    ->where('status', 'active')
                    ->when($this->customer_id, fn($q) => $q->where('customer_id', '!=', $this->customer_id))
                    ->with('customer')
                    ->first();
                if ($activeOtherCs) {
                    $otherName = $activeOtherCs->customer?->name ?? 'Pelanggan #' . $activeOtherCs->customer_id;
                    $this->addError('onu_sn', "ONU SN {$sn} sedang aktif digunakan oleh pelanggan lain ({$otherName}). Tidak dapat dialihkan secara sepihak.");
                }
            }
        }
    }

    public function submit(ProvisioningService $provisioningService)
    {
        // 1. Validasi Komprehensif Seluruh Tahapan Sebelum Eksekusi Database
        if ($this->is_new_customer) {
            $this->validate([
                'new_customer_name' => 'required|string|max:255',
                'new_customer_phone' => 'required|numeric',
                'new_customer_address' => 'required|string',
                'new_customer_reseller_id' => 'nullable|exists:users,id',
            ]);
        } else {
            $this->validate(['customer_id' => 'required']);
        }

        $this->validate([
            'service_profile_id' => 'required|exists:service_profiles,id',
            'odp_id' => 'nullable|exists:odps,id',
            'olt_id' => 'required|exists:olts,id',
            'pon_port' => 'required|numeric|min:1',
            'onu_sn' => 'required|string|min:4',
            'onu_vendor_id' => 'required|exists:vendors,id',
            'onu_model' => 'required|string|max:255',
        ]);

        $checkProfile = ServiceProfile::withoutGlobalScopes()->find($this->service_profile_id);
        $currentUser = auth()->user();
        if ($checkProfile && $checkProfile->isFreePackage() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            $this->dispatch('toast', type: 'error', message: 'Aktivasi ditolak: Hanya Administrator yang dapat memilih Paket Gratis (0 Rupiah).');
            return;
        }

        if (!empty($this->wifi_password) && strlen(trim($this->wifi_password)) < 8) {
            $this->addError('wifi_password', 'Password Wi-Fi minimal 8 karakter (standar keamanan WPA2).');
            return;
        }

        // 2. Validasi & Penegakan Branch Isolation (P0-4)
        $user = auth()->user();

        // Proteksi Anti-Duplikasi Pengerjaan (Hanya 1 teknisi yang menyelesaikan)
        if ($this->installation_id) {
            $instCheck = \App\Models\CRM\Installation::withoutGlobalScopes()->with('completedBy')->find($this->installation_id);
            if ($instCheck && ($instCheck->status === 'completed' || !empty($instCheck->completed_by))) {
                $completer = $instCheck->completedBy?->name ?? 'teknisi lain';
                $this->addError('general', "Pemasangan SPK ini telah diselesaikan oleh {$completer}. Aktivasi dibatalkan untuk menghindari duplikasi data.");
                return;
            }
        }
        if ($this->ticket_id) {
            $ticketCheck = \App\Models\Support\Ticket::withoutGlobalScopes()->with('resolvedBy')->find($this->ticket_id);
            if ($ticketCheck && (in_array($ticketCheck->status, ['resolved', 'closed']) || !empty($ticketCheck->resolved_by))) {
                $completer = $ticketCheck->resolvedBy?->name ?? 'teknisi lain';
                $this->addError('general', "Tiket ini telah diselesaikan oleh {$completer}. Aktivasi dibatalkan untuk menghindari duplikasi data.");
                return;
            }
        }

        $branchId = $user->branch_id;
        if (!$branchId && $this->is_new_customer && $this->new_customer_reseller_id) {
            $reseller = \App\Models\User::withoutGlobalScopes()->find($this->new_customer_reseller_id);
            $branchId = $reseller?->branch_id;
        } elseif (!$branchId && $this->customer_id) {
            $existingCust = Customer::withoutGlobalScopes()->find($this->customer_id);
            $branchId = $existingCust?->branch_id ?? $existingCust?->reseller?->branch_id;
        }

        // Resolusi cabang dari infrastruktur (ODP / OLT) jika belum terikat
        if (!$branchId && !empty($this->odp_id)) {
            $branchId = Odp::withoutGlobalScopes()->where('id', $this->odp_id)->value('branch_id');
        }
        if (!$branchId && !empty($this->olt_id)) {
            $branchId = Olt::withoutGlobalScopes()->where('id', $this->olt_id)->value('branch_id');
        }

        // Fallback jika user adalah Administrator/Superadmin oversight atau branch belum terikat
        if (!$branchId) {
            $defaultBranch = \App\Models\Master\Branch::first();
            if (!$defaultBranch) {
                try {
                    $defaultBranch = \App\Models\Master\Branch::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'code' => 'PST',
                        'name' => 'Kantor Pusat',
                        'is_active' => true,
                    ]);
                } catch (\Throwable $e) {}
            }
            $branchId = $defaultBranch?->id;
        }

        // Jika user teknisi memiliki branch_id, pastikan tidak lintas cabang
        if ($user->branch_id && $branchId && (int) $user->branch_id !== (int) $branchId) {
            session()->flash('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk melakukan instalasi di luar cabang Anda (Branch Isolation).');
            $this->addError('general', 'Instalasi di luar cabang kantor Anda tidak diizinkan.');
            return;
        }

        if (!$branchId) {
            session()->flash('error', 'Cabang kantor (Branch) Anda atau Reseller belum dikonfigurasi di sistem. Silakan hubungi Administrator untuk menetapkan cabang sebelum instalasi.');
            $this->addError('general', 'Cabang kantor Anda belum ditentukan.');
            return;
        }

        // 3. Validasi Awal Kapasitas ODP & Format ONU (P0-3)
        $sn = strtoupper(trim($this->onu_sn));

        // 4. Eksekusi Atomic Database Transaction dengan Concurrency Locking (P0-H3)
        try {
            $executionResult = \Illuminate\Support\Facades\DB::transaction(function () use ($provisioningService, $branchId, $sn, $user) {
                // A. Concurrency Lock & Reservasi Kapasitas Port ODP (Opsional jika ODP ditentukan)
                if (!empty($this->odp_id)) {
                    $odp = Odp::withoutGlobalScopes()->where('id', $this->odp_id)->lockForUpdate()->first();
                    if ($odp) {
                        if ($odp->availablePortCount <= 0) {
                            throw new \DomainException("ODP " . ($odp->name ?? $this->odp_id) . " sudah penuh atau tidak memiliki sisa port.");
                        }
                        $odp->increment('used_port_count');
                    }
                }

                // B. Concurrency Lock & Verifikasi Konflik Kepemilikan ONU (Global Cross-Branch Aware)
                $existingOnu = \App\Models\ISP\Onu::withoutGlobalScopes()
                    ->where('serial_number', $sn)
                    ->lockForUpdate()
                    ->first();

                if ($existingOnu) {
                    $activeOtherCs = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                        ->where('onu_id', $existingOnu->id)
                        ->where('status', 'active')
                        ->when($this->customer_id, fn($q) => $q->where('customer_id', '!=', $this->customer_id))
                        ->with('customer')
                        ->first();

                    if ($activeOtherCs) {
                        $otherName = $activeOtherCs->customer?->name ?? 'Pelanggan #' . $activeOtherCs->customer_id;
                        throw new \DomainException("ONU SN {$sn} sedang aktif digunakan oleh {$otherName}. Tidak dapat dialihkan.");
                    }
                }

                // C. Simpan atau Perbarui Data Pelanggan
                if ($this->is_new_customer) {
                    $existing = Customer::withoutGlobalScopes()
                        ->where('phone', $this->new_customer_phone)
                        ->first();

                    if ($existing) {
                        $customer = $existing;
                        $customer->update([
                            'name' => $this->new_customer_name ?: $customer->name,
                            'address' => $this->new_customer_address ?: $customer->address,
                            'reseller_id' => $this->new_customer_reseller_id ?: $customer->reseller_id,
                            'branch_id' => $branchId,
                        ]);
                    } else {
                        $customerCode = \App\Services\CRM\CustomerCodeGenerator::generate();
                        $customer = Customer::create([
                            'code' => $customerCode,
                            'name' => $this->new_customer_name,
                            'phone' => $this->new_customer_phone,
                            'address' => $this->new_customer_address,
                            'reseller_id' => $this->new_customer_reseller_id,
                            'branch_id' => $branchId,
                            'status' => 'active',
                            'created_by' => $user->id,
                        ]);
                    }
                    $this->customer_id = $customer->id;
                } else {
                    $customer = Customer::withoutGlobalScopes()->find($this->customer_id);
                    if (!$customer) {
                        // Jika record customer_id tidak ditemukan, buat pelanggan baru otomatis agar flow teknisi tidak macet
                        $customerCode = \App\Services\CRM\CustomerCodeGenerator::generate();
                        $custName = $this->activeWorkOrder['customer_name'] ?? ($this->new_customer_name ?: 'Pelanggan Baru');
                        $custPhone = $this->activeWorkOrder['customer_phone'] ?? ($this->new_customer_phone ?: null);
                        $custAddr = $this->activeWorkOrder['address'] ?? ($this->new_customer_address ?: '-');
                        $customer = Customer::create([
                            'code' => $customerCode,
                            'name' => $custName,
                            'phone' => $custPhone,
                            'address' => $custAddr,
                            'reseller_id' => $this->new_customer_reseller_id ?: null,
                            'branch_id' => $branchId,
                            'status' => 'active',
                            'created_by' => $user->id,
                        ]);
                        $this->customer_id = $customer->id;
                    } else {
                        $updatePayload = [];
                        if (empty($customer->branch_id) && $branchId) {
                            $updatePayload['branch_id'] = $branchId;
                        }
                        if (!empty($this->new_customer_reseller_id) && $customer->reseller_id != $this->new_customer_reseller_id) {
                            $updatePayload['reseller_id'] = $this->new_customer_reseller_id;
                        }
                        if (!empty($updatePayload)) {
                            $customer->update($updatePayload);
                        }
                    }
                }

                $companyName = \App\Models\Setting::getValue('company.name', 'dsbilling');
                $companySuffix = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $companyName));

                $username = !empty(trim($this->custom_username ?? ''))
                    ? trim($this->custom_username)
                    : strtolower($customer->code) . ($this->service_type === 'pppoe' ? '@' . $companySuffix : '');

                $password = !empty(trim($this->custom_password ?? ''))
                    ? trim($this->custom_password)
                    : ($this->service_type === 'hotspot' ? substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6) : 'pppoe');

                // E. Simpan atau perbarui ONU dengan branch_id terikat
                $wifiSsid = !empty(trim($this->wifi_ssid ?? '')) ? trim($this->wifi_ssid) : ('WINETS-' . substr($sn, -4));
                $wifiPass = !empty(trim($this->wifi_password ?? '')) ? trim($this->wifi_password) : null;

                if (!$existingOnu) {
                    $onu = \App\Models\ISP\Onu::create([
                        'olt_id' => $this->olt_id,
                        'pon_port' => $this->pon_port,
                        'serial_number' => $sn,
                        'vendor_id' => $this->onu_vendor_id,
                        'model' => $this->onu_model,
                        'name' => 'ONU ' . $customer->name,
                        'status' => 'inactive',
                        'code' => 'ONU-' . substr(strtoupper(md5(time())), 0, 6),
                        'branch_id' => $branchId,
                        'wifi_ssid' => $wifiSsid,
                        'wifi_password' => $wifiPass,
                        'provision_status' => 'queued',
                    ]);
                } else {
                    $existingOnu->update([
                        'olt_id' => $this->olt_id ?: $existingOnu->olt_id,
                        'pon_port' => $this->pon_port ?: $existingOnu->pon_port,
                        'vendor_id' => $this->onu_vendor_id ?: $existingOnu->vendor_id,
                        'model' => $this->onu_model ?: $existingOnu->model,
                        'name' => 'ONU ' . $customer->name,
                        'branch_id' => $branchId,
                        'wifi_ssid' => $wifiSsid ?: $existingOnu->wifi_ssid,
                        'wifi_password' => $wifiPass ?: $existingOnu->wifi_password,
                        'provision_status' => 'queued',
                    ]);
                    $onu = $existingOnu;
                }

                $npId = $this->network_profile_id 
                    ?: NetworkProfile::withoutGlobalScopes()->where('type', $this->service_type)->orderByDesc('vlan_id')->value('id')
                    ?: NetworkProfile::withoutGlobalScopes()->value('id');

                $npModel = $npId ? NetworkProfile::withoutGlobalScopes()->find($npId) : null;
                $targetRouterId = $npModel?->router_id 
                    ?: (\App\Models\ISP\Router::withoutGlobalScopes()->where('branch_id', $branchId)->value('id')
                    ?: \App\Models\ISP\Router::withoutGlobalScopes()->value('id'));

                // Pastikan targetRouterId benar-benar ada di tabel routers untuk menghindari foreign key violation
                $resolvedRouterId = ($targetRouterId && \App\Models\ISP\Router::withoutGlobalScopes()->where('id', $targetRouterId)->exists())
                    ? $targetRouterId 
                    : null;

                $data = [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'service_profile_id' => $this->service_profile_id,
                    'network_profile_id' => $npId,
                    'router_id' => $resolvedRouterId,
                    'username' => $username,
                    'password' => $password,
                    'status' => 'active',
                    'reseller_id' => $customer->reseller_id ?: ($this->new_customer_reseller_id ?: null),
                    'branch_id' => $branchId,
                    'odp_id' => $this->odp_id,
                    'onu_id' => $onu->id,
                    'mac_address' => $sn,
                ];

                if ($this->service_type === 'pppoe') {
                    $result = $provisioningService->activatePPPoEService($data, $user->id);
                    $cs = $result['customer_service'];

                    return [
                        'type' => 'pppoe',
                        'customer_service' => $cs,
                        'onu' => $onu,
                        'username' => $username,
                        'password' => $password,
                        'network_profile_id' => $npId,
                    ];
                } else {
                    $result = $provisioningService->activateHotspotService($data, $user->id);
                    $cs = $result['customer_service'] ?? null;

                    return [
                        'type' => 'hotspot',
                        'customer_service' => $cs,
                        'onu' => $onu,
                        'username' => $username,
                        'password' => $password,
                        'network_profile_id' => $npId,
                    ];
                }
            });

            // 6. Eksekusi Non-Blocking Post-Commit Actions (di luar database transaction)
            $onu = $executionResult['onu'];
            $username = $executionResult['username'] ?? '';
            $password = $executionResult['password'] ?? '';
            $npId = $executionResult['network_profile_id'] ?? null;
            $cs = $executionResult['customer_service'] ?? null;
            $this->completed_onu_id = $onu->id;
            $this->last_provisioned_at = now()->format('d M Y H:i:s');

            if ($executionResult['type'] === 'pppoe' && $cs) {
                try {
                    $serviceInstance = $provisioningService->startProvisioning($cs, $user->id);
                    $this->completed_pipeline_id = $serviceInstance->provisionPipeline?->id;
                    $this->provisioning_status = 'queued';
                    $this->provisioning_message = 'Instalasi PPPoE berhasil disimpan. Task provisioning ONT telah dikirim ke GenieACS NBI.';
                } catch (\Throwable $pe) {
                    \Illuminate\Support\Facades\Log::warning('[Wizard] Pipeline provisioning post-commit non-fatal: ' . $pe->getMessage());
                    $this->completed_pipeline_id = null;
                    $this->provisioning_status = 'pending';
                    $this->provisioning_message = 'Data pelanggan & layanan PPPoE berhasil disimpan. Task hardware OLT dijadwalkan ulang: ' . $pe->getMessage();
                }
            } else {
                $this->completed_pipeline_id = null;
                try {
                    $jobEngine = app(\App\Services\Provisioning\OnuConfigurationJobEngine::class);
                    $bridgeDesiredState = [
                        'bridge' => [
                            'lan' => ['1', '2', '3', '4'],
                            'ssid' => ['1'],
                        ],
                    ];
                    $jobEngine->dispatchProvisioningJob($onu, $bridgeDesiredState, 'BRIDGE', null, $user->id);

                    $ssidName = strtoupper(\App\Models\Setting::getValue('company.name', 'WINETS.ID'));
                    $wifiService = app(\App\Services\Provisioning\WifiConfigurationService::class);
                    $wifiService->configureWifi($onu, [
                        1 => [
                            'ssid' => $ssidName,
                            'security' => 'None',
                            'password' => '',
                            'enable' => true,
                        ],
                    ], null, $user->id);

                    // Auto-Provisioning WAN PPPoE_Bridged & Port Binding via GenieACS (Hotspot ZTP)
                    $npVlan = $npId ? (\App\Models\ISP\NetworkProfile::withoutGlobalScopes()->where('id', $npId)->value('vlan_id') ?? 300) : 300;
                    $acsProv = app(\App\Services\ISP\GenieAcsProvisioningService::class);
                    $acsProv->provisionHotspotBridge($onu, [
                        'vlan_id' => $npVlan,
                        'wifi_ssid' => $ssidName,
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('[Wizard] Gagal push konfigurasi WiFi/Bridge ke ONU: ' . $e->getMessage());
                }

                $this->provisioning_status = 'queued';
                $this->provisioning_message = 'Layanan Hotspot Member & konfigurasi Bridge (PPPoE_Bridged + LAN 1-4 + SSID 1) berhasil dieksekusi.';
            }

            // Eksekusi Injeksi WAN & IP Host TR-069 OMCI ke OLT (Zero-Touch Provisioning)
            if ($this->enable_tr069_iphost && !empty($this->olt_id)) {
                try {
                    $oltModel = Olt::withoutGlobalScopes()->find($this->olt_id);
                    if ($oltModel) {
                        $driver = app(\App\Services\Adapters\Provisioning\OltRegistry::class)->forOlt($oltModel);
                        $npVlan = $npId ? (\App\Models\ISP\NetworkProfile::withoutGlobalScopes()->where('id', $npId)->value('vlan_id') ?? 200) : 200;
                        $driver->pushOnuWanConfig($onu, [
                            'mode' => $this->service_type === 'hotspot' ? 'dhcp' : 'pppoe',
                            'vlan_id' => $npVlan,
                            'username' => $username,
                            'password' => $password,
                            'tr069_mode' => $this->tr069_mode,
                            'tr069_vlan' => (int) ($this->tr069_vlan ?: 901),
                            'tr069_static_ip' => $this->tr069_static_ip,
                            'tr069_netmask' => $this->tr069_netmask,
                            'tr069_gateway' => $this->tr069_gateway,
                            'acs_url' => $this->tr069_acs_url,
                        ]);
                    }
                } catch (\Throwable $oe) {
                    \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal push IP Host TR-069 ke OLT via OMCI: ' . $oe->getMessage());
                }
            }

            // ==========================================
            // Potong Barang dari Bagasi / Custody Teknisi & Reseller (Enterprise Inventory)
            // ==========================================
            try {
                $stockService = app(\App\Services\Inventory\StockMutationService::class);
                $stockService->recordPsbConsumption(
                    technicianId: $user->id,
                    customerId: $customer->id,
                    onuSn: $sn,
                    dropcoreMeters: (float) $this->dropcore_meters,
                    fastConnectorQty: (int) $this->fast_connector_qty,
                    opticalPower: $this->optical_power ?: null,
                    installationId: $this->installation_id ?: null,
                    ticketId: $this->ticket_id ?: null,
                    requisitionId: $this->requisition_id ?: null,
                    resellerId: $customer->reseller_id ?: ($this->new_customer_reseller_id ?: null)
                );

                if (isset($onu) && $onu && \Illuminate\Support\Facades\Schema::hasTable('onu_histories')) {
                    \App\Models\ISP\OnuHistory::withoutGlobalScopes()->create([
                        'onu_id' => $onu->id,
                        'serial_number' => $onu->serial_number,
                        'customer_id' => $customer->id,
                        'old_status' => 'spare',
                        'new_status' => 'active',
                        'condition' => 'good',
                        'old_olt_id' => null,
                        'new_olt_id' => $onu->olt_id,
                        'old_odp_id' => null,
                        'new_odp_id' => $onu->odp_id,
                        'activity' => 'psb',
                        'technician_id' => $user->id,
                        'ticket_id' => $this->ticket_id ?: null,
                        'branch_id' => $onu->branch_id,
                        'description' => "Aktivasi PSB via Wizard Teknisi oleh {$user->name}",
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal potong custody material PSB: ' . $e->getMessage());
            }

            // Fallback backward-compatibility untuk support_maintenance_materials
            if (($this->dropcore_meters > 0 || $this->fast_connector_qty > 0) && \Illuminate\Support\Facades\Schema::hasTable('support_maintenance_materials')) {
                try {
                    if ($this->dropcore_meters > 0) {
                        $dc = \Illuminate\Support\Facades\DB::table('support_maintenance_materials')->where('name', 'like', '%Dropcore%')->first();
                        if ($dc) {
                            \Illuminate\Support\Facades\DB::table('support_maintenance_materials')->where('id', $dc->id)->decrement('stock', (int) $this->dropcore_meters);
                        }
                        \Illuminate\Support\Facades\DB::table('support_maintenance_usages')->insert([
                            'material_id' => $dc?->id,
                            'material_name' => $dc?->name ?? 'Kabel Dropcore Optik 1 Core',
                            'quantity' => (int) $this->dropcore_meters,
                            'unit' => 'meter',
                            'technician_id' => $user->id,
                            'technician_name' => $user->name,
                            'used_date' => now()->toDateString(),
                            'notes' => 'PSB Pelanggan ' . ($this->new_customer_name ?? $this->selectedCustomer?->name ?? 'Pelanggan'),
                            'created_by' => $user->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    if ($this->fast_connector_qty > 0) {
                        $fc = \Illuminate\Support\Facades\DB::table('support_maintenance_materials')->where('name', 'like', '%Fast Connector%')->first();
                        if ($fc) {
                            \Illuminate\Support\Facades\DB::table('support_maintenance_materials')->where('id', $fc->id)->decrement('stock', (int) $this->fast_connector_qty);
                        }
                        \Illuminate\Support\Facades\DB::table('support_maintenance_usages')->insert([
                            'material_id' => $fc?->id,
                            'material_name' => $fc?->name ?? 'Fast Connector SC/UPC',
                            'quantity' => (int) $this->fast_connector_qty,
                            'unit' => 'pcs',
                            'technician_id' => $user->id,
                            'technician_name' => $user->name,
                            'used_date' => now()->toDateString(),
                            'notes' => 'PSB Pelanggan ' . ($this->new_customer_name ?? $this->selectedCustomer?->name ?? 'Pelanggan'),
                            'created_by' => $user->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal catat pemakaian material legacy: ' . $e->getMessage());
                }
            }

            // 7. Update status tiket / surat perintah kerja terkait menjadi selesai & sinkronkan data
            if ($this->ticket_id) {
                try {
                    \App\Models\Support\Ticket::withoutGlobalScopes()
                        ->where('id', $this->ticket_id)
                        ->update([
                            'customer_id' => $customer->id,
                            'status' => 'resolved',
                            'resolved_at' => now(),
                            'resolved_by' => $user->id,
                            'resolution_notes' => 'PSB berhasil diaktivasi oleh teknisi ' . $user->name . ' pada ' . now()->format('d/m/Y H:i') . ' (ONU SN: ' . $sn . ')',
                        ]);
                } catch (\Throwable $te) {
                    \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal update status tiket: ' . $te->getMessage());
                }
            }

            if ($this->installation_id) {
                try {
                    $instModel = \App\Models\CRM\Installation::withoutGlobalScopes()->with(['survey.prospect'])->find($this->installation_id);
                    if ($instModel) {
                        $instModel->update([
                            'customer_id' => $customer->id,
                            'status' => 'completed',
                            'completed_at' => now(),
                            'completed_by' => $user->id,
                            'onu_serial_number' => $sn,
                            'onu_id' => $onu->id,
                        ]);

                        // Tandai Survey & Prospect sebagai activated resmi (agar sinkron di CRM Aktivasi)
                        if ($instModel->survey) {
                            $instModel->survey->update(['status' => 'activated']);
                            if ($instModel->survey->prospect) {
                                $instModel->survey->prospect->update(['status' => 'activated']);
                            }
                        }

                        // Jika ada tiket tertaut ke SPK instalasi ini, update statusnya menjadi resolved
                        if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'installation_id')) {
                            \App\Models\Support\Ticket::withoutGlobalScopes()
                                ->where('installation_id', $instModel->id)
                                ->where('status', '!=', 'resolved')
                                ->where('status', '!=', 'closed')
                                ->update([
                                    'customer_id' => $customer->id,
                                    'status' => 'resolved',
                                    'resolved_at' => now(),
                                    'resolution_notes' => 'PSB SPK #' . $instModel->id . ' selesai dipasang & diaktivasi oleh teknisi ' . $user->name . ' (ONU SN: ' . $sn . ')',
                                ]);
                        }
                    }
                } catch (\Throwable $ie) {
                    \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal update status installation: ' . $ie->getMessage());
                }
            }

            // Catat Aktivasi resmi pada CRM
            try {
                \App\Models\CRM\Activation::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'customer_name' => $customer->name,
                    'activation_date' => now()->toDateString(),
                    'service_package' => $selectedProfile?->name ?? strtoupper($this->service_type),
                    'status' => 'completed',
                    'notes' => 'Aktivasi resmi via Wizard PSB Teknisi ' . $user->name . ' (ONU SN: ' . $sn . ')',
                    'created_by' => $user->id,
                ]);
            } catch (\Throwable $ae) {
                \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal catat CRM Activation: ' . $ae->getMessage());
            }

            $this->currentStep = 7;
            session()->flash('success', 'Pemasangan Baru (PSB) Berhasil Disimpan!');
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
            $this->addError('general', $e->getMessage());
            return;
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            session()->flash('error', "ONU SN {$sn} sudah terdaftar pada sistem (duplikasi dicegah oleh database).");
            $this->addError('onu_sn', "ONU SN {$sn} sudah terdaftar.");
            return;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[Wizard] Gagal memproses instalasi: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Gagal memproses instalasi: ' . $e->getMessage());
            $this->addError('general', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    #[Renderless]
    public function searchCustomers(string $query = ''): array
    {
        $q = trim($query);

        return Customer::withoutGlobalScopes()
            ->whereRaw('LOWER(name) NOT LIKE ?', ['guest%'])
            ->whereRaw('LOWER(code) NOT LIKE ?', ['gst-%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', ['tamu%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', ['voucher%'])
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->take(50)
            ->get(['id', 'name', 'phone', 'code', 'address'])
            ->map(function ($c) {
                $name = !empty(trim($c->name)) ? $c->name : 'Pelanggan #' . $c->id;
                $details = array_filter([$c->phone, $c->code, $c->address ? \Illuminate\Support\Str::limit($c->address, 30) : null]);
                return [
                    'value' => (string) $c->id,
                    'label' => $name,
                    'sublabel' => implode(' • ', $details),
                ];
            })
            ->toArray();
    }

    public function clearOdp(): void
    {
        $this->odp_id = null;
    }

    #[Renderless]
    public function searchOdps(string $query = ''): array
    {
        $q = trim($query);
        $user = auth()->user();
        $builder = Odp::withoutGlobalScopes();
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $hasBranchOdp = (clone $builder)->where('branch_id', $user->branch_id)->exists();
            if ($hasBranchOdp) {
                $builder->where(function ($b) use ($user) {
                    $b->where('branch_id', $user->branch_id)
                      ->orWhereNull('branch_id');
                });
            }
        }

        return $builder
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->take(50)
            ->get()
            ->map(function ($odp) {
                $avail = $odp->availablePortCount ?? $odp->available_ports ?? 0;
                $total = $odp->port_count ?? 8;
                return [
                    'value' => (string) $odp->id,
                    'label' => $odp->name . ($odp->code ? " ({$odp->code})" : ''),
                    'sublabel' => "Sisa: {$avail}/{$total} port" . ($odp->address ? ' • ' . \Illuminate\Support\Str::limit($odp->address, 30) : ''),
                ];
            })
            ->toArray();
    }

    public function updatedCustomerId($value)
    {
        if ($value) {
            $cust = Customer::withoutGlobalScopes()->find($value);
            if ($cust && !empty($cust->reseller_id)) {
                $this->new_customer_reseller_id = (string) $cust->reseller_id;
            }
        }
    }

    public function getSelectedCustomerProperty()
    {
        if ($this->is_new_customer) {
            $reseller = !empty($this->new_customer_reseller_id)
                ? \App\Models\User::withoutGlobalScopes()->find($this->new_customer_reseller_id)
                : null;
            return (object) [
                'id' => null,
                'name' => $this->new_customer_name ?: '(Nama Pelanggan Baru Belum Diisi)',
                'phone' => $this->new_customer_phone ?: '-',
                'address' => $this->new_customer_address ?: '-',
                'code' => 'BARU',
                'reseller_id' => $this->new_customer_reseller_id ?: null,
                'reseller' => $reseller,
            ];
        }

        if (!$this->customer_id) {
            return null;
        }

        return Customer::withoutGlobalScopes()->with('reseller')->find($this->customer_id);
    }

    public function getSelectedCustomerLabelProperty(): ?string
    {
        if (!$this->customer_id) return null;
        $c = Customer::withoutGlobalScopes()->find($this->customer_id);
        if (!$c) return null;
        $name = !empty(trim($c->name)) ? $c->name : 'Pelanggan #' . $c->id;
        return $c->phone ? "{$name} ({$c->phone})" : $name;
    }

    public function getSelectedCustomerSublabelProperty(): ?string
    {
        if (!$this->customer_id) return null;
        $c = Customer::withoutGlobalScopes()->find($this->customer_id);
        if (!$c) return null;
        $details = array_filter([$c->phone, $c->code, $c->address ? \Illuminate\Support\Str::limit($c->address, 30) : null]);
        return implode(' • ', $details);
    }

    public function getSelectedResellerLabelProperty(): ?string
    {
        if (!$this->new_customer_reseller_id) return null;
        $r = User::withoutGlobalScopes()->find($this->new_customer_reseller_id);
        if (!$r) return null;
        $branchLabel = !empty($r->branch?->name) ? ' (' . $r->branch->name . ')' : '';
        return $r->name . $branchLabel;
    }

    public function getSelectedResellerSublabelProperty(): ?string
    {
        if (!$this->new_customer_reseller_id) return null;
        $r = User::withoutGlobalScopes()->find($this->new_customer_reseller_id);
        if (!$r) return null;
        return $r->email ?? ($r->phone ?? 'Mitra Reseller');
    }

    public function getSelectedServiceProfileProperty()
    {
        if (!$this->service_profile_id) return null;
        return ServiceProfile::withoutGlobalScopes()->find($this->service_profile_id);
    }

    public function getSelectedOdpProperty()
    {
        if (!$this->odp_id) return null;
        return Odp::withoutGlobalScopes()->find($this->odp_id);
    }

    public function getSelectedOltProperty()
    {
        if (!$this->olt_id) return null;
        return Olt::withoutGlobalScopes()->find($this->olt_id);
    }

    public function getSelectedOdpLabelProperty(): ?string
    {
        if (!$this->odp_id) return null;
        $odp = Odp::withoutGlobalScopes()->find($this->odp_id);
        return $odp ? $odp->name : null;
    }

    public function retryProvisioning()
    {
        if (!$this->completed_onu_id) {
            session()->flash('error', 'Tidak ada perangkat ONU yang dipilih untuk retry.');
            return;
        }

        $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->find($this->completed_onu_id);
        if (!$onu) {
            session()->flash('error', 'Data ONU tidak ditemukan.');
            return;
        }

        try {
            $service = app(\App\Services\ISP\GenieAcsProvisioningService::class);
            $result = $service->provisionOnt($onu, [
                'force_retry' => true,
                'wifi_ssid' => $this->wifi_ssid,
                'wifi_password' => $this->wifi_password,
            ]);

            $this->provisioning_status = $result['status'];
            $this->last_provisioned_at = now()->format('d M Y H:i:s');
            $this->provisioning_message = $result['message'] ?? ($result['error'] ?? 'Task berhasil dikirim ulang ke GenieACS.');

            if ($result['status'] === 'success') {
                session()->flash('success', 'Retry Provisioning Berhasil: ONT telah terkonfigurasi!');
            } elseif ($result['status'] === 'waiting_device') {
                session()->flash('warning', 'Retry Provisioning: ONT sedang offline. Task telah diantrikan kembali di GenieACS.');
            } else {
                session()->flash('error', 'Retry Provisioning Gagal: ' . ($result['error'] ?? 'Terjadi kesalahan NBI'));
            }
        } catch (\Throwable $e) {
            $this->provisioning_status = 'failed';
            $this->provisioning_message = $e->getMessage();
            session()->flash('error', 'Gagal retry provisioning: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $baseServiceQuery = ServiceProfile::withoutGlobalScopes()
            ->availableForUser($user)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', '')
                  ->orWhereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['active', 'enabled', '1', 'actived', 'aktif']);
            });

        $servicesQuery = clone $baseServiceQuery;
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $servicesQuery->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id')
                  ->orWhere('branch_id', '')
                  ->orWhere('visibility', 'global');
            });
        }

        $serviceRecords = $servicesQuery->orderBy('name')->get();

        // Fallback jika tidak ada paket spesifik branch: ambil semua paket aktif yang diizinkan untuk user
        if ($serviceRecords->isEmpty()) {
            $serviceRecords = $baseServiceQuery->orderBy('name')->get();
        }

        // Filter tipe & pencarian untuk tampilan UI kartu paket (PPPoE, Hotspot, Voucher)
        $filteredRecords = $serviceRecords;
        if ($this->package_filter_type === 'pppoe') {
            $filteredRecords = $filteredRecords->filter(fn ($s) => in_array(strtolower((string)$s->service_type), ['pppoe', 'combined', 'broadband']) || empty($s->service_type));
        } elseif ($this->package_filter_type === 'hotspot') {
            $filteredRecords = $filteredRecords->filter(fn ($s) => in_array(strtolower((string)$s->service_type), ['hotspot', 'member_hotspot']));
        } elseif ($this->package_filter_type === 'voucher') {
            $filteredRecords = $filteredRecords->filter(fn ($s) => in_array(strtolower((string)$s->service_type), ['voucher']));
        }

        if (!empty(trim($this->package_search))) {
            $term = strtolower(trim($this->package_search));
            $filteredRecords = $filteredRecords->filter(function ($s) use ($term) {
                return str_contains(strtolower((string)$s->name), $term)
                    || str_contains(strtolower((string)$s->code), $term)
                    || str_contains(strtolower((string)$s->download_speed), $term);
            });
        }

        $servicesOptions = $serviceRecords
            ->map(function ($s) {
                $price = $s->base_price ?? $s->price ?? $s->cost_price ?? 0;
                $st = strtoupper($s->service_type ?: 'INTERNET');
                $speed = '';
                if ($s->download_speed) {
                    $speed = " • {$s->download_speed} Mbps";
                }
                $priceLabel = $s->isFreePackage() ? "Paket Gratis (Rp 0)" : "Rp" . number_format($price, 0, ',', '.');
                return [
                    'value' => (string) $s->id,
                    'label' => $s->name,
                    'sublabel' => "[{$st}] {$priceLabel}{$speed}",
                ];
            })
            ->toArray();

        $resellersOptions = \App\Models\User::withoutGlobalScopes()
            ->where(function($q) {
                $q->whereHas('roles', fn($rq) => $rq->whereIn('name', ['reseller', 'Reseller', 'mitra', 'Mitra']))
                  ->orWhereRaw('UPPER(COALESCE(job_function, "")) IN (?, ?)', ['RESELLER', 'SALES']);
            })
            ->with('branch')
            ->orderBy('name')
            ->get()
            ->map(function ($r) {
                $branchLabel = !empty($r->branch?->name) ? ' (' . $r->branch->name . ')' : '';
                return [
                    'value' => (string) $r->id,
                    'label' => $r->name . $branchLabel,
                    'sublabel' => $r->email ?? ($r->phone ?? 'Mitra Reseller'),
                ];
            })
            ->toArray();

        if ($this->new_customer_reseller_id) {
            $exists = collect($resellersOptions)->firstWhere('value', (string) $this->new_customer_reseller_id);
            if (!$exists) {
                $r = User::withoutGlobalScopes()->find($this->new_customer_reseller_id);
                if ($r) {
                    $branchLabel = !empty($r->branch?->name) ? ' (' . $r->branch->name . ')' : '';
                    array_unshift($resellersOptions, [
                        'value' => (string) $r->id,
                        'label' => $r->name . $branchLabel,
                        'sublabel' => $r->email ?? ($r->phone ?? 'Mitra Reseller'),
                    ]);
                }
            }
        }

        $customersQuery = Customer::withoutGlobalScopes()
            ->whereRaw('LOWER(name) NOT LIKE ?', ['guest%'])
            ->whereRaw('LOWER(code) NOT LIKE ?', ['gst-%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', ['tamu%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', ['voucher%']);
        $customerSearchTerm = trim((string) $this->customer_search);
        if ($customerSearchTerm !== '') {
            $like = '%' . $customerSearchTerm . '%';
            $customersQuery->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('address', 'like', $like);
            });
        }

        $customersOptions = $customersQuery
            ->latest('id')
            ->take(150)
            ->get(['id', 'name', 'phone', 'code', 'address'])
            ->map(function ($c) {
                $name = !empty(trim($c->name)) ? $c->name : 'Pelanggan #' . $c->id;
                $details = array_filter([$c->phone, $c->code, $c->address ? \Illuminate\Support\Str::limit($c->address, 30) : null]);
                return [
                    'value' => (string) $c->id,
                    'label' => $name,
                    'sublabel' => implode(' • ', $details),
                ];
            })
            ->toArray();

        if ($this->customer_id) {
            $exists = collect($customersOptions)->firstWhere('value', (string) $this->customer_id);
            if (!$exists) {
                $c = Customer::withoutGlobalScopes()->find($this->customer_id);
                if ($c) {
                    $name = !empty(trim($c->name)) ? $c->name : 'Pelanggan #' . $c->id;
                    $details = array_filter([$c->phone, $c->code, $c->address ? \Illuminate\Support\Str::limit($c->address, 30) : null]);
                    array_unshift($customersOptions, [
                        'value' => (string) $c->id,
                        'label' => $name,
                        'sublabel' => implode(' • ', $details),
                    ]);
                }
            }
        }

        $olts = Olt::withoutGlobalScopes()->get();
        $vendors = \App\Models\ISP\Vendor::withoutGlobalScopes()->get();
        $allOdps = Odp::withoutGlobalScopes()->orderBy('name')->get();

        return view('livewire.isp.technician.installation.wizard', [
            'customersOptions' => $customersOptions,
            'servicesOptions' => $servicesOptions,
            'serviceRecords' => $filteredRecords,
            'allServiceRecords' => $serviceRecords,
            'resellersOptions' => $resellersOptions,
            'allOdps' => $allOdps,
            'olts' => $olts,
            'vendors' => $vendors,
            'currentStep' => $this->currentStep,
            'is_new_customer' => $this->is_new_customer,
            'hasAccess' => $this->hasAccess,
            'requireWorkOrderSelection' => $this->requireWorkOrderSelection,
            'isRestrictedTechnician' => $this->isRestrictedTechnician,
            'activeWorkOrder' => $this->activeWorkOrder,
            'pendingTickets' => $this->pendingTickets,
            'pendingInstallations' => $this->pendingInstallations,
            'pendingRequisitions' => $this->pendingRequisitions,
        ]);
    }

    public static function resolveAcsServerUrl(): string
    {
        $acsSettings = \App\Models\Setting::getValue('connection.acs', []);
        $acsUrl = trim((string) ($acsSettings['acs_url'] ?? ''));

        // Abaikan jika berisi domain dummy example.com
        if (str_contains($acsUrl, 'example.com')) {
            $acsUrl = '';
        }

        if (empty($acsUrl) && !empty($acsSettings['base_url'])) {
            $parsed = parse_url($acsSettings['base_url']);
            $scheme = $parsed['scheme'] ?? 'http';
            $host = $parsed['host'] ?? '';
            $port = $acsSettings['tr069_port'] ?? 7547;
            if ($host && !in_array($host, ['example.com', 'localhost'], true)) {
                $acsUrl = "{$scheme}://{$host}:{$port}";
            }
        }

        if (empty($acsUrl)) {
            $envUrl = env('GENIEACS_PUBLIC_CWMP_URL', config('genieacs.cpe_defaults.acs_url', ''));
            if ($envUrl && !str_contains($envUrl, 'example.com')) {
                $acsUrl = $envUrl;
            }
        }

        // Jika masih kosong, coba ambil domain/host aktif dari request server
        if (empty($acsUrl)) {
            $currentHost = null;
            try {
                $currentHost = request()->getHost();
            } catch (\Throwable $e) {}

            if ($currentHost && !in_array($currentHost, ['localhost', '127.0.0.1', 'example.com'], true)) {
                $acsUrl = "http://{$currentHost}:7547";
            }
        }

        return $acsUrl ?: 'http://10.10.10.1:7547';
    }
}

