<?php

namespace App\Livewire\ResellerPortal\Finance;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\Auth;

class Balance extends AdminComponent
{
    public $user;
    
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.finance.balance';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Data Keuangan', 'url' => '#'],
            ['label' => 'Saldo Deposit', 'url' => route('reseller-portal.finance.balance')],
        ];
        
        $this->user = Auth::user();
    }

    public function render()
    {
        // Get total topup approved this month
        $totalTopup = Payment::where('customer_id', $this->user->id)
            ->where('gateway', 'reseller_topup')
            ->where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
            
        // Get total pending topup
        $totalPending = Payment::where('customer_id', $this->user->id)
            ->where('gateway', 'reseller_topup')
            ->where('status', 'pending')
            ->sum('amount');

        return view('livewire.reseller-portal.finance.balance', compact('totalTopup', 'totalPending'));
    }
}
