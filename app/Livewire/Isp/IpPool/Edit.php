<?php

namespace App\Livewire\ISP\IpPool;

use App\Livewire\AdminComponent;
use App\Models\ISP\IpPool;
use App\Models\ISP\Pop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Edit extends AdminComponent
{
    public ?int $poolId = null;

    public array $form = [
        'name' => '',
        'code' => '',
        'network' => '',
        'start_ip' => '',
        'end_ip' => '',
        'gateway' => '',
        'dns' => '',
        'pop_id' => null,
        'status' => 'active',
    ];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'ip-pools';

        $pool = IpPool::findOrFail($id);
        $this->poolId = $pool->id;
        $this->form = [
            'name' => (string)$pool->name,
            'code' => (string)$pool->code,
            'network' => (string)($pool->network ?? ''),
            'start_ip' => (string)($pool->start_ip ?? ''),
            'end_ip' => (string)($pool->end_ip ?? ''),
            'gateway' => (string)($pool->gateway ?? ''),
            'dns' => (string)($pool->dns_servers ?? '8.8.8.8,8.8.4.4'),
            'pop_id' => $pool->pop_id,
            'status' => (string)$pool->status,
        ];

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Paket & Layanan'],
            ['label' => 'IP Pool', 'url' => route('isp.ip-pools.index')],
            ['label' => 'Edit'],
        ];
    }

    protected function rules()
    {
        return [
            'form.name' => 'required|string|max:150',
            'form.code' => 'required|string|max:50|unique:ip_pools,code,' . $this->poolId,
            'form.network' => 'nullable|ipv4',
            'form.start_ip' => 'required|ipv4',
            'form.end_ip' => 'required|ipv4',
            'form.gateway' => 'required|ipv4',
            'form.dns' => 'nullable|string|max:150',
            'form.pop_id' => 'nullable|exists:pops,id',
            'form.status' => 'required|in:active,inactive',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            $user = Auth::user();
            $pool = IpPool::findOrFail($this->poolId);

            $totalIps = 0;
            if ($this->form['start_ip'] && $this->form['end_ip']) {
                $sLong = ip2long($this->form['start_ip']);
                $eLong = ip2long($this->form['end_ip']);
                if ($sLong !== false && $eLong !== false && $eLong >= $sLong) {
                    $totalIps = $eLong - $sLong + 1;
                }
            }

            $pool->update([
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
                'updated_by' => $user->id,
            ]);

            session()->flash('success', 'IP Pool berhasil diperbarui!');
            return redirect()->route('isp.ip-pools.index');
        } catch (Throwable $e) {
            Log::error('Update IP Pool Gagal', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $pops = Pop::active()->with('router')->get(['id', 'name', 'router_id']);

        return view('livewire.isp.ip-pool.edit', [
            'pops' => $pops,
        ])->layout('layouts.enterprise');
    }
}
