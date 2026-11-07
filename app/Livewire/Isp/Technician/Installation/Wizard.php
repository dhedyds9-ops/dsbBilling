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
    public $service_profile_id;
    public $service_type = 'pppoe'; // Default to PPPoE
    public $odp_id;
    public $olt_id;
    public $pon_port;
    public $onu_sn;
    public $onu_vendor_id;
    public $onu_model;

    public $custom_username = '';
    public $custom_password = '';

    public $wifi_ssid = '';
    public $wifi_password = '';

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

        $user = auth()->user();
        $isAdmin = $user && ($user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin', 'admin', 'manager', 'dispatcher']));
        $this->isRestrictedTechnician = !$isAdmin;

        // Auto pre-populate from query parameters (PSB, Work Order, CRM Installation, or Ticket)
        $customerId = request()->query('customer_id');
        $ticketId = request()->query('ticket');
        $installationId = request()->query('installation_id');
        $requisitionId = request()->query('requisition_id');

        if ($this->isRestrictedTechnician && $user) {
            // Cek seluruh tugas PSB aktif yang ditugaskan kepada teknisi ini
            $tickets = \App\Models\Support\Ticket::withoutGlobalScopes()
                ->where('assigned_to', $user->id)
                ->whereIn('category', ['psb', 'installation'])
                ->whereIn('status', ['open', 'in_progress', 'assigned'])
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()])
                ->latest()
                ->get();

            $installations = \App\Models\CRM\Installation::withoutGlobalScopes()
                ->where(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('technician_id', $user->id);
                })
                ->whereIn('status', ['pending', 'scheduled', 'in_progress', 'assigned'])
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()])
                ->latest()
                ->get();

            $requisitions = \App\Models\Inventory\InventoryRequisition::withoutGlobalScopes()
                ->where('designated_technician_id', $user->id)
                ->where('work_order_type', 'psb')
                ->whereIn('status', ['approved', 'dispatched'])
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()])
                ->latest()
                ->get();

            $totalWorkOrders = $tickets->count() + $installations->count() + $requisitions->count();

            // 1. Jika teknisi TIDAK MEMILIKI tugas PSB apapun dan tidak ada parameter tiket sah:
            if ($totalWorkOrders === 0 && empty($ticketId) && empty($installationId) && empty($requisitionId)) {
                $this->hasAccess = false;
                $this->requireWorkOrderSelection = false;
                return;
            }

            // 2. Jika ada parameter tiket / SPK di URL:
            if ($ticketId) {
                $validTicket = $tickets->firstWhere('id', (int) $ticketId)
                    ?? \App\Models\Support\Ticket::withoutGlobalScopes()->where('id', $ticketId)->where('assigned_to', $user->id)->first();
                if (!$validTicket) {
                    $this->hasAccess = false;
                    session()->flash('error', 'Tiket Tugas PSB ini tidak ditugaskan kepada Anda atau statusnya sudah selesai.');
                    return;
                }
                $this->selectWorkOrder('ticket', $validTicket->id);
            } elseif ($installationId) {
                $validInst = $installations->firstWhere('id', (int) $installationId)
                    ?? \App\Models\CRM\Installation::withoutGlobalScopes()->where('id', $installationId)->where(fn($q) => $q->where('assigned_to', $user->id)->orWhere('technician_id', $user->id))->first();
                if (!$validInst) {
                    $this->hasAccess = false;
                    session()->flash('error', 'SPK Pemasangan ini tidak ditugaskan kepada Anda.');
                    return;
                }
                $this->selectWorkOrder('installation', $validInst->id);
            } elseif ($requisitionId) {
                $validReq = $requisitions->firstWhere('id', (int) $requisitionId);
                if (!$validReq) {
                    $this->hasAccess = false;
                    session()->flash('error', 'Permintaan Barang / SPK ini tidak ditugaskan kepada Anda.');
                    return;
                }
                $this->selectWorkOrder('requisition', $validReq->id);
            } else {
                // Teknisi membuka form instalasi langsung tanpa parameter URL:
                // Kunci form dan wajibkan memilih salah satu tugas yang sudah ditugaskan padanya!
                $this->requireWorkOrderSelection = true;
                $this->pendingTickets = $tickets;
                $this->pendingInstallations = $installations;
                $this->pendingRequisitions = $requisitions;
            }
        } else {
            // Admin flow:
            if ($ticketId) {
                $this->selectWorkOrder('ticket', (int) $ticketId);
            } elseif ($installationId) {
                $this->selectWorkOrder('installation', (int) $installationId);
            } elseif ($requisitionId) {
                $this->selectWorkOrder('requisition', (int) $requisitionId);
            } elseif ($customerId) {
                $cust = Customer::withoutGlobalScopes()->find($customerId);
                if ($cust) {
                    $this->customer_id = (string) $cust->id;
                    $this->is_new_customer = false;
                }
            }
        }
    }

    public function selectWorkOrder(string $type, $id)
    {
        if ($type === 'ticket') {
            $ticket = \App\Models\Support\Ticket::withoutGlobalScopes()->with(['customer'])->find($id);
            if ($ticket) {
                $this->ticket_id = $ticket->id;
                $this->customer_id = (string) $ticket->customer_id;
                $this->is_new_customer = false;
                $this->activeWorkOrder = [
                    'type' => 'ticket',
                    'id' => $ticket->id,
                    'number' => $ticket->uuid ?? ('TIKET-PSB-' . $ticket->id),
                    'customer_name' => $ticket->customer?->name ?? 'Pelanggan #' . $ticket->customer_id,
                    'customer_phone' => $ticket->customer?->phone ?? '-',
                    'address' => $ticket->customer?->address ?? $ticket->description,
                    'notes' => $ticket->description,
                ];
                $this->requireWorkOrderSelection = false;
                $this->hasAccess = true;
            }
        } elseif ($type === 'installation') {
            $inst = \App\Models\CRM\Installation::withoutGlobalScopes()->with(['customer'])->find($id);
            if ($inst) {
                $this->installation_id = $inst->id;
                if ($inst->customer_id) {
                    $this->customer_id = (string) $inst->customer_id;
                    $this->is_new_customer = false;
                } elseif (!empty($inst->customer_name)) {
                    $this->is_new_customer = true;
                    $this->new_customer_name = $inst->customer_name;
                    $this->new_customer_phone = $inst->customer_phone ?? '';
                    $this->new_customer_address = $inst->installation_address ?? '';
                }
                $this->activeWorkOrder = [
                    'type' => 'installation',
                    'id' => $inst->id,
                    'number' => $inst->uuid ?? ('SPK-INST-' . $inst->id),
                    'customer_name' => $inst->customer?->name ?? $inst->customer_name,
                    'customer_phone' => $inst->customer?->phone ?? $inst->customer_phone,
                    'address' => $inst->installation_address ?? $inst->customer?->address,
                    'notes' => $inst->notes,
                ];
                $this->requireWorkOrderSelection = false;
                $this->hasAccess = true;
            }
        } elseif ($type === 'requisition') {
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

    public function nextStep()
    {
        $this->validateStep();
        if ($this->currentStep < 6) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
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
        } elseif ($this->currentStep === 3) {
            $this->validate(['odp_id' => 'required|exists:odps,id']);
            $odp = Odp::find($this->odp_id);
            if ($odp && $odp->availablePortCount <= 0) {
                $this->addError('odp_id', "ODP {$odp->name} sudah penuh (kapasitas 0 port tersedia). Silakan pilih ODP lain.");
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
            $this->validate(['customer_id' => 'required|exists:customers,id']);
        }

        $this->validate([
            'service_profile_id' => 'required|exists:service_profiles,id',
            'odp_id' => 'required|exists:odps,id',
            'olt_id' => 'required|exists:olts,id',
            'pon_port' => 'required|numeric|min:1',
            'onu_sn' => 'required|string|min:4',
            'onu_vendor_id' => 'required|exists:vendors,id',
            'onu_model' => 'required|string|max:255',
        ]);

        // 2. Validasi & Penegakan Branch Isolation (P0-4)
        $user = auth()->user();
        $branchId = $user->branch_id;
        if (!$branchId && $this->is_new_customer && $this->new_customer_reseller_id) {
            $reseller = \App\Models\User::find($this->new_customer_reseller_id);
            $branchId = $reseller?->branch_id;
        } elseif (!$branchId && $this->customer_id) {
            $existingCust = Customer::find($this->customer_id);
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
                // A. Concurrency Lock & Reservasi Kapasitas Port ODP
                $odp = Odp::where('id', $this->odp_id)->lockForUpdate()->first();
                if (!$odp || $odp->availablePortCount <= 0) {
                    throw new \DomainException("ODP " . ($odp?->name ?? $this->odp_id) . " sudah penuh atau tidak memiliki sisa port.");
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

                // C. Klaim port ODP secara atomik di dalam transaksi
                $odp->increment('used_port_count');

                // D. Simpan atau Perbarui Data Pelanggan
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
                    $customer = Customer::findOrFail($this->customer_id);
                    if (empty($customer->branch_id)) {
                        $customer->update(['branch_id' => $branchId]);
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

                $data = [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'service_profile_id' => $this->service_profile_id,
                    'network_profile_id' => $npId,
                    'router_id' => $npId,
                    'username' => $username,
                    'password' => $password,
                    'status' => 'active',
                    'reseller_id' => $customer->reseller_id,
                    'branch_id' => $branchId,
                    'odp_id' => $this->odp_id,
                    'onu_id' => $onu->id,
                    'mac_address' => $sn,
                ];

                if ($this->service_type === 'pppoe') {
                    $result = $provisioningService->activatePPPoEService($data, $user->id);
                    $cs = $result['customer_service'];
                    $serviceInstance = $provisioningService->startProvisioning($cs, $user->id);

                    return [
                        'type' => 'pppoe',
                        'pipeline_id' => $serviceInstance->provisionPipeline->id,
                        'onu' => $onu,
                    ];
                } else {
                    $provisioningService->activateHotspotService($data, $user->id);
                    return [
                        'type' => 'hotspot',
                        'onu' => $onu,
                    ];
                }
            });

            // 6. Eksekusi Non-Blocking Post-Commit Actions (di luar database transaction)
            $onu = $executionResult['onu'];
            $this->completed_onu_id = $onu->id;
            $this->last_provisioned_at = now()->format('d M Y H:i:s');

            if ($executionResult['type'] === 'pppoe') {
                $this->completed_pipeline_id = $executionResult['pipeline_id'];
                $this->provisioning_status = 'queued';
                $this->provisioning_message = 'Instalasi PPPoE berhasil disimpan. Task provisioning ONT telah dikirim ke GenieACS NBI.';
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
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('[Wizard] Gagal push konfigurasi WiFi/Bridge ke ONU: ' . $e->getMessage());
                }

                $this->provisioning_status = 'queued';
                $this->provisioning_message = 'Layanan Hotspot Member & konfigurasi Bridge berhasil dieksekusi.';
            }

            // Catat pemakaian material kabel dropcore & fast connector jika diisi
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
                    \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal catat pemakaian material: ' . $e->getMessage());
                }
            }

            // 7. Update status tiket / surat perintah kerja terkait menjadi selesai
            if ($this->ticket_id) {
                try {
                    \App\Models\Support\Ticket::withoutGlobalScopes()
                        ->where('id', $this->ticket_id)
                        ->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                            'resolution_notes' => 'PSB berhasil diaktivasi oleh teknisi ' . $user->name . ' pada ' . now()->format('d/m/Y H:i') . ' (ONU SN: ' . $sn . ')',
                        ]);
                } catch (\Throwable $te) {
                    \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal update status tiket: ' . $te->getMessage());
                }
            }

            if ($this->installation_id) {
                try {
                    \App\Models\CRM\Installation::withoutGlobalScopes()
                        ->where('id', $this->installation_id)
                        ->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'onu_serial_number' => $sn,
                            'onu_id' => $onu->id,
                        ]);
                } catch (\Throwable $ie) {
                    \Illuminate\Support\Facades\Log::warning('[Wizard] Gagal update status installation: ' . $ie->getMessage());
                }
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
        $user = auth()->user();

        $builder = Customer::withoutGlobalScopes()
            ->where(function ($sq) {
                $sq->whereNull('code')
                  ->orWhere('code', 'not like', 'GST-%')
                  ->orWhereHas('customerServices');
            });

        // Jika user memiliki branch_id, izinkan pelanggan di cabang tersebut atau pelanggan unassigned
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $builder->where(function ($bq) use ($user) {
                $bq->where('branch_id', $user->branch_id)
                   ->orWhereNull('branch_id');
            });
        }

        return $builder
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->take(30)
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

    #[Renderless]
    public function searchOdps(string $query = ''): array
    {
        $q = trim($query);
        $user = auth()->user();
        $builder = Odp::withoutGlobalScopes();
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $builder->where(function ($b) use ($user) {
                $b->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
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
            ->take(30)
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

    public function getSelectedCustomerProperty()
    {
        if ($this->is_new_customer) {
            return (object) [
                'id' => null,
                'name' => $this->new_customer_name ?: '(Nama Pelanggan Baru Belum Diisi)',
                'phone' => $this->new_customer_phone ?: '-',
                'address' => $this->new_customer_address ?: '-',
                'code' => 'BARU',
            ];
        }

        if (!$this->customer_id) {
            return null;
        }

        return Customer::withoutGlobalScopes()->find($this->customer_id);
    }

    public function getSelectedCustomerLabelProperty(): ?string
    {
        if (!$this->customer_id) return null;
        $c = Customer::withoutGlobalScopes()->find($this->customer_id);
        if (!$c) return null;
        $name = !empty(trim($c->name)) ? $c->name : 'Pelanggan #' . $c->id;
        return "{$name} ({$c->phone})";
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
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['active', 'enabled', '1', 'actived']);
            });

        $servicesQuery = clone $baseServiceQuery;
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $servicesQuery->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id')
                  ->orWhere('visibility', 'global');
            });
        }

        $serviceRecords = $servicesQuery->orderBy('name')->get();

        // Fallback jika tidak ada paket spesifik branch: ambil semua paket aktif sistem
        if ($serviceRecords->isEmpty()) {
            $serviceRecords = $baseServiceQuery->orderBy('name')->get();
        }

        // Fallback darurat: jika status bukan active, ambil semua profile agar teknisi tidak terblokir
        if ($serviceRecords->isEmpty()) {
            $serviceRecords = ServiceProfile::withoutGlobalScopes()->orderBy('name')->get();
        }

        $servicesOptions = $serviceRecords
            ->map(function ($s) {
                $price = $s->base_price ?? $s->price ?? $s->cost_price ?? 0;
                $st = strtoupper($s->service_type ?: 'INTERNET');
                $speed = '';
                if ($s->download_speed) {
                    $speed = " • {$s->download_speed} Mbps";
                }
                return [
                    'value' => (string) $s->id,
                    'label' => $s->name,
                    'sublabel' => "[{$st}] Rp" . number_format($price, 0, ',', '.') . $speed,
                ];
            })
            ->toArray();

        $resellersOptions = \App\Models\User::withoutGlobalScopes()->whereHas('roles', function($q) {
            $q->where('name', 'reseller');
        })
        ->get()
        ->map(function ($r) {
            return [
                'value' => (string) $r->id,
                'label' => $r->name,
                'sublabel' => $r->email ?? '',
            ];
        })
        ->toArray();

        $olts = Olt::withoutGlobalScopes()->get();
        $vendors = \App\Models\ISP\Vendor::withoutGlobalScopes()->get();

        return view('livewire.isp.technician.installation.wizard', [
            'servicesOptions' => $servicesOptions,
            'resellersOptions' => $resellersOptions,
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
}

