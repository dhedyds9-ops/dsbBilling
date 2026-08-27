<?php

namespace App\Livewire\ProfilePaket;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\ServiceProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProfileHotspot extends BaseNetworkComponent
{
    public string $activeTab = 'member';

    public bool $showCreateModal = false;
    public ?int $editingId = null;

    public array $form = [
        'name' => '',
        'price' => 0,
        'validity_unit' => 'days',
        'validity_value' => 30,
        'quota_gb' => null,
        'time_limit_hours' => null,
        'shared_users' => 1,
        'download_max' => 10,
        'upload_max' => 2,
        'auto_voucher_code_prefix' => 'HOT-',
        'description' => '',
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'profile-paket';
        $this->activePage = 'profile-hotspot';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Profile Paket'],
            ['label' => 'Profile Hotspot'],
        ];
    }

    public function setTab(string $tab)
    {
        if (!in_array($tab, ['member', 'voucher'])) return;
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function getTabServiceType(): string
    {
        return $this->activeTab === 'member' ? 'hotspot' : 'voucher';
    }

    public function openCreateModal()
    {
        $isMember = $this->activeTab === 'member';
        $this->editingId = null;
        $this->form = [
            'name' => $isMember ? 'Hotspot Member ' : 'Voucher ',
            'price' => $isMember ? 150000 : 5000,
            'validity_unit' => $isMember ? 'days' : 'hours',
            'validity_value' => $isMember ? 30 : 6,
            'quota_gb' => $isMember ? null : 2,
            'time_limit_hours' => $isMember ? null : 6,
            'shared_users' => $isMember ? 1 : 1,
            'download_max' => $isMember ? 10 : 5,
            'upload_max' => $isMember ? 2 : 1,
            'auto_voucher_code_prefix' => $isMember ? 'MBR-' : 'VCH-',
            'description' => '',
        ];
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $p = ServiceProfile::findOrFail($id);
        $this->editingId = $id;
        $this->activeTab = ($p->service_type === 'voucher') ? 'voucher' : 'member';

        preg_match('/([0-9.]+)\s*Mbps/i', $p->download_speed ?? '10 Mbps', $dl);
        preg_match('/([0-9.]+)\s*Mbps/i', $p->upload_speed ?? '2 Mbps', $ul);

        $validity = (int)($p->validity_value ?? 30);
        $unit = $p->validity_unit ?? ($this->activeTab === 'member' ? 'days' : 'hours');
        if ($unit === 'days' && $validity <= 0) { $validity = 30; }
        if ($unit === 'hours' && $validity <= 0) { $validity = 6; }

        $this->form = [
            'name' => $p->name,
            'price' => (int)($p->base_price ?? 0),
            'validity_unit' => $unit,
            'validity_value' => $validity,
            'quota_gb' => $p->quota_gb,
            'time_limit_hours' => $p->time_limit_hours,
            'shared_users' => (int)($p->shared_users ?? 1),
            'download_max' => (float)($dl[1] ?? 10),
            'upload_max'   => (float)($ul[1] ?? 2),
            'auto_voucher_code_prefix' => $p->prefix_code ?? ($this->activeTab === 'member' ? 'MBR-' : 'VCH-'),
            'description' => (string)$p->description,
        ];
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function save()
    {
        $svcType = $this->getTabServiceType();

        $this->validate([
            'form.name' => 'required|string|max:100',
            'form.price' => 'required|numeric|min:0',
            'form.validity_unit' => 'required|in:hours,days,months',
            'form.validity_value' => 'required|integer|min:1',
            'form.download_max' => 'required|numeric|min:0.01',
            'form.upload_max' => 'required|numeric|min:0.01',
            'form.shared_users' => 'required|integer|min:1',
        ]);

        try {
            $payload = [
                'name' => $this->form['name'],
                'service_type' => $svcType,
                'base_price' => (int)$this->form['price'],
                'reseller_price' => (int)($this->form['price'] * 0.85),
                'owner_price' => (int)($this->form['price'] * 0.70),
                'validity_unit' => $this->form['validity_unit'],
                'validity_value' => (int)$this->form['validity_value'],
                'quota_gb' => $this->form['quota_gb'] ?: null,
                'time_limit_hours' => $this->form['time_limit_hours'] ?: null,
                'shared_users' => (int)$this->form['shared_users'],
                'download_speed' => "{$this->form['download_max']} Mbps",
                'upload_speed'   => "{$this->form['upload_max']} Mbps",
                'description'    => $this->form['description'] ?? '',
                'prefix_code'    => $this->form['auto_voucher_code_prefix'],
            ];

            $user = Auth::user();
            if ($this->editingId) {
                ServiceProfile::findOrFail($this->editingId)->update($payload);
                session()->flash('success', 'Profile Hotspot diperbarui!');
            } else {
                $payload['status'] = 'active';
                $payload['code'] = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $this->form['name']), 0, 5)) . now()->format('dmy');
                $payload['created_by'] = $user->id;
                $payload['tenant_id'] = $user->tenant_id ?? null;
                ServiceProfile::create($payload);
                $label = $svcType === 'voucher' ? 'Voucher Sekali Pakai' : 'Member Hotspot Bulanan';
                session()->flash('success', "Profile {$label} dibuat!");
            }

            $this->closeCreateModal();
        } catch (Throwable $e) {
            Log::error('Profile Hotspot Save Error', ['msg' => $e->getMessage()]);
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $p = ServiceProfile::findOrFail($id);
            $p->status = $p->status === 'active' ? 'inactive' : 'active';
            $p->save();
            session()->flash('success', 'Status diperbarui.');
        } catch (Throwable $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        try {
            $p = ServiceProfile::findOrFail($id);
            $new = $p->replicate();
            $new->name = $p->name . ' (Copy)';
            $new->status = 'inactive';
            $new->code = $p->code . 'CP';
            $new->created_by = Auth::id();
            $new->save();
            session()->flash('success', 'Profile diduplikasi!');
        } catch (Throwable $e) {
            session()->flash('error', 'Gagal duplikasi: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            ServiceProfile::findOrFail($id)->delete();
            session()->flash('success', 'Profile dihapus.');
        } catch (Throwable $e) {
            session()->flash('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $serviceType = $this->getTabServiceType();

        $query = ServiceProfile::with('owner')
            ->where('service_type', $serviceType)
            ->when($this->search, fn($q) =>
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                       ->orWhere('prefix_code', 'like', "%{$this->search}%");
                })
            )
            ->when($this->filters['status'] ?? null, fn($q, $v) => $q->where('status', $v));

        $stats = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'total_revenue_monthly_est' => 0,
            'avg_price' => 0,
        ];

        $all = (clone $query)->get();
        $stats['total_revenue_monthly_est'] = (int)$all->sum(function ($p) {
            $price = (int)($p->base_price ?? 0);
            if ($p->validity_unit === 'hours') {
                return $price * (($p->validity_value ?? 6) >= 24 ? 30 : 120);
            } elseif ($p->validity_unit === 'days') {
                return $price * (30 / max(1, (int)($p->validity_value ?? 30)));
            }
            return $price;
        });
        $stats['avg_price'] = $stats['total'] ? (int)($all->avg('base_price') ?? 0) : 0;

        $profiles = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.profile-paket.profile-hotspot', compact('profiles', 'stats'));
    }
}
