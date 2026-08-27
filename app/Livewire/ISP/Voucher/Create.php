<?php

namespace App\Livewire\ISP\Voucher;

use App\Livewire\AdminComponent;
use App\Models\User;
use App\Models\ISP\NasDevice;
use App\Models\ISP\ServiceProfile;
use App\Services\ISP\VoucherService;
use Illuminate\Support\Str;

class Create extends AdminComponent
{
    public $type = 'hotspot';
    public $nas_device_id;
    public $owner_id;
    public $bind_on_login = false;
    public $service_profile_id;
    public $fee_seller = 0;
    public $login_method = 'voucher_code';
    public $quantity = 1;
    public $length = 6;
    public $prefix;
    public $code_combination = 'uppercase_alphanumeric';
    public $validity_days;
    public $notes;

    public $generated_vouchers = [];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'vouchers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.vouchers.index')],
            ['label' => 'Vouchers', 'url' => route('isp.vouchers.index')],
            ['label' => 'Tambah Voucher'],
        ];
    }

    public function generate(VoucherService $service)
    {
        $this->validate([
            'type' => 'required|in:hotspot,pppoe',
            'service_profile_id' => 'required|exists:service_profiles,id',
            'quantity' => 'required|integer|min:1|max:5000',
            'length' => 'required|integer|min:4|max:32',
            'validity_days' => 'nullable|integer|min:1',
            'fee_seller' => 'nullable|numeric|min:0',
            'nas_device_id' => 'nullable|exists:nas_devices,id',
            'owner_id' => 'nullable|exists:users,id',
            'login_method' => 'required|in:voucher_code,username_password',
            'code_combination' => 'required|in:uppercase,lowercase,alphanumeric,numbers,uppercase_alphanumeric',
        ]);

        try {
            $attrs = [
                'type' => $this->type,
                'nas_device_id' => $this->nas_device_id,
                'owner_id' => $this->owner_id,
                'service_profile_id' => $this->service_profile_id,
                'bind_on_login' => (bool)$this->bind_on_login,
                'fee_seller' => (float)$this->fee_seller,
                'login_method' => $this->login_method,
                'code_combination' => $this->code_combination,
                'length' => (int)$this->length,
                'prefix' => $this->prefix ?: '',
                'validity_days' => $this->validity_days,
                'notes' => $this->notes,
            ];

            $vouchers = $service->generateAdHocVouchers($attrs, (int)$this->quantity, (int)auth()->id());

            $this->generated_vouchers = $vouchers;
            session()->flash('success', sprintf(
                'Voucher berhasil di-generate (%d unit). Pool counter sinkron.',
                count($vouchers)
            ));
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal generate voucher: ' . $e->getMessage());
        }
    }

    protected function generateCode()
    {
        $prefix = $this->prefix ?: '';

        switch ($this->code_combination) {
            case 'uppercase':
                $random = strtoupper(Str::random($this->length));
                break;
            case 'lowercase':
                $random = strtolower(Str::random($this->length));
                break;
            case 'numbers':
                $random = '';
                for ($j = 0; $j < $this->length; $j++) {
                    $random .= mt_rand(0, 9);
                }
                break;
            case 'alphanumeric':
                $random = Str::random($this->length);
                break;
            case 'uppercase_alphanumeric':
            default:
                $random = strtoupper(Str::random($this->length));
                break;
        }

        return $prefix . $random;
    }

    public function resetForm()
    {
        $this->type = 'hotspot';
        $this->nas_device_id = null;
        $this->owner_id = null;
        $this->bind_on_login = false;
        $this->service_profile_id = null;
        $this->fee_seller = 0;
        $this->login_method = 'voucher_code';
        $this->quantity = 1;
        $this->length = 6;
        $this->prefix = null;
        $this->code_combination = 'uppercase_alphanumeric';
        $this->validity_days = null;
        $this->notes = null;
        $this->generated_vouchers = [];
    }

    public function render()
    {
        $nasDevices = NasDevice::active()->get();
        $owners = User::select('id', 'name', 'email')->get();
        $serviceProfiles = ServiceProfile::active()
            ->where(function($q) {
                $q->where('service_type', 'voucher')
                  ->orWhere('service_type', 'hotspot')
                  ->orWhere('service_type', 'combined');
            })
            ->get();
        
        return view('livewire.isp.voucher.create', compact('nasDevices', 'owners', 'serviceProfiles'));
    }
}
