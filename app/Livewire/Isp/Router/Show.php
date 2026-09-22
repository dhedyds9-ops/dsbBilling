<?php

namespace App\Livewire\ISP\Router;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\Router;
use App\Services\ISP\MonitoringService;
use Illuminate\Support\Facades\App;

class Show extends BaseNetworkComponent
{
    public $routerId;
    public Router $router;
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
        return view('livewire.isp.router.show');
    }
}
