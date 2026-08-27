<?php

namespace App\Livewire\ISP\Router;

use App\Livewire\AdminComponent;
use App\Models\ISP\Pop;
use App\Models\ISP\Vendor;
use App\Services\ISP\RouterService;
use Illuminate\Support\Facades\Auth;

class Create extends AdminComponent
{
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
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'routers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'MikroTik', 'url' => route('isp.routers.index')],
            ['label' => 'Router', 'url' => route('isp.routers.index')],
            ['label' => 'Buat Baru'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:routers,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(RouterService::class);
        $service->create([
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

        session()->flash('success', 'Router berhasil dibuat!');
        return redirect()->route('isp.routers.index');
    }

    public function render()
    {
        $pops = Pop::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.router.create', compact('pops', 'vendors'));
    }
}
