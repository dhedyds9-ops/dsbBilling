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

        $pppoeQuery = \App\Models\ISP\PppActiveSession::with([
            'router',
            'pppoeUser' => fn($q) => $q->withoutGlobalScopes()->with([
                'reseller',
                'customer' => fn($cq) => $cq->withoutGlobalScopes()->with('reseller'),
                'customerService' => fn($csq) => $csq->withoutGlobalScopes()->with([
                    'customer' => fn($cq) => $cq->withoutGlobalScopes()->with('reseller'),
                    'serviceProfile' => fn($sq) => $sq->withoutGlobalScopes(),
                ]),
                'serviceProfile' => fn($sq) => $sq->withoutGlobalScopes(),
            ]),
        ]);
        
        $voucherQuery = \App\Models\ISP\HotspotActiveSession::with(['router', 'voucher.reseller'])
            ->whereExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))->from('vouchers')->whereRaw('vouchers.code = hotspot_active_sessions.user');
            });

        $hotspotQuery = \App\Models\ISP\HotspotActiveSession::with([
            'router',
            'hotspotUser' => fn($q) => $q->withoutGlobalScopes()->with([
                'reseller',
                'customer' => fn($cq) => $cq->withoutGlobalScopes()->with('reseller'),
                'customerService' => fn($csq) => $csq->withoutGlobalScopes()->with([
                    'customer' => fn($cq) => $cq->withoutGlobalScopes()->with('reseller'),
                    'serviceProfile' => fn($sq) => $sq->withoutGlobalScopes(),
                ]),
                'serviceProfile' => fn($sq) => $sq->withoutGlobalScopes(),
            ]),
        ])
            ->whereNotExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))->from('vouchers')->whereRaw('vouchers.code = hotspot_active_sessions.user');
            });
        
        if ($user->hasRole('reseller')) {
            $effectiveResellerId = $user->getEffectiveResellerId();
            $pppoeQuery->whereExists(function ($sub) use ($effectiveResellerId) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('pppoe_users')
                    ->leftJoin('customer_services', 'pppoe_users.customer_service_id', '=', 'customer_services.id')
                    ->leftJoin('customers', 'customer_services.customer_id', '=', 'customers.id')
                    ->where(function($uq) {
                        $uq->whereRaw('LOWER(pppoe_users.username) = LOWER(ppp_active_sessions.name)')
                           ->orWhereRaw('LOWER(ppp_active_sessions.name) LIKE LOWER(pppoe_users.username) || \'@%\'')
                           ->orWhereRaw('LOWER(pppoe_users.username) LIKE LOWER(ppp_active_sessions.name) || \'@%\'');
                    })
                    ->whereNull('pppoe_users.deleted_at')
                    ->where(function($q) use ($effectiveResellerId) {
                        $q->where('pppoe_users.reseller_id', $effectiveResellerId)
                          ->orWhere('customers.reseller_id', $effectiveResellerId)
                          ->orWhere('customers.created_by', $effectiveResellerId)
                          ->orWhere('customer_services.reseller_id', $effectiveResellerId);
                    });
            });
            $hotspotQuery->whereExists(function ($sub) use ($effectiveResellerId) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('hotspot_users')
                    ->leftJoin('customer_services', 'hotspot_users.customer_service_id', '=', 'customer_services.id')
                    ->leftJoin('customers', 'customer_services.customer_id', '=', 'customers.id')
                    ->where(function($uq) {
                        $uq->whereRaw('LOWER(hotspot_users.username) = LOWER(hotspot_active_sessions.user)')
                           ->orWhereRaw('LOWER(hotspot_active_sessions.user) LIKE LOWER(hotspot_users.username) || \'@%\'')
                           ->orWhereRaw('LOWER(hotspot_users.username) LIKE LOWER(hotspot_active_sessions.user) || \'@%\'');
                    })
                    ->whereNull('hotspot_users.deleted_at')
                    ->where(function($q) use ($effectiveResellerId) {
                        $q->where('hotspot_users.reseller_id', $effectiveResellerId)
                          ->orWhere('customers.reseller_id', $effectiveResellerId)
                          ->orWhere('customers.created_by', $effectiveResellerId)
                          ->orWhere('customer_services.reseller_id', $effectiveResellerId);
                    });
            });
            $voucherQuery->whereExists(function ($sub) use ($effectiveResellerId) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('vouchers')
                    ->whereRaw('vouchers.code = hotspot_active_sessions.user')
                    ->where('vouchers.reseller_id', $effectiveResellerId);
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
