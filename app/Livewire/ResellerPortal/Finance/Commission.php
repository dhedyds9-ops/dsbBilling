<?php

namespace App\Livewire\ResellerPortal\Finance;

use App\Livewire\AdminComponent;
use App\Models\ISP\Voucher;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;
use Illuminate\Support\Facades\Auth;

class Commission extends AdminComponent
{
    public $search = '';
    public $perPage = 10;
    
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.finance.commission';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Data Keuangan', 'url' => '#'],
            ['label' => 'Komisi / Margin', 'url' => route('reseller-portal.finance.commission')],
        ];
    }

    public function render()
    {
        // For MVP, we'll list vouchers sold as a proxy for commissions/margins
        // Assuming voucher price = sell price, and we just show an estimated margin
        $query = Voucher::where('reseller_id', Auth::id())
            ->where('status', 'used')
            ->with('serviceProfile');
            
        if ($this->search) {
            $query->where('username', 'like', '%' . $this->search . '%');
        }

        $commissions = $query->orderBy('updated_at', 'desc')->paginate($this->perPage);

        return view('livewire.reseller-portal.finance.commission', [
            'commissions' => $commissions
        ]);
    }
}
