<?php

namespace App\Livewire\Isp\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Models\ISP\Vendor;
use App\Models\Customer\CustomerService;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\ISP\OnuService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnconfiguredOnus extends AdminComponent
{
    public Olt $olt;
    public array $unconfiguredOnus = [];
    public bool $isLoading = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    public bool $showProvisionModal = false;
    public array $provisionData = [
        'pon_port' => '',
        'onu_id' => '',
        'serial_number' => '',
        'vendor_oui' => '',
        'customer_service_id' => '',
        'profile' => 'default',
        'name' => '',
    ];

    public function mount($olt = null)
    {
        parent::mount();

        if ($olt instanceof Olt) {
            $this->olt = $olt;
        } elseif (is_numeric($olt)) {
            $this->olt = Olt::findOrFail($olt);
        }
    }

    public function discover()
    {
        $this->isLoading = true;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->unconfiguredOnus = [];

        try {
            $registry = app(OltRegistry::class);
            $driver = $registry->forOlt($this->olt);
            $list = $driver->discoverUnregisteredOnus();
            $this->unconfiguredOnus = array_map(function ($item) {
                $item['serial_number'] = strtoupper(trim($item['serial_number'] ?? ''));
                return $item;
            }, $list);
            if (empty($this->unconfiguredOnus)) {
                $this->successMessage = 'Tidak ditemukan ONU baru yang belum dikonfigurasi.';
            } else {
                $this->successMessage = count($this->unconfiguredOnus) . ' ONU ditemukan!';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal mengambil data dari OLT: ' . $e->getMessage();
            Log::error('Discovery Unconfigured ONU Failed', ['olt_id' => $this->olt->id, 'error' => $e->getMessage()]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function openProvisionModal(string $serial, int $ponPort, string $vendor, string $onuId = '')
    {
        $cleanSerial = strtoupper(trim($serial));
        $this->provisionData = [
            'pon_port' => $ponPort,
            'onu_id' => $onuId,
            'serial_number' => $cleanSerial,
            'vendor_oui' => strtoupper(trim($vendor)),
            'customer_service_id' => '',
            'profile' => 'default',
            'name' => 'ONU-' . substr($cleanSerial, -6),
        ];
        $this->showProvisionModal = true;
    }

    public function closeProvisionModal()
    {
        $this->showProvisionModal = false;
    }

    public function getCustomerServicesProperty()
    {
        return CustomerService::with('customer')
            ->where('status', '!=', 'terminated')
            ->whereNull('onu_id')
            ->get();
    }

    public function submitProvision()
    {
        $this->validate([
            'provisionData.customer_service_id' => 'required|exists:customer_services,id',
            'provisionData.name' => 'required|string|max:50',
            'provisionData.profile' => 'required|string',
            'provisionData.serial_number' => 'required|string',
            'provisionData.pon_port' => 'required|integer|min:0',
        ]);

        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            $ponPort = (int)$this->provisionData['pon_port'];
            $serialNumber = strtoupper(trim($this->provisionData['serial_number']));
            $vendorOui = strtoupper(trim($this->provisionData['vendor_oui']));

            $ponPortModel = PonPort::where('olt_id', $this->olt->id)
                ->where(function ($q) use ($ponPort) {
                    $q->where('port_index', $ponPort)
                        ->orWhere('name', 'like', '%/' . $ponPort);
                })->first();

            $vendor = null;
            if ($vendorOui) {
                $vendor = Vendor::active()
                    ->where(function ($q) use ($vendorOui) {
                        $q->whereRaw('UPPER(name) like ?', ['%' . $vendorOui . '%'])
                            ->orWhereRaw('UPPER(code) like ?', ['%' . $vendorOui . '%']);
                    })->first();
            }

            $user = Auth::user();
            $service = app(OnuService::class);

            DB::beginTransaction();
            try {
                $onu = $service->create([
                    'olt_id' => $this->olt->id,
                    'pon_port_id' => $ponPortModel ? $ponPortModel->id : null,
                    'vendor_id' => $vendor?->id,
                    'pon_port' => $ponPort,
                    'onu_id_on_olt' => $this->provisionData['onu_id'] ?: null,
                    'name' => trim($this->provisionData['name']),
                    'serial_number' => $serialNumber,
                    'profile_name' => $this->provisionData['profile'],
                    'status' => 'active',
                    'provision_status' => 'provisioning',
                    'description' => 'Auto-provisioned via Zero-Touch Provisioning (OLT: ' . $this->olt->code . ' / PON:' . $ponPort . ')',
                ], $user);

                $customerService = CustomerService::findOrFail($this->provisionData['customer_service_id']);
                $customerService->update([
                    'onu_id' => $onu->id,
                    'updated_by' => $user->id,
                ]);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            $registry = app(OltRegistry::class);
            $driver = $registry->forOlt($this->olt);

            $success = $driver->provisionOnu($onu, $serialNumber, $ponPort, $this->provisionData['profile']);

            if ($success) {
                $onu->update([
                    'provision_status' => 'provisioned',
                    'provisioned_at' => now(),
                    'status' => 'active',
                    'updated_by' => $user->id,
                ]);
                $this->successMessage = 'ONU berhasil diprovision ke OLT dan terhubung ke Layanan Pelanggan.';
                $this->closeProvisionModal();
                $this->discover();
            } else {
                $onu->update([
                    'provision_status' => 'failed',
                    'updated_by' => $user->id,
                ]);
                $this->errorMessage = 'Gagal mengeksekusi command provision ke OLT. Periksa log SSH/Telnet dan credential OLT.';
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            Log::error('Auto-Provision ONU Failed', [
                'olt_id' => $this->olt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.isp.olt.unconfigured-onus');
    }
}
