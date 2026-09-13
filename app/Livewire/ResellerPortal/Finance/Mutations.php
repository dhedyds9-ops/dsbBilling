<?php

namespace App\Livewire\ResellerPortal\Finance;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\Auth;

class Mutations extends AdminComponent
{
    public $search = '';
    public $perPage = 10;
    public $type = '';
    public $dateRange = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.finance.mutations';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Data Keuangan', 'url' => '#'],
            ['label' => 'Mutasi', 'url' => route('reseller-portal.finance.mutations')],
        ];
    }

    public function render()
    {
        $query = Payment::where('customer_id', Auth::id())
            //->where('status', 'success') // or approved
            ;
            
        if ($this->search) {
            $query->where(function($q) {
                $q->where('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhere('gateway_transaction_id', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->type === 'in') {
            $query->where('gateway', 'reseller_topup');
        } elseif ($this->type === 'out') {
            $query->where('gateway', '!=', 'reseller_topup');
        }

        $mutations = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.reseller-portal.finance.mutations', [
            'mutations' => $mutations
        ]);
    }
}
