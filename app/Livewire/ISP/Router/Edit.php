<?php

namespace App\Livewire\ISP\Router;

use App\Livewire\AdminComponent;
use App\Models\ISP\Router as RouterModel;
use App\Models\ISP\Pop;
use App\Models\ISP\Vendor;
use App\Services\ISP\RouterService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $routerId;
    public $router;
    public $pop_id;
    public $vendor_id;
    public $code;
    public $name;
    public $description;
    public $model;
    public $serial_number;
    public $ip_address;
    public $api_port = 8728;
    public $use_ssl = false;
    public $timeout = 30;
    public $username;
    public $password;
    public $routeros_version;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'routers';
        $this->routerId = $id;
        $this->router = RouterModel::findOrFail($id);

        $this->pop_id = $this->router->pop_id;
        $this->vendor_id = $this->router->vendor_id;
        $this->code = $this->router->code;
        $this->name = $this->router->name;
        $this->description = $this->router->description;
        $this->model = $this->router->model;
        $this->serial_number = $this->router->serial_number;
        $this->ip_address = $this->router->ip_address;
        $this->api_port = $this->router->api_port;
        $this->use_ssl = $this->router->use_ssl;
        $this->timeout = $this->router->timeout;
        $this->username = $this->router->username;
        $this->password = $this->router->password;
        $this->routeros_version = $this->router->routeros_version;
        $this->status = $this->router->status;

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'MikroTik', 'url' => route('isp.routers.index')],
            ['label' => 'Router', 'url' => route('isp.routers.index')],
            ['label' => $this->router->name, 'url' => route('isp.routers.show', $this->routerId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:routers,code,' . $this->routerId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(RouterService::class);
        $service->update($this->router, [
            'pop_id' => $this->pop_id,
            'vendor_id' => $this->vendor_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'ip_address' => $this->ip_address,
            'api_port' => $this->api_port,
            'use_ssl' => $this->use_ssl,
            'timeout' => $this->timeout,
            'username' => $this->username,
            'password' => $this->password,
            'routeros_version' => $this->routeros_version,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'Router berhasil diperbarui!');
        return redirect()->route('isp.routers.show', $this->routerId);
    }

    public function render()
    {
        $pops = Pop::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.router.edit', compact('pops', 'vendors'));
    }
}
