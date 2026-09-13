<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class ChangePlan extends Component
{
    public $selectedPlanId;
    public $reason;
    public $message;
    public $messageType;
    public $currentServices;
    public $availablePlans;

    protected $rules = [
        'selectedPlanId' => 'required|exists:service_profiles,id',
        'reason'         => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $user     = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            $this->currentServices = collect();
            $this->availablePlans  = collect();
            return;
        }

        $this->currentServices = CustomerService::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->with(['serviceProfile', 'pppoeUser', 'hotspotUser'])
            ->get();

        // Get service types this customer has
        $serviceTypes = [];
        foreach ($this->currentServices as $cs) {
            if ($cs->pppoeUser) $serviceTypes[] = 'pppoe';
            if ($cs->hotspotUser) $serviceTypes[] = 'hotspot';
        }
        if (empty($serviceTypes)) {
            $serviceTypes = ['pppoe', 'hotspot'];
        }
        $serviceTypes = array_unique($serviceTypes);

        $this->availablePlans = ServiceProfile::active()
            ->where(function ($q) use ($serviceTypes) {
                foreach ($serviceTypes as $type) {
                    $q->orWhere('service_type', $type);
                }
                $q->orWhere('service_type', 'combined');
            })
            ->orderBy('base_price')
            ->get();
    }

    public function submitRequest()
    {
        $this->validate();

        $user     = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            $this->message     = 'Data pelanggan tidak ditemukan.';
            $this->messageType = 'error';
            return;
        }

        $newPlan     = ServiceProfile::find($this->selectedPlanId);
        $currentPlan = $this->currentServices->first()?->serviceProfile;

        if (!$newPlan) {
            $this->message     = 'Paket tidak ditemukan.';
            $this->messageType = 'error';
            return;
        }

        if ($currentPlan && $currentPlan->id === $newPlan->id) {
            $this->message     = 'Anda sudah menggunakan paket ini.';
            $this->messageType = 'error';
            return;
        }

        // Determine upgrade or downgrade
        $changeType = 'Perubahan';
        if ($currentPlan) {
            $changeType = ($newPlan->base_price > $currentPlan->base_price) ? 'Upgrade' : 'Downgrade';
        }

        // Save as notification for admin to review
        \App\Models\Notification\Notification::create([
            'uuid'         => (string) \Illuminate\Support\Str::uuid(),
            'recipient_id' => 1, // admin
            'title'        => "[{$changeType} Paket] {$customer->name}",
            'message'      => "Pelanggan {$customer->name} (#{$customer->code}) mengajukan {$changeType} paket dari \"{$currentPlan?->name}\" ke \"{$newPlan->name}\". Alasan: " . ($this->reason ?: '-'),
            'type'         => 'system',
            'status'       => 'pending',
            'created_by'   => $user->id,
            'updated_by'   => $user->id,
        ]);

        $this->message     = "Permintaan {$changeType} paket ke \"{$newPlan->name}\" berhasil dikirim! Tim kami akan segera memproses dalam 1x24 jam.";
        $this->messageType = 'success';
        $this->reset(['selectedPlanId', 'reason']);
    }

    public function render()
    {
        return view('livewire.customer-portal.self-service.change-plan');
    }
}
