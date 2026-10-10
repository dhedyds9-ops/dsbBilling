<?php

namespace App\Livewire\Isp\Router;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\Router;
use App\Services\ISP\MonitoringService;
use Illuminate\Support\Facades\App;

class Show extends BaseNetworkComponent
{
    public $routerId;
    public Router $router;
    #[\Livewire\Attributes\Url(as: 'tab')]
    public string $activeTab = 'overview';
    public $systemInfo = [];
    public $isOnline = false;
    public $interfaces = [];
    public $pppActive = [];
    public $hotspotActive = [];

    public $logs = [];
    public $queueStats = [];
    public $pppServers = [];
    public $pppProfiles = [];
    public $pppSecrets = [];
    public $vpnServers = [];
    public string $searchPpp = '';
    public string $searchHotspot = '';
        public $dhcpServers = [];
    public $dhcpLeases = [];
    public $firewallFilters = [];
    public $firewallNat = [];
    public $routes = [];
    
    // Traffic Graph
    public string $selectedTrafficInterface = '';
    public $liveTx = 0;
    public $liveRx = 0;
    public $liveTrafficData = [];
    public $hotspotServers = [];
    public $hotspotProfiles = [];
    public $walledGarden = [];

    // Terminal
    public string $terminalInput = '';
    public array $terminalOutput = [];
    public bool $isTerminalRunning = false;
    
    public $provisioningToken = null;
    public $provisioningExpires = null;
    public bool $showProvisioningModal = false;

        public function updatedSelectedTrafficInterface()
    {
        $this->liveTrafficData = [];
        $this->liveTx = 0;
        $this->liveRx = 0;
        $this->updateTraffic();
    }

    public function updateTraffic()
    {
        if (!$this->selectedTrafficInterface || $this->activeTab !== 'traffic') return;
        
        $driver = new \App\Services\Adapters\Monitoring\MikroTikDriver();
        \Illuminate\Support\Facades\Log::info('Updating traffic', ['interface' => $this->selectedTrafficInterface, 'router_pwd' => $this->router->password ? 'exists' : 'missing']);
        $stats = $driver->getTrafficStats($this->router, $this->selectedTrafficInterface);
        \Illuminate\Support\Facades\Log::info('Stats', $stats);
        
        $this->liveTx = $stats['tx-bits-per-second'] ?? 0;
        $this->liveRx = $stats['rx-bits-per-second'] ?? 0;
        
        $data = $this->liveTrafficData;
        $data[] = [
            'time' => now()->format('H:i:s'),
            'tx' => (int) $this->liveTx,
            'rx' => (int) $this->liveRx
        ];
        
        if (count($data) > 20) {
            array_shift($data);
        }
        $this->liveTrafficData = $data;
        
        $this->dispatch('trafficUpdated', 
            time: array_column($data, 'time'),
            tx: array_column($data, 'tx'),
            rx: array_column($data, 'rx')
        );
    }

    public function mount($id = null)
    {
        parent::mount();
        $this->routerId = $id;
        $this->router = Router::with(['pop', 'vendor'])->findOrFail($id);
        $this->activeModule = 'isp';
        $this->activePage = 'routers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'MikroTik', 'url' => route('isp.routers.index')],
            ['label' => 'Router', 'url' => route('isp.routers.index')],
            ['label' => $this->router->name],
        ];

