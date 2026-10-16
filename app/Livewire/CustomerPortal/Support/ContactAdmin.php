<?php

namespace App\Livewire\CustomerPortal\Support;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

#[Layout('layouts.customer-app')]
class ContactAdmin extends Component
{
    public $isPppoe = false;
    public $isHotspot = false;
    public $whatsappNumber;

    public function mount()
    {
        $companyPhone = Setting::getValue('company.phone', '');
        $this->whatsappNumber = $companyPhone ? preg_replace('/[^0-9]/', '', $companyPhone) : '';
        
        $customer = \App\Models\CRM\Customer::with(['customerServices.serviceProfile', 'customerServices.pppoeUser', 'customerServices.hotspotUser'])
            ->where('user_id', Auth::id())
            ->first();
            
        if ($customer) {
            foreach ($customer->customerServices as $cs) {
                $rawType = strtolower($cs->serviceProfile?->service_type ?? '');
                if (in_array($rawType, ['hotspot', 'member_hotspot', 'voucher']) || ($cs->hotspotUser && !$cs->pppoeUser)) {
                    $this->isHotspot = true;
                } else {
                    $this->isPppoe = true;
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.customer-portal.support.contact-admin');
    }
}
