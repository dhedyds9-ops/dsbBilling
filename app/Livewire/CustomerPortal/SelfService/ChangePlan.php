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
    public string $planCategory = 'all'; // all, pppoe, hotspot

    protected $rules = [
        'selectedPlanId' => 'required|exists:service_profiles,id',
        'reason'         => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $this->loadPlans();
    }

    public function setCategory(string $category)
    {
        $this->planCategory = $category;
        $this->loadPlans();
    }

    public function loadPlans()
    {
        $user     = Auth::user();
        $customer = $user?->customer;

        if (!$customer) {
            $customer = \App\Models\CRM\Customer::withoutGlobalScopes()->where('user_id', auth()->id())->first();
        }

        if (!$customer) {
            $this->currentServices = collect();
            $this->availablePlans  = collect();
            return;
        }

        $this->currentServices = CustomerService::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->with(['serviceProfile', 'pppoeUser', 'hotspotUser'])
            ->get();

        // Query paket komersial (mengecualikan paket gratis / isolir)
        $query = ServiceProfile::active()
            ->where(function ($q) {
                $q->whereNull('is_free')->orWhere('is_free', false);
            })
            ->where('base_price', '>', 0)
            ->where(function ($q) {
                $q->whereNull('visibility')
                  ->orWhereNotIn('visibility', ['hidden', 'private']);
            });

        // Filter berdasarkan kategori tab jika dipilih
        if ($this->planCategory === 'pppoe') {
            $query->whereIn('service_type', ['pppoe', 'combined']);
        } elseif ($this->planCategory === 'hotspot') {
            $query->whereIn('service_type', ['hotspot', 'member_hotspot', 'voucher', 'combined']);
        } else {
            $query->whereIn('service_type', ['pppoe', 'hotspot', 'member_hotspot', 'voucher', 'combined']);
        }

        $this->availablePlans = $query->orderBy('base_price')->get();
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
