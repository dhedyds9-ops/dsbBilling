<?php

namespace App\Livewire\Isp\IpPool;

use App\Livewire\AdminComponent;
use App\Models\ISP\IpPool;
use App\Models\ISP\Pop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Create extends AdminComponent
{
    public array $form = [
        'name' => '',
        'code' => '',
        'network' => '',
        'start_ip' => '',
        'end_ip' => '',
        'gateway' => '',
        'dns' => '8.8.8.8,8.8.4.4',
        'pop_id' => null,
        'status' => 'active',
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'ip-pools';

        $this->form['code'] = strtoupper('POOL_' . substr(uniqid(), -6));

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Paket & Layanan'],
            ['label' => 'IP Pool', 'url' => route('isp.ip-pools.index')],
            ['label' => 'Buat Baru'],
        ];
    }

    public function updatedFormStartIp()
    {
        if ($this->form['start_ip'] && filter_var($this->form['start_ip'], FILTER_VALIDATE_IP)) {
            $parts = explode('.', $this->form['start_ip']);
            if (count($parts) === 4) {
                if (empty($this->form['network'])) {
                    $this->form['network'] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0';
                }
                if (empty($this->form['gateway'])) {
                    $this->form['gateway'] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.1';
                }
                if (empty($this->form['end_ip'])) {
                    $this->form['end_ip'] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.254';
                }
            }
        }
    }

    public function save()
    {
        $rules = [
            'form.name' => 'required|string|max:150',
            'form.code' => 'required|string|max:50|unique:ip_pools,code',
            'form.network' => 'nullable|ipv4',
            'form.start_ip' => 'required|ipv4',
            'form.end_ip' => 'required|ipv4',
            'form.gateway' => 'required|ipv4',
            'form.dns' => 'nullable|string|max:150',
            'form.pop_id' => 'nullable|exists:pops,id',
            'form.status' => 'required|in:active,inactive',
        ];
        $this->validate($rules);

        try {
            $user = Auth::user();

            $totalIps = 0;
            if ($this->form['start_ip'] && $this->form['end_ip']) {
                $sLong = ip2long($this->form['start_ip']);
                $eLong = ip2long($this->form['end_ip']);
                if ($sLong !== false && $eLong !== false && $eLong >= $sLong) {
                    $totalIps = $eLong - $sLong + 1;
                }
            }

            IpPool::create([
                'name' => $this->form['name'],
                'code' => $this->form['code'],
                'network' => $this->form['network'],
                'start_ip' => $this->form['start_ip'],
                'end_ip' => $this->form['end_ip'],
                'gateway' => $this->form['gateway'],
                'dns_servers' => $this->form['dns'],
                'pop_id' => $this->form['pop_id'] ?: null,
                'status' => $this->form['status'],
                'total_ips' => $totalIps,
                'used_ips' => 0,
                'created_by' => $user->id,
            ]);

            session()->flash('success', 'IP Pool berhasil dibuat!');
            return redirect()->route('isp.ip-pools.index');
        } catch (Throwable $e) {
            Log::error('Save IP Pool Gagal', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $pops = Pop::active()->with('router')->get(['id', 'name', 'router_id']);

        return view('livewire.isp.ip-pool.create', [
            'pops' => $pops,
        ])->layout('layouts.enterprise');
    }
}
