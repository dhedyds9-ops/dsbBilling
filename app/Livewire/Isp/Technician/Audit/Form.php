<?php

namespace App\Livewire\Isp\Technician\Audit;

use Livewire\Component;
use Livewire\Attributes\Renderless;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\ServiceProfile;

class Form extends Component
{
    public $searchCustomer = "";
    public $customer_id;
    public $olt_id;
    public $pon_port;
    public $onu_sn;
    public $service_profile_id;
    public $latitude;
    public $longitude;

    protected $rules = [
        "customer_id" => "required|exists:customers,id",
        "olt_id" => "required|exists:olts,id",
        "pon_port" => "required|integer|min:1",
        "onu_sn" => "required|string|max:100",
        "service_profile_id" => "required|exists:service_profiles,id",
    ];

    public function mount()
    {
        // Initialization if needed
    }

    public function submit()
    {
        $this->validate();

        $user = auth()->user();
        $customer = Customer::find($this->customer_id);

        if (!$customer) {
            $this->addError('customer_id', 'Pelanggan tidak ditemukan.');
            return;
        }

        // Tentukan branch_id dari konteks organisasi yang sah
        $branchId = $user->branch_id ?? $customer->branch_id ?? $customer->reseller?->branch_id;
        if (!$branchId) {
            $this->addError('customer_id', 'Cabang kantor (Branch) Anda atau Pelanggan belum ditentukan. Hubungi Administrator untuk menetapkan cabang.');
            return;
        }

        // Validasi: Cegah penggunaan ONU yang sedang aktif digunakan oleh pelanggan lain (Cross-Branch Aware)
        $existingOnu = Onu::withoutGlobalScopes()->where('serial_number', $this->onu_sn)->first();
        if ($existingOnu) {
            $activeOtherCs = CustomerService::withoutGlobalScopes()->where('onu_id', $existingOnu->id)
                ->where('status', 'active')
                ->where('customer_id', '!=', $customer->id)
                ->with('customer')
                ->first();
            if ($activeOtherCs) {
                $otherName = $activeOtherCs->customer?->name ?? 'Pelanggan #' . $activeOtherCs->customer_id;
                $this->addError('onu_sn', "ONU SN {$this->onu_sn} sedang aktif digunakan oleh pelanggan lain ({$otherName}). Tidak dapat dialihkan secara sepihak.");
                return;
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($customer, $branchId, $user) {
            // Update Koordinat Pelanggan & pastikan branch_id terikat
            $updateCust = ['branch_id' => $branchId];
            if ($this->latitude && $this->longitude) {
                $updateCust['latitude'] = $this->latitude;
                $updateCust['longitude'] = $this->longitude;
            }
            $customer->update($updateCust);

            // 1. Buat/Update ONU (Tandai online pasif, terikat branch_id)
            $onu = Onu::withoutGlobalScopes()->updateOrCreate(
                ["serial_number" => $this->onu_sn],
                [
                    "olt_id" => $this->olt_id,
                    "pon_port" => $this->pon_port,
                    "branch_id" => $branchId,
                    "name" => "ONU " . $customer->name,
                    "status" => "online",
                ]
            );

            // 2. Buat CustomerService (Soft Link, tanpa dikirim ke Orchestrator)
            $profile = ServiceProfile::withoutGlobalScopes()->find($this->service_profile_id);
            
            $cs = CustomerService::where("customer_id", $customer->id)->where("onu_id", $onu->id)->first();
            if (!$cs) {
                CustomerService::create([
                    "uuid" => (string) \Illuminate\Support\Str::uuid(),
                    "customer_id" => $customer->id,
                    "service_id" => $profile->service_id ?? 1,
                    "service_profile_id" => $profile->id,
                    "onu_id" => $onu->id,
                    "status" => "active",
                    "service_status" => "active",
                    "created_by" => $user->id,
                ]);
            }
        });

        session()->flash("success", "Data Sensus ONU & Koordinat berhasil disimpan tanpa mereset modem!");
        return redirect()->route("technician.dashboard");
    }

    #[Renderless]
    public function searchCustomers(string $query = ''): array
    {
        $q = trim($query);
        $user = auth()->user();
        $builder = Customer::withoutGlobalScopes()->withoutGuests();
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
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->take(20)
            ->get()
            ->map(function ($c) {
                return [
                    'value' => (string) $c->id,
                    'label' => $c->name,
                    'sublabel' => $c->phone . ($c->code ? " ({$c->code})" : ''),
                ];
            })
            ->toArray();
    }

    public function getSelectedCustomerLabelProperty(): ?string
    {
        if (!$this->customer_id) return null;
        $c = Customer::withoutGlobalScopes()->withoutGuests()->find($this->customer_id);
        return $c ? "{$c->name} ({$c->phone})" : null;
    }

    public function render()
    {
        $oltsOptions = Olt::withoutGlobalScopes()->get()->map(function ($o) {
            return [
                'value' => (string) $o->id,
                'label' => $o->name,
                'sublabel' => $o->ip_address ?? '',
            ];
        })->toArray();

        $user = auth()->user();
        $servicesQuery = ServiceProfile::withoutGlobalScopes();
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $servicesQuery->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }
        $servicesQuery->where(function ($q) {
            $q->whereNull('status')->orWhere('status', 'active');
        });

        $servicesOptions = $servicesQuery
            ->orderBy('name')
            ->get()
            ->map(function ($s) {
                $price = $s->base_price ?? $s->price ?? $s->cost_price ?? 0;
                $st = strtoupper($s->service_type ?: 'INTERNET');
                return [
                    'value' => (string) $s->id,
                    'label' => $s->name,
                    'sublabel' => "[{$st}] Rp" . number_format($price, 0, ',', '.'),
                ];
            })->toArray();

        return view("livewire.isp.technician.audit.form", [
            "oltsOptions" => $oltsOptions,
            "servicesOptions" => $servicesOptions,
        ])->layout("layouts.technician-app");
    }
}