        $this->loadData();
    }

    public function loadData()
    {
        $driver = new \App\Services\Adapters\Monitoring\MikroTikDriver();

        if (!$driver) {
            $this->isOnline = false;
            return;
        }

        // Always load basic system info for the header (Live from Driver)
        $this->systemInfo = $driver->getSystemInfo($this->router);
        $this->isOnline = $driver->ping($this->router);

        if ($this->activeTab === 'overview') {
            // Extra overview
        } elseif ($this->activeTab === 'interfaces') {
            $this->interfaces = $driver->getInterfaceStats($this->router);
            $this->queueStats = $driver->getQueueStats($this->router);
        } elseif ($this->activeTab === 'ppp') {
            $this->pppActive = $driver->getPPPActive($this->router);
            $this->pppServers = $driver->getPppServers($this->router);
            $this->pppProfiles = $driver->getPppProfiles($this->router);
            $this->pppSecrets = $driver->getPppSecrets($this->router);
            $this->vpnServers = $driver->getVpnServers($this->router);
        } elseif ($this->activeTab === 'hotspot') {
            $this->hotspotActive = $driver->getHotspotActive($this->router);
            $this->hotspotServers = $driver->getHotspotServers($this->router);
            $this->hotspotProfiles = $driver->getHotspotProfiles($this->router);
            $this->walledGarden = $driver->getWalledGarden($this->router);
                } elseif ($this->activeTab === 'dhcp') {
            $this->dhcpServers = $driver->getDhcpServers($this->router);
            $this->dhcpLeases = $driver->getDhcpLeases($this->router);
        } elseif ($this->activeTab === 'firewall') {
            $this->firewallFilters = $driver->getFirewallFilters($this->router);
            $this->firewallNat = $driver->getFirewallNat($this->router);
        } elseif ($this->activeTab === 'routing') {
            $this->routes = $driver->getRoutes($this->router);
                } elseif ($this->activeTab === 'traffic') {
            $this->interfaces = $driver->getInterfaceStats($this->router);
            if (empty($this->selectedTrafficInterface) && count($this->interfaces) > 0) {
                $this->selectedTrafficInterface = $this->interfaces[0]['name'] ?? '';
                $this->updateTraffic();
            }
        } elseif ($this->activeTab === 'logs') {
            $this->logs = $driver->getLogs($this->router, 100);
        }
    }

    public function refreshData()
    {
        $this->loadData();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadData(); // Load data specifically for this tab to save API calls
    }

    public function executeTerminalCommand()
    {
        if (empty(trim($this->terminalInput))) {
            return;
        }

        $this->isTerminalRunning = true;
        
        // Add command to output log
        $this->terminalOutput[] = [
            'type' => 'input',
            'text' => '> ' . $this->terminalInput
        ];

        try {
            $driver = new \App\Services\Adapters\Monitoring\MikroTikDriver();
            $response = $driver->runTerminalCommand($this->router, $this->terminalInput);
            
            if (isset($response['error'])) {
                $this->terminalOutput[] = [
                    'type' => 'error',
                    'text' => $response['error']
                ];
            } else {
                $this->terminalOutput[] = [
                    'type' => 'output',
                    'text' => json_encode($response, JSON_PRETTY_PRINT)
                ];
            }
        } catch (\Exception $e) {
            $this->terminalOutput[] = [
                'type' => 'error',
                'text' => 'Exception: ' . $e->getMessage()
            ];
        }

        $this->terminalInput = '';
        $this->isTerminalRunning = false;
        
        // Dispatch browser event to scroll to bottom
        $this->dispatch('terminal-updated');
    }

    public function generateProvisioningToken()
    {
        $service = \Illuminate\Support\Facades\App::make(\App\Services\Provisioning\RouterProvisioningService::class);
        $session = $service->generateSession($this->router, auth()->id());
        
        $this->provisioningToken = $session->raw_token;
        $this->provisioningExpires = $session->expires_at->diffForHumans();
        $this->showProvisioningModal = true;
    }

        public function disconnectPpp($username)
    {
        try {
            $service = app(\App\Integration\MikroTik\Services\RouterOSService::class);
            $driver = $service->getDriver($this->router);
            if ($driver->connect()) {
                if ($driver->disconnectPppoeUser($username)) {
                    session()->flash('success', "User PPP {$username} berhasil diputuskan.");
                } else {
                    session()->flash('error', "Gagal memutuskan user PPP {$username}.");
                }
                $driver->disconnect();
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
        $this->loadData();
    }

    public function disconnectHotspot($username)
    {
        try {
            $service = app(\App\Integration\MikroTik\Services\RouterOSService::class);
            $driver = $service->getDriver($this->router);
            if ($driver->connect()) {
                if ($driver->disconnectHotspotUser($username)) {
                    session()->flash('success', "User Hotspot {$username} berhasil diputuskan.");
                } else {
                    session()->flash('error', "Gagal memutuskan user Hotspot {$username}.");
                }
                $driver->disconnect();
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
        $this->loadData();
    }

    public function closeProvisioningModal()
    {
        $this->showProvisioningModal = false;
        $this->provisioningToken = null;
    }
    
    public function rebootRouter()
    {
        $driver = new \App\Services\Adapters\Monitoring\MikroTikDriver();
        $success = $driver->rebootRouter($this->router);
        
        if ($success) {
            session()->flash('success', 'Perintah reboot telah dikirim ke router.');
        } else {
            session()->flash('error', 'Gagal mengirim perintah reboot.');
        }
        
        $this->loadData();
    }

    public function backupRouter()
    {
        $driver = new \App\Services\Adapters\Monitoring\MikroTikDriver();
        $result = $driver->backupRouter($this->router);
        
        if ($result) {
            session()->flash('success', "Backup berhasil dibuat di router dengan nama: {$result['filename']}");
        } else {
            session()->flash('error', 'Gagal membuat backup di router.');
        }
    }

    public function render()
    {
        return view('livewire.isp.router.show')->layout('layouts.router-panel', ['activeTab' => $this->activeTab]);
    }
}







