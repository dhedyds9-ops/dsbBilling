<?php

namespace App\Livewire\Isp\Technician\Audit;

use Livewire\Component;
use Livewire\Attributes\Renderless;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use App\Models\ISP\Onu;

class Form extends Component
{
    public $is_titipan = false;
    public $titipan_name = "";
    public $titipan_notes = "";

    public $searchCustomer = "";
    public $customer_id;
    public $olt_id;
    public $pon_port = 1;
    public $onu_sn;
    public $odp_id;
    public $odp_port;
    public $latitude;
    public $longitude;

    protected function rules()
    {
        $rules = [
            "olt_id" => "required|exists:olts,id",
            "pon_port" => "required|integer|min:1",
            "onu_sn" => "required|string|max:100",
            "odp_id" => "required|exists:odps,id",
            "odp_port" => "nullable|integer|min:1|max:128",
        ];

        if ($this->is_titipan) {
            $rules["titipan_name"] = "required|string|min:3|max:100";
            $rules["customer_id"] = "nullable";
        } else {
            $rules["customer_id"] = "required|exists:customers,id";
        }

        return $rules;
    }

    public function mount()
    {
        // Default OLT jika hanya ada 1
        $firstOlt = Olt::withoutGlobalScopes()->first();
        if ($firstOlt) {
            $this->olt_id = (string) $firstOlt->id;
        }
    }

    public function submit()
    {
        $this->validate();

        $user = auth()->user();
        $customer = null;
        $branchId = null;

        if (!$this->is_titipan) {
            $customer = Customer::find($this->customer_id);
            if (!$customer) {
                $this->addError('customer_id', 'Pelanggan tidak ditemukan.');
                return;
            }
            $branchId = $user->branch_id ?? $customer->branch_id ?? $customer->reseller?->branch_id;
        } else {
            $branchId = $user->branch_id ?? Odp::withoutGlobalScopes()->where('id', $this->odp_id)->value('branch_id');
        }

        if (!$branchId) {
            $branchId = 1; // Fallback ke default branch
        }

        // Validasi: Cegah penggunaan ONU yang sedang aktif digunakan oleh pelanggan lain
        $existingOnu = Onu::withoutGlobalScopes()->where('serial_number', $this->onu_sn)->first();
        if ($existingOnu && !$this->is_titipan && $customer) {
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
            $onuName = $this->is_titipan
                ? "Titipan: " . trim($this->titipan_name)
                : "ONU " . ($customer ? $customer->name : 'Pelanggan');

            $onuDesc = $this->is_titipan
                ? ($this->titipan_notes ?: "Perangkat titipan infrastruktur/lokal tanpa paket PPPoE/Hotspot")
                : "Hasil Sensus Lapangan & Audit Jalur";

            // Update Koordinat Pelanggan jika bukan titipan
            if ($customer) {
                $updateCust = ['branch_id' => $branchId];
                if ($this->latitude && $this->longitude) {
                    $updateCust['latitude'] = $this->latitude;
                    $updateCust['longitude'] = $this->longitude;
                }
                $customer->update($updateCust);
            }

            // 1. Buat/Update ONU (Tandai online pasif, terikat ODP & OLT)
            $onuData = [
                "olt_id" => $this->olt_id,
                "pon_port" => $this->pon_port,
                "odp_id" => $this->odp_id,
                "branch_id" => $branchId,
                "name" => $onuName,
                "description" => $onuDesc,
                "status" => "online",
                "attributes" => [
                    "odp_port" => $this->odp_port,
                    "is_titipan" => $this->is_titipan,
                    "titipan_name" => $this->is_titipan ? $this->titipan_name : null,
                    "audited_by" => $user?->name,
                    "audited_at" => now()->toDateTimeString(),
                ],
            ];

            if ($this->latitude && $this->longitude) {
                $onuData['latitude'] = $this->latitude;
                $onuData['longitude'] = $this->longitude;
            }

            $onu = Onu::withoutGlobalScopes()->updateOrCreate(
                ["serial_number" => $this->onu_sn],
                $onuData
            );

            // Perbarui okupansi port ODP jika belum dihitung
            if ($this->odp_id) {
                $odp = Odp::withoutGlobalScopes()->find($this->odp_id);
                if ($odp) {
                    $count = Onu::withoutGlobalScopes()->where('odp_id', $odp->id)->count();
                    $odp->update(['used_port_count' => $count]);
                }
            }

            // 2. Hubungkan CustomerService jika pelanggan resmi
            if ($customer && !$this->is_titipan) {
                $cs = CustomerService::where("customer_id", $customer->id)->where("onu_id", $onu->id)->first();
                if (!$cs) {
                    CustomerService::create([
                        "uuid" => (string) \Illuminate\Support\Str::uuid(),
                        "customer_id" => $customer->id,
                        "service_id" => 1,
                        "onu_id" => $onu->id,
                        "status" => "active",
                        "service_status" => "active",
                        "created_by" => $user->id,
                    ]);
                }
            }
        });

        session()->flash("success", "Data Sensus ONU & Audit ODP berhasil disimpan ke sistem!");
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
                    'sublabel' => ($c->phone ?: '-') . ($c->code ? " ({$c->code})" : ''),
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
        $odpsQuery = Odp::withoutGlobalScopes()->where('status', 'active');
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $odpsQuery->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        $odpsOptions = $odpsQuery
            ->orderBy('name')
            ->get()
            ->map(function ($o) {
                $used = (int) ($o->used_port_count ?? 0);
                $total = (int) ($o->port_count ?? 8);
                return [
                    'value' => (string) $o->id,
                    'label' => "{$o->name} (" . ($o->code ?: 'ODP') . ")",
                    'sublabel' => "Port: {$used}/{$total} | " . ($o->address ?: 'Titik Distribusi'),
                ];
            })->toArray();

        return view("livewire.isp.technician.audit.form", [
            "oltsOptions" => $oltsOptions,
            "odpsOptions" => $odpsOptions,
        ])->layout("layouts.technician-app");
    }
}


