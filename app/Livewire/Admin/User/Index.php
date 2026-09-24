<?php

namespace App\Livewire\Admin\User;

use App\Livewire\AdminComponent;
use App\Models\User as UserModel;
use App\Models\ISP\Voucher;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $filters = [];
    public $showFilters = false;
    
    public $showMappingModal = false;
    public $mappingUserId = null;
    public $mappingWilayah = '';
    public $mappingUser = null;
    
    public $showSummaryModal = false;
    public $summaryUser = null;
    public $summaryData = [];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Users'],
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->filters = ['status' => ''];
        $this->search = '';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openMappingModal($userId)
    {
        $this->mappingUserId = $userId;
        $this->mappingUser = UserModel::find($userId);
        $this->mappingWilayah = $this->mappingUser->wilayah ?? '';
        $this->showMappingModal = true;
    }

    public function closeMappingModal()
    {
        $this->showMappingModal = false;
        $this->mappingUserId = null;
        $this->mappingUser = null;
        $this->mappingWilayah = '';
    }

    public function saveMapping()
    {
        if ($this->mappingUser) {
            $this->mappingUser->wilayah = $this->mappingWilayah;
            $this->mappingUser->save();
            session()->flash('success', 'Mapping area berhasil disimpan.');
        }
        $this->closeMappingModal();
    }

    public function delete($id)
    {
        $user = UserModel::findOrFail($id);
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri!');
            return;
        }
        $user->delete();
        session()->flash('success', 'User berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function showSummary($userId)
    {
        $this->summaryUser = UserModel::find($userId);
        
        $vouchersHariIni = Voucher::with('serviceProfile')->where('created_by', $userId)->whereDate('activated_at', today())->get();
        $vouchersBulanIni = Voucher::with('serviceProfile')->where('created_by', $userId)->whereMonth('activated_at', today()->month)->whereYear('activated_at', today()->year)->get();
        
        $this->summaryData = [
            'income_hari_ini' => $vouchersHariIni->sum(fn($v) => $v->serviceProfile->price ?? 0),
            'income_bulan_ini' => $vouchersBulanIni->sum(fn($v) => $v->serviceProfile->price ?? 0),
            'fee_hari_ini' => $vouchersHariIni->sum('fee_seller'),
            'fee_bulan_ini' => $vouchersBulanIni->sum('fee_seller'),
            'stok_voucher' => Voucher::where('created_by', $userId)->where('status', 'available')->count(),
            'voucher_expired' => Voucher::where('created_by', $userId)->where('status', 'expired')->count(),
            'voucher_bulan_ini' => Voucher::where('created_by', $userId)->whereMonth('created_at', today()->month)->whereYear('created_at', today()->year)->count(),
            
            'jml_hotspot' => HotspotUser::where('created_by', $userId)->count(),
            'jml_ppp' => PPPoEUser::where('created_by', $userId)->count(),
            'isolir_hotspot' => HotspotUser::where('created_by', $userId)->whereIn('status', ['isolated', 'suspended'])->count(),
            'isolir_ppp' => PPPoEUser::where('created_by', $userId)->whereIn('status', ['isolated', 'suspended'])->count(),
            'hotspot_bulan_ini' => HotspotUser::where('created_by', $userId)->whereMonth('created_at', today()->month)->whereYear('created_at', today()->year)->count(),
            'ppp_bulan_ini' => PPPoEUser::where('created_by', $userId)->whereMonth('created_at', today()->month)->whereYear('created_at', today()->year)->count(),
        ];
        
        $this->showSummaryModal = true;
    }

    public function closeSummaryModal()
    {
        $this->showSummaryModal = false;
        $this->summaryUser = null;
        $this->summaryData = [];
    }

    public function render()
    {
        $query = UserModel::query()
            ->with('roles')
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['customer', 'pppoe', 'hotspot']);
            });

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $users = $query->orderBy($this->sortField, $this->sortDirection)
                      ->paginate($this->perPage === 'all' ? 999999 : $this->perPage);

        return view('livewire.admin.user.index', compact('users'));
    }
}
