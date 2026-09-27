<?php

namespace App\Livewire\Isp\Technician\Audit;

use Livewire\Component;
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
        "customer_id" => "required|exists:members,id",
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

        $customer = Customer::find($this->customer_id);

        // Update Koordinat Pelanggan jika ada
        if ($this->latitude && $this->longitude) {
            $customer->update([
                "latitude" => $this->latitude,
                "longitude" => $this->longitude,
            ]);
        }

        // 1. Buat/Update ONU (Tandai online pasif, tanpa pipeline OMCI)
        $onu = Onu::updateOrCreate(
            ["serial_number" => $this->onu_sn],
            [
                "olt_id" => $this->olt_id,
                "pon_port" => $this->pon_port,
                "name" => "ONU " . $customer->name,
                "status" => "online",
            ]
        );

        // 2. Buat CustomerService (Soft Link, tanpa dikirim ke Orchestrator)
        $profile = ServiceProfile::find($this->service_profile_id);
        
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
                "created_by" => auth()->id(),
            ]);
        }

        session()->flash("success", "Data Sensus ONU & Koordinat berhasil disimpan tanpa mereset modem!");
        return redirect()->route("technician.dashboard");
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->searchCustomer, function($q) {
                $q->where("name", "like", "%" . $this->searchCustomer . "%")
                  ->orWhere("phone", "like", "%" . $this->searchCustomer . "%");
            })
            ->take(15)
            ->get();

        return view("livewire.isp.technician.audit.form", [
            "customers" => $customers,
            "olts" => Olt::all(),
            "services" => ServiceProfile::all(),
        ])->layout("layouts.technician-app");
    }
}

