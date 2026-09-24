<?php

namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\Voucher;
use App\Services\ISP\Session\SessionKickService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserOnline extends \App\Livewire\ISP\BaseNetworkComponent
{
    public string $activeTab = 'pppoe';
    public bool $showFilterModal = false;
    
    protected $listeners = ['echo:user-online,UserOnlineUpdated' => '$refresh', 'refresh-online' => '$refresh'];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'customers.user-online';
        $this->filters = [];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'User Online'],
        ];
    }

    public function openFilterModal()
    {
        $this->showFilterModal = true;
    }

    public function applyFilter()
    {
        $this->showFilterModal = false;
        $this->resetPage();
    }

    public function setActiveTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function kickPppoe(int $sessionId)
    {
        try {
            $session = PppActiveSession::with('router')->findOrFail($sessionId);
            $kicker = app(SessionKickService::class);
            $result = $kicker->kickPppoe($session, Auth::user());

            $msg = 'PPPoE user berhasil diputuskan!';
            $pod = $result['pod'] ?? null;
            if (!$result['success'] || $pod === null || !($pod['success'] ?? false)) {
                $msg .= ' (Hanya via API RouterOS — PoD RADIUS gagal/tidak tersedia. Radius user dapat reconnect.)';
            }
            session()->flash('success', $msg);
            $this->dispatch('refresh-online');
        } catch (\Exception $e) {
            Log::error('Failed to kick PPPoE user', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal memutuskan PPPoE user: ' . $e->getMessage());
        }
    }

    public function kickHotspot(int $sessionId)
    {
        try {
            $session = HotspotActiveSession::with('router')->findOrFail($sessionId);
            $kicker = app(SessionKickService::class);
            $result = $kicker->kickHotspot($session, Auth::user());

            $msg = 'Hotspot user berhasil diputuskan!';
            $pod = $result['pod'] ?? null;
            if (!$result['success'] || $pod === null || !($pod['success'] ?? false)) {
                $msg .= ' (Hanya via API RouterOS — PoD RADIUS gagal/tidak tersedia.)';
            }
            session()->flash('success', $msg);
            $this->dispatch('refresh-online');
        } catch (\Exception $e) {
            Log::error('Failed to kick Hotspot user', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal memutuskan Hotspot user: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        $pppoeQuery = \App\Models\ISP\PppActiveSession::with(['router', 'pppoeUser.customerService.customer.createdBy']);
        
        $voucherQuery = \App\Models\ISP\HotspotActiveSession::with(['router', 'voucher.reseller'])
            ->whereExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))->from('vouchers')->whereRaw('vouchers.code = hotspot_active_sessions.user');
            });

        $hotspotQuery = \App\Models\ISP\HotspotActiveSession::with(['router', 'hotspotUser.customerService.customer.createdBy'])
            ->whereNotExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))->from('vouchers')->whereRaw('vouchers.code = hotspot_active_sessions.user');
            });
        
        if ($user->hasRole('reseller')) {
            $pppoeQuery->whereExists(function ($sub) use ($user) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('pppoe_users')
                    ->join('customer_services', 'pppoe_users.customer_service_id', '=', 'customer_services.id')
                    ->join('members', 'customer_services.customer_id', '=', 'members.id')
                    ->whereRaw('pppoe_users.username = ppp_active_sessions.name')
                    ->where('members.created_by', $user->id);
            });
            $hotspotQuery->whereExists(function ($sub) use ($user) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('hotspot_users')
                    ->join('customer_services', 'hotspot_users.customer_service_id', '=', 'customer_services.id')
                    ->join('members', 'customer_services.customer_id', '=', 'members.id')
                    ->whereRaw('hotspot_users.username = hotspot_active_sessions.user')
                    ->where('members.created_by', $user->id);
            });
            $voucherQuery->whereExists(function ($sub) use ($user) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('vouchers')
                    ->whereRaw('vouchers.code = hotspot_active_sessions.user')
                    ->where('vouchers.reseller_id', $user->id);
            });
        }

        $stats = [
            'pppoe' => (clone $pppoeQuery)->count(),
            'hotspot' => (clone $hotspotQuery)->count(),
            'voucher' => (clone $voucherQuery)->count(),
        ];
        $stats['total'] = $stats['pppoe'] + $stats['hotspot'] + $stats['voucher'];

        if ($this->search) {
            if ($this->activeTab === 'pppoe') {
                $pppoeQuery->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhere('uptime', 'like', '%' . $this->search . '%')
                        ->orWhereHas('router', function ($routerQuery) {
                            $routerQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            } elseif ($this->activeTab === 'hotspot') {
                $hotspotQuery->where(function ($subQuery) {
                    $subQuery->where('user', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhere('mac_address', 'like', '%' . $this->search . '%')
                        ->orWhere('server', 'like', '%' . $this->search . '%')
                        ->orWhereHas('router', function ($routerQuery) {
                            $routerQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            } elseif ($this->activeTab === 'voucher') {
                $voucherQuery->where(function ($subQuery) {
                    $subQuery->where('user', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhere('mac_address', 'like', '%' . $this->search . '%')
                        ->orWhere('server', 'like', '%' . $this->search . '%')
                        ->orWhereHas('router', function ($routerQuery) {
                            $routerQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            }
        }

        $results = [];
        if ($this->activeTab === 'pppoe') {
            $results = $pppoeQuery->orderBy('session_started_at', 'desc')->paginate($this->perPage);
        } elseif ($this->activeTab === 'hotspot') {
            $results = $hotspotQuery->orderBy('session_started_at', 'desc')->paginate($this->perPage);
        } else {
            $results = $voucherQuery->orderBy('session_started_at', 'desc')->paginate($this->perPage);
        }

        return view('livewire.reseller-portal.customer.user-online', compact('results', 'stats'));
    }
}
