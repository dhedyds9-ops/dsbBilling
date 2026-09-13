<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\PppActiveSession;
use App\Services\ISP\Session\SessionKickService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.customer-app')]
class ActiveSessions extends Component
{
    use WithPagination;

    public $search = '';
    public $message = '';
    public $messageType = 'success';

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function kickSession(string $type, int $sessionId, SessionKickService $kickService)
    {
        $user = Auth::user();
        $customer = $user->customer;
        
        if (!$customer) {
            $this->showMessage('Akses ditolak: Data pelanggan tidak ditemukan.', 'error');
            return;
        }

        // Dapatkan semua username yang dimiliki oleh pelanggan ini
        $validUsernames = CustomerService::where('customer_id', $customer->id)
            ->pluck('username')
            ->toArray();

        try {
            if ($type === 'pppoe') {
                $session = PppActiveSession::find($sessionId);
                if (!$session || !in_array($session->name, $validUsernames)) {
                    $this->showMessage('Sesi PPPoE tidak ditemukan atau Anda tidak memiliki akses.', 'error');
                    return;
                }
                $result = $kickService->kickPppoe($session, $user);
            } else {
                $session = HotspotActiveSession::find($sessionId);
                if (!$session || !in_array($session->user, $validUsernames)) {
                    $this->showMessage('Sesi Hotspot tidak ditemukan atau Anda tidak memiliki akses.', 'error');
                    return;
                }
                $result = $kickService->kickHotspot($session, $user);
            }

            if (isset($result['success']) && $result['success']) {
                $this->showMessage('Perangkat berhasil diputuskan dari jaringan.', 'success');
            } else {
                $errorMsg = $result['error'] ?? 'Terjadi kesalahan sistem saat memutus koneksi.';
                $this->showMessage('Gagal memutus koneksi: ' . $errorMsg, 'error');
            }

        } catch (\Throwable $e) {
            Log::error('Customer kick session error: ' . $e->getMessage());
            $this->showMessage('Gagal memutus koneksi karena gangguan teknis.', 'error');
        }
    }

    private function showMessage($message, $type)
    {
        $this->message = $message;
        $this->messageType = $type;
        $this->dispatch('alert', type: $type, message: $message);
    }

    public function render()
    {
        $customer = Auth::user()->customer;
        $usernames = [];
        
        if ($customer) {
            $usernames = CustomerService::where('customer_id', $customer->id)
                ->pluck('username')
                ->toArray();
        }

        $queryStr = '%' . $this->search . '%';

        // PPPoE Sessions
        $pppoeSessions = empty($usernames) ? collect() : PppActiveSession::with('router')
            ->whereIn('name', $usernames)
            ->when($this->search, function ($query) use ($queryStr) {
                $query->where(function($q) use ($queryStr) {
                    $q->where('name', 'like', $queryStr)
                      ->orWhere('address', 'like', $queryStr)
                      ->orWhere('caller_id', 'like', $queryStr);
                });
            })
            ->get()
            ->map(function($item) {
                return (object)[
                    'id' => $item->id,
                    'type' => 'pppoe',
                    'username' => $item->name,
                    'ip_address' => $item->address,
                    'mac_address' => $item->caller_id,
                    'uptime' => $item->uptime,
                    'router' => $item->router ? $item->router->name : 'N/A',
                ];
            });

        // Hotspot Sessions
        $hotspotSessions = empty($usernames) ? collect() : HotspotActiveSession::with('router')
            ->whereIn('user', $usernames)
            ->when($this->search, function ($query) use ($queryStr) {
                $query->where(function($q) use ($queryStr) {
                    $q->where('user', 'like', $queryStr)
                      ->orWhere('address', 'like', $queryStr)
                      ->orWhere('mac_address', 'like', $queryStr);
                });
            })
            ->get()
            ->map(function($item) {
                return (object)[
                    'id' => $item->id,
                    'type' => 'hotspot',
                    'username' => $item->user,
                    'ip_address' => $item->address,
                    'mac_address' => $item->mac_address,
                    'uptime' => $item->uptime,
                    'router' => $item->router ? $item->router->name : 'N/A',
                ];
            });

        $allSessions = $pppoeSessions->concat($hotspotSessions);

        return view('livewire.customer-portal.self-service.active-sessions', [
            'sessions' => $allSessions
        ]);
    }
}
