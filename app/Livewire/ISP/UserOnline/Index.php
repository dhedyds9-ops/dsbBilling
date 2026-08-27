<?php

namespace App\Livewire\ISP\UserOnline;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\Voucher;
use App\Services\ISP\Session\SessionKickService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Index extends BaseNetworkComponent
{
    public string $activeTab = 'pppoe';

    protected $listeners = ['echo:user-online,UserOnlineUpdated' => '$refresh'];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'user-online';
        $this->filters = [];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.service-profiles.index')],
            ['label' => 'User Online'],
        ];
    }

    public function setActiveTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetTabPages();
    }

    public function kickPppoe(int $sessionId)
    {
        try {
            $session = PppActiveSession::with('router')->findOrFail($sessionId);
            /** @var SessionKickService $kicker */
            $kicker = app(SessionKickService::class);
            $result = $kicker->kickPppoe($session, Auth::user());

            $msg = 'PPPoE user berhasil diputuskan!';
            $pod = $result['pod'] ?? null;
            if (!$result['success'] || $pod === null || !($pod['success'] ?? false)) {
                $msg .= ' (Hanya via API RouterOS — PoD RADIUS gagal/tidak tersedia. Radius user dapat reconnect.)';
            }
            session()->flash('success', $msg);
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error('Failed to kick PPPoE user', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal memutuskan PPPoE user: ' . $e->getMessage());
        }
    }

    public function kickHotspot(int $sessionId)
    {
        try {
            $session = HotspotActiveSession::with('router')->findOrFail($sessionId);
            /** @var SessionKickService $kicker */
            $kicker = app(SessionKickService::class);
            $result = $kicker->kickHotspot($session, Auth::user());

            $msg = 'Hotspot user berhasil diputuskan!';
            $pod = $result['pod'] ?? null;
            if (!$result['success'] || $pod === null || !($pod['success'] ?? false)) {
                $msg .= ' (Hanya via API RouterOS — PoD RADIUS gagal/tidak tersedia.)';
            }
            session()->flash('success', $msg);
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error('Failed to kick Hotspot user', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal memutuskan Hotspot user: ' . $e->getMessage());
        }
    }

    public function kickAllUsernameSessions(string $username)
    {
        try {
            $username = trim(urldecode($username));
            if ($username === '') {
                session()->flash('error', 'Username tidak boleh kosong');
                return;
            }
            /** @var SessionKickService $kicker */
            $kicker = app(SessionKickService::class);
            $result = $kicker->kickUsernameGlobally($username, Auth::user());

            $count = $result['count'] ?? 0;
            session()->flash('success', "Global kick username {$username}: {$count} session ditangani.");
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error('Global kick username gagal', ['u' => $username, 'err' => $e->getMessage()]);
            session()->flash('error', 'Global kick gagal: ' . $e->getMessage());
        }
    }

    public function updatedSearch()
    {
        $this->resetTabPages();
    }

    public function updatedPerPage()
    {
        $this->resetTabPages();
    }

    private function resetTabPages(): void
    {
        $this->resetPage('pppoePage');
        $this->resetPage('hotspotPage');
        $this->resetPage('voucherPage');
    }

    public function render()
    {
        $pppoeQuery = PppActiveSession::with(['router'])
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhere('uptime', 'like', '%' . $this->search . '%')
                        ->orWhereHas('router', function ($routerQuery) {
                            $routerQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy('session_started_at', 'desc');

        $hotspotQuery = HotspotActiveSession::with(['router'])
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('user', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhere('mac_address', 'like', '%' . $this->search . '%')
                        ->orWhere('server', 'like', '%' . $this->search . '%')
                        ->orWhere('uptime', 'like', '%' . $this->search . '%')
                        ->orWhereHas('router', function ($routerQuery) {
                            $routerQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy('session_started_at', 'desc');

        $voucherQuery = Voucher::with(['hotspotUser', 'serviceProfile', 'owner'])
            ->where('status', '!=', 'available')
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('code', 'like', '%' . $this->search . '%')
                        ->orWhere('status', 'like', '%' . $this->search . '%')
                        ->orWhereHas('hotspotUser', function ($hotspotUserQuery) {
                            $hotspotUserQuery->where('username', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('serviceProfile', function ($serviceProfileQuery) {
                            $serviceProfileQuery->where('name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('owner', function ($ownerQuery) {
                            $ownerQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy('activated_at', 'desc');

        $pppoeSessions = $pppoeQuery->paginate($this->perPage, ['*'], 'pppoePage');
        $hotspotSessions = $hotspotQuery->paginate($this->perPage, ['*'], 'hotspotPage');
        $vouchers = $voucherQuery->paginate($this->perPage, ['*'], 'voucherPage');

        return view('livewire.isp.user-online.index', compact('pppoeSessions', 'hotspotSessions', 'vouchers'));
    }
}
