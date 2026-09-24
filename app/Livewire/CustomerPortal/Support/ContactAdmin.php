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
        $this->whatsappNumber = preg_replace('/[^0-9]/', '', Setting::getValue('company.phone', '08123456789'));
        
        $customer = \App\Models\CRM\Customer::with(['customerServices.pppoeUser', 'customerServices.hotspotUser'])
            ->where('user_id', Auth::id())
            ->first();
            
        if ($customer) {
            foreach ($customer->customerServices as $cs) {
                if ($cs->pppoeUser) $this->isPppoe = true;
                if ($cs->hotspotUser) $this->isHotspot = true;
            }
        }
    }

    public function render()
    {
        return view('livewire.customer-portal.support.contact-admin');
    }
}
