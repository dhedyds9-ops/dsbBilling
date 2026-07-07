<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;

class Customer360 extends AdminComponent
{
    public $customerId;
    public $customer;
    public $activeTab = 'profile';
    
    protected $tabs = [
        'profile' => 'Profile',
        'contract' => 'Contract',
        'billing' => 'Billing',
        'invoice' => 'Invoice',
        'payment' => 'Payment',
        'ticket' => 'Ticket',
        'device' => 'Device',
        'installation' => 'Installation',
        'gis' => 'GIS',
        'monitoring' => 'Monitoring',
        'history' => 'History',
        'activity' => 'Activity',
        'notification' => 'Notification',
    ];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->customerId = $id;
        $this->customer = Customer::with([
            'customerServices.service',
            'customerServices.serviceProfile',
            'customerServices.onu',
            'invoices',
            'payments',
            'contracts',
            'installations',
        ])->findOrFail($id);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $timeline = [
            ['date' => $this->customer->created_at, 'title' => 'Customer Dibuat', 'description' => 'Customer baru ditambahkan ke sistem', 'type' => 'create'],
        ];
        
        if ($this->customer->installations->count() > 0) {
            foreach ($this->customer->installations as $inst) {
                $timeline[] = ['date' => $inst->completed_at ?? $inst->scheduled_at ?? $inst->created_at, 'title' => 'Instalasi', 'description' => $inst->notes ?? 'Instalasi dilakukan', 'type' => 'installation'];
            }
        }
        
        $activities = [
            ['user' => $this->customer->createdBy->name ?? 'Admin', 'action' => 'Membuat customer baru', 'module' => 'CRM', 'time' => $this->customer->created_at->diffForHumans()],
        ];

        $invoices = $this->customer->invoices;
        $payments = $this->customer->payments;
        $tickets = []; // placeholder until ticket module exists
        $devices = [];
        $this->customer->customerServices->each(function ($service) use (&$devices) {
            if ($service->onu) {
                $devices[] = [
                    'id' => $service->onu->id, 
                    'type' => 'ONT', 
                    'brand' => $service->onu->brand ?? 'Unknown', 
                    'model' => $service->onu->model ?? 'Unknown', 
                    'serial' => $service->onu->serial_number ?? 'Unknown', 
                    'status' => $service->onu->status ?? 'active',
                ];
            }
        });
        
        $installations = $this->customer->installations;
        $notifications = []; // placeholder until notification module exists

        return view('livewire.crm.customer.customer360', compact(
            'timeline', 
            'activities', 
            'invoices', 
            'payments', 
            'tickets', 
            'devices', 
            'installations',
            'notifications'
        ));
    }
}
