<?php

namespace App\Livewire\Isp\HotspotCookie;

use App\Models\ISP\Router;
use App\Services\Adapters\Monitoring\MikroTikDriver;
use Livewire\Component;

class Index extends Component
{
    public $routers = [];
    public $selectedRouter = "";
    public $cookies = [];
    public $isLoading = false;

    public function mount()
    {
        $this->routers = Router::where("status", "active")->get();
        if ($this->routers->count() > 0) {
            $this->selectedRouter = $this->routers->first()->id;
            $this->loadCookies();
        }
    }

    public function updatedSelectedRouter()
    {
        $this->loadCookies();
    }

    public function loadCookies()
    {
        if (!$this->selectedRouter) return;
        
        $this->isLoading = true;
        try {
            $router = Router::find($this->selectedRouter);
            if ($router) {
                $driver = app(MikroTikDriver::class);
                $this->cookies = $driver->getHotspotCookies($router);
            }
        } catch (\Exception $e) {
            $this->dispatch("notify", ["type" => "error", "message" => "Gagal mengambil data cookies dari router."]);
            $this->cookies = [];
        }
        $this->isLoading = false;
    }

    public function removeCookie($macAddress)
    {
        try {
            $router = Router::find($this->selectedRouter);
            if ($router) {
                $driver = app(MikroTikDriver::class);
                $driver->removeHotspotCookie($router, $macAddress);
                $this->dispatch("notify", ["type" => "success", "message" => "Cookie berhasil dihapus (Kick)."]);
                $this->loadCookies();
            }
        } catch (\Exception $e) {
            $this->dispatch("notify", ["type" => "error", "message" => "Gagal menghapus cookie."]);
        }
    }

    public function render()
    {
        return view("livewire.isp.hotspot-cookie.index")->layout("layouts.app");
    }
}
