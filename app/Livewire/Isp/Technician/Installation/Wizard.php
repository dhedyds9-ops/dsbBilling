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

    // Step 7 Result Properties (P1-1A)
    public $provisioning_status = 'pending';
    public $provisioning_message = '';
    public $completed_pipeline_id = null;
    public $completed_onu_id = null;
    public $last_provisioned_at = null;

    public $network_profile_id;

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Technician Dashboard', 'url' => route('technician.dashboard')],
            ['label' => 'Pemasangan Baru', 'url' => '#'],
        ];

        $this->network_profile_id = NetworkProfile::where('type', 'pppoe')->first()?->id;
    }

    public function updatedServiceProfileId($value)
    {
        if ($value) {
            $profile = ServiceProfile::find($value);
            $this->service_type = $profile ? $profile->service_type : 'pppoe';
            $this->network_profile_id = NetworkProfile::where('type', $this->service_type)->first()?->id;
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
            if ($this->is_new_customer) {
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

                $data = [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'service_profile_id' => $this->service_profile_id,
                    'network_profile_id' => $this->network_profile_id,
                    'router_id' => $this->network_profile_id,
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
        return Customer::withoutGuests()
            ->when(!empty($q), function ($builder) use ($q) {
                $builder->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->take(20)
            ->get(['id', 'name', 'phone', 'code'])
            ->map(function ($c) {
                return [
                    'value' => (string) $c->id,
                    'label' => $c->name,
                    'sublabel' => $c->phone . ($c->code ? " ({$c->code})" : ''),
                ];
            })
            ->toArray();
    }

    #[Renderless]
    public function searchOdps(string $query = ''): array
    {
        $q = trim($query);
        return Odp::query()
            ->when(!empty($q), function ($builder) use ($q) {
                $builder->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->take(20)
            ->get()
            ->map(function ($odp) {
                return [
                    'value' => (string) $odp->id,
                    'label' => $odp->name,
                    'sublabel' => "Sisa: {$odp->available_ports} port",
                ];
            })
            ->toArray();
    }

    public function getSelectedCustomerLabelProperty(): ?string
    {
        if (!$this->customer_id) return null;
        $c = Customer::withoutGuests()->find($this->customer_id);
        return $c ? "{$c->name} ({$c->phone})" : null;
    }

    public function getSelectedOdpLabelProperty(): ?string
    {
        if (!$this->odp_id) return null;
        $odp = Odp::find($this->odp_id);
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
        $servicesOptions = ServiceProfile::whereIn('service_type', ['pppoe', 'hotspot'])
            ->get()
            ->map(function ($s) {
                return [
                    'value' => (string) $s->id,
                    'label' => $s->name,
                    'sublabel' => 'Rp' . number_format($s->base_price, 0, ',', '.'),
                ];
            })
            ->toArray();

        $resellersOptions = \App\Models\User::whereHas('roles', function($q) {
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

        $vendors = \App\Models\ISP\Vendor::all();

        return view('livewire.isp.technician.installation.wizard', [
            'servicesOptions' => $servicesOptions,
            'resellersOptions' => $resellersOptions,
            'olts' => Olt::all(),
            'vendors' => $vendors,
        ]);
    }
}

