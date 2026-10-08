<?php

namespace App\Livewire\Isp\Technician\Installation;

use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.technician-app')]
class Register extends Component
{
    public $serialNumber = '';
    public $macAddress = '';
    public $selectedOlt = '';
    public $selectedOdp = '';
    public $profileName = '';
    
    // For dropdowns
    public $olts = [];
    public $odps = [];

    public function mount()
    {
        $this->serialNumber = request()->query('sn', '');
        $this->olts = Olt::active()->get();
        $this->odps = Odp::active()->get();
    }

    public function registerOnu()
    {
        $this->validate([
            'serialNumber' => 'required|string|min:4|unique:onus,serial_number',
            'selectedOlt' => 'required|exists:olts,id',
            'selectedOdp' => 'required|exists:odps,id',
            'profileName' => 'required|string',
        ]);

        // Simulating the actual ONU registration
        Onu::create([
            'serial_number' => strtoupper($this->serialNumber),
            'mac_address' => strtoupper($this->macAddress),
            'olt_id' => $this->selectedOlt,
            'odp_id' => $this->selectedOdp,
            'status' => 'active',
            'profile_name' => $this->profileName,
            'code' => 'ONU-' . strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $this->serialNumber ?? Str::random(6)), -6)),
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', 'ONU berhasil diregistrasi ke sistem dan OLT.');
        return redirect()->route('technician.installation.scan');
    }

    public function render() { return view('livewire.isp.technician.installation.register'); }
}

