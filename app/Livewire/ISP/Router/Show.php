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
    public $interfaces = [];
    public $pppActive = [];
    public $hotspotActive = [];

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
        $monitoringService = App::make(MonitoringService::class);

        $this->systemInfo = $monitoringService->getSystemInfo($this->router);
        $this->interfaces = $monitoringService->getInterfaceStats($this->router);
        $this->pppActive = $monitoringService->getPPPActive($this->router);
        $this->hotspotActive = $monitoringService->getHotspotActive($this->router);
    }

    public function refreshData()
    {
        $this->loadData();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.isp.router.show');
    }
}
