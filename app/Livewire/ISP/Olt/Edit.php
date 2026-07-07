<?php

namespace App\Livewire\ISP\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt as OltModel;
use App\Models\ISP\Pop;
use App\Models\ISP\Vendor;
use App\Services\ISP\OltService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $oltId;
    public $olt;
    public $pop_id;
    public $vendor_id;
    public $code;
    public $name;
    public $description;
    public $model;
    public $serial_number;
    public $ip_address;
    public $username;
    public $password;
    public $port_count;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'olts';
        $this->oltId = $id;
        $this->olt = OltModel::findOrFail($id);

        $this->pop_id = $this->olt->pop_id;
        $this->vendor_id = $this->olt->vendor_id;
        $this->code = $this->olt->code;
        $this->name = $this->olt->name;
        $this->description = $this->olt->description;
        $this->model = $this->olt->model;
        $this->serial_number = $this->olt->serial_number;
        $this->ip_address = $this->olt->ip_address;
        $this->username = $this->olt->username;
        $this->password = $this->olt->password;
        $this->port_count = $this->olt->port_count;
        $this->status = $this->olt->status;
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.olts.index')],
            ['label' => 'OLT', 'url' => route('isp.olts.index')],
            ['label' => $this->olt->name, 'url' => route('isp.olts.show', $this->oltId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:isp_olts,code,' . $this->oltId,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(OltService::class);
        $service->update($this->olt, [
            'pop_id' => $this->pop_id,
            'vendor_id' => $this->vendor_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'ip_address' => $this->ip_address,
            'username' => $this->username,
            'password' => $this->password,
            'port_count' => $this->port_count,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'OLT berhasil diperbarui!');
        return redirect()->route('isp.olts.show', $this->oltId);
    }

    public function render()
    {
        $pops = Pop::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.olt.edit', compact('pops', 'vendors'));
    }
}
