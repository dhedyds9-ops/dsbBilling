<?php

namespace App\Livewire\ResellerPortal\Network\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Services\Auth\ResellerScopeHelper;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public int $perPage = 10;

    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public function mount()
    {
        parent::mount();
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.map')],
            ['label' => 'Data OLT', 'url' => '#'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function rowSync(int $id): void
    {
        try {
            $olt = Olt::forReseller()->findOrFail($id);
            $service = app(\App\Services\ISP\OltPollingService::class);
            $res = $service->pollOlt($olt);

            if (isset($res['success']) && !$res['success']) {
                $errMsg = $res['error'] ?? $res['message'] ?? 'Gagal menghubungi perangkat OLT.';
                throw new \Exception($errMsg);
            }

            $online = (int)($res['onu_online'] ?? 0);
            $offline = (int)($res['onu_offline'] ?? 0);
            $total = $online + $offline;
            $msg = "Sinkronisasi OLT {$olt->name} selesai: {$online} ONU Online, {$offline} Offline ({$total} Total).";
            session()->flash('success', $msg);
            $this->dispatch('toast', type: 'success', message: $msg);
        } catch (\Throwable $e) {
            $errMsg = $e->getMessage();
            session()->flash('error', 'Gagal sinkronisasi OLT: ' . $errMsg);
            $this->dispatch('toast', type: 'error', message: 'Gagal sync OLT: ' . $errMsg);
        }
    }

    public function syncAll(): void
    {
        try {
            $olts = Olt::forReseller()->whereNotNull('ip_address')->get();
            if ($olts->isEmpty()) {
                session()->flash('info', 'Tidak ada OLT yang memiliki IP Address pada cakupan Anda.');
                return;
            }

            $service = app(\App\Services\ISP\OltPollingService::class);
            $successCount = 0;
            $failMessages = [];

            foreach ($olts as $olt) {
                $res = $service->pollOlt($olt);
                if (!empty($res['success'])) {
                    $successCount++;
                } else {
                    $err = $res['error'] ?? $res['message'] ?? 'Unknown error';
                    $failMessages[] = "{$olt->name}: {$err}";
                }
            }

            if (empty($failMessages)) {
                $msg = "Sinkronisasi berhasil untuk {$successCount} OLT.";
                session()->flash('success', $msg);
                $this->dispatch('toast', type: 'success', message: $msg);
            } else {
                $summaryMsg = "Hasil: {$successCount} OLT sukses. Kendala: " . implode(' | ', $failMessages);
                session()->flash('error', $summaryMsg);
                $this->dispatch('toast', type: 'error', message: $summaryMsg);
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Sync error: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Sync error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $olts = Olt::forReseller()
            ->with(['pop', 'vendor', 'branch'])
            ->withCount([
                'onus as onus_total_count' => fn ($q) => $q->forReseller(),
                'onus as onus_online_count' => fn ($q) => $q->forReseller()->where('status', 'active'),
                'onus as onus_offline_count' => fn ($q) => $q->forReseller()->where('status', 'inactive'),
            ])
            ->when(trim($this->search) !== '', function ($q) {
                $like = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('model', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('address', 'like', $like);
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when(
                in_array($this->sortField, ['name', 'code', 'ip_address', 'status'], true),
                fn ($q) => $q->orderBy($this->sortField, $this->sortDirection),
                fn ($q) => $q->orderBy('name', 'asc')
            )
            ->paginate($this->perPage);

        $totalOlt = Olt::forReseller()->count();
        $activeOlt = Olt::forReseller()->where('status', 'active')->count();
        $totalOnu = Onu::forReseller()->count();
        $onlineOnu = Onu::forReseller()->where('status', 'active')->count();
        $offlineOnu = max(0, $totalOnu - $onlineOnu);
        $onlinePercentage = $totalOnu > 0 ? round(($onlineOnu / $totalOnu) * 100, 1) : 0;

        $stats = [
            'total_olt'         => $totalOlt,
            'active_olt'        => $activeOlt,
            'total_onu'         => $totalOnu,
            'online_onu'        => $onlineOnu,
            'offline_onu'       => $offlineOnu,
            'online_percentage' => $onlinePercentage,
        ];

        return view('livewire.reseller-portal.network.olt.index', [
            'olts'      => $olts,
            'stats'     => $stats,
            'territory' => ResellerScopeHelper::describeTerritory(),
        ]);
    }
}
