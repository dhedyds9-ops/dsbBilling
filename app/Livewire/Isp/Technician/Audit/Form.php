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
    public string $transaction_id = '';

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
        $this->transaction_id = 'SENSUS-' . (string) \Illuminate\Support\Str::uuid();

        // Default OLT jika hanya ada 1 (hanya OLT aktif dan belum dihapus)
        $firstOlt = Olt::withoutGlobalScope('branch_isolation')
            ->where('status', '!=', 'inactive')
            ->first();
        if ($firstOlt) {
            $this->olt_id = (string) $firstOlt->id;
        }
    }

    public function submit()
    {
        $this->validate();

        $user = auth()->user();

        try {
            app(\App\Services\ISP\OntLifecycleService::class)->recordSensus([
                'transaction_id' => $this->transaction_id ?: ('SENSUS-' . (string) \Illuminate\Support\Str::uuid()),
                'is_titipan' => (bool) $this->is_titipan,
                'titipan_name' => $this->titipan_name,
                'titipan_notes' => $this->titipan_notes,
                'customer_id' => $this->customer_id,
                'olt_id' => $this->olt_id,
                'pon_port' => $this->pon_port,
                'onu_sn' => $this->onu_sn,
                'odp_id' => $this->odp_id,
                'odp_port' => $this->odp_port,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ], $user);

            session()->flash("success", "Data Sensus ONU & Audit ODP berhasil disimpan ke sistem!");
            return redirect()->route("technician.dashboard");
        } catch (\InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'Pelanggan tidak ditemukan')) {
                $this->addError('customer_id', $e->getMessage());
            } else {
                $this->addError('onu_sn', $e->getMessage());
            }
            return;
        }
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
        $oltsOptions = Olt::withoutGlobalScope('branch_isolation')
            ->where('status', '!=', 'inactive')
            ->orderBy('name')
            ->get()
            ->map(function ($o) {
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


