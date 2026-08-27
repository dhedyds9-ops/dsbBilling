<?php

namespace App\Livewire\ProfilePaket;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\ServiceProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Bandwidth extends BaseNetworkComponent
{
    public bool $showFormModal = false;
    public ?int $editingId = null;

    public bool $autoBurst = true;
    public float $burstRatio = 1.5;
    public int $burstTime = 8;
    public float $thresholdPercent = 70;

    public array $form = [
        'name' => '',
        'download_max' => 10,
        'upload_max' => 2,
        'unit' => 'Mbps',
        'burst_limit_download' => null,
        'burst_limit_upload' => null,
        'burst_threshold_download' => null,
        'burst_threshold_upload' => null,
        'burst_time_download' => 8,
        'burst_time_upload' => 8,
        'description' => '',
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'profile-paket';
        $this->activePage = 'bandwidth';
        $this->filters = ['unit' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Profile Paket'],
            ['label' => 'Bandwidth'],
        ];
    }

    public function updatedAutoBurst()
    {
        if ($this->autoBurst) {
            $this->calculateBurst();
        }
    }

    public function updatedFormDownloadMax()
    {
        if ($this->autoBurst) $this->calculateBurst();
    }

    public function updatedFormUploadMax()
    {
        if ($this->autoBurst) $this->calculateBurst();
    }

    public function updatedBurstRatio()
    {
        if ($this->autoBurst) $this->calculateBurst();
    }

    public function updatedThresholdPercent()
    {
        if ($this->autoBurst) $this->calculateBurst();
    }

    public function updatedBurstTime()
    {
        if ($this->autoBurst) {
            $this->form['burst_time_download'] = $this->burstTime;
            $this->form['burst_time_upload'] = $this->burstTime;
        }
    }

    public function calculateBurst()
    {
        $dl = (float)$this->form['download_max'];
        $ul = (float)$this->form['upload_max'];

        $this->form['burst_limit_download']  = round($dl * $this->burstRatio, 2);
        $this->form['burst_limit_upload']    = round($ul * $this->burstRatio, 2);
        $this->form['burst_threshold_download'] = round($dl * ($this->thresholdPercent / 100), 2);
        $this->form['burst_threshold_upload']   = round($ul * ($this->thresholdPercent / 100), 2);
        $this->form['burst_time_download'] = $this->burstTime;
        $this->form['burst_time_upload'] = $this->burstTime;
    }

    public function openCreateModal()
    {
        $this->editingId = null;
        $this->autoBurst = true;
        $this->burstRatio = 1.5;
        $this->burstTime = 8;
        $this->thresholdPercent = 70;
        $this->form = [
            'name' => '',
            'download_max' => 10,
            'upload_max' => 2,
            'unit' => 'Mbps',
            'burst_limit_download' => 15,
            'burst_limit_upload' => 3,
            'burst_threshold_download' => 7,
            'burst_threshold_upload' => 1.4,
            'burst_time_download' => 8,
            'burst_time_upload' => 8,
            'description' => '',
        ];
        $this->calculateBurst();
        $this->showFormModal = true;
    }

    public function openEditModal($id)
    {
        $bw = ServiceProfile::findOrFail($id);
        $this->editingId = $id;

        preg_match('/([0-9.]+)\s*([a-zA-Z]+)/i', $bw->download_speed ?? '0 Mbps', $dlMatch);
        preg_match('/([0-9.]+)\s*([a-zA-Z]+)/i', $bw->upload_speed ?? '0 Mbps', $ulMatch);

        $this->form = [
            'name' => $bw->name,
            'download_max' => (float)($dlMatch[1] ?? 0),
            'upload_max'   => (float)($ulMatch[1] ?? 0),
            'unit'         => $dlMatch[2] ?? 'Mbps',
            'burst_limit_download'  => (float)($bw->burst_limit_download ?? round(($dlMatch[1] ?? 0) * 1.5, 2)),
            'burst_limit_upload'    => (float)($bw->burst_limit_upload ?? round(($ulMatch[1] ?? 0) * 1.5, 2)),
            'burst_threshold_download' => (float)($bw->burst_threshold_download ?? round(($dlMatch[1] ?? 0) * 0.7, 2)),
            'burst_threshold_upload'   => (float)($bw->burst_threshold_upload ?? round(($ulMatch[1] ?? 0) * 0.7, 2)),
            'burst_time_download' => (int)($bw->burst_time_download ?? 8),
            'burst_time_upload' => (int)($bw->burst_time_upload ?? 8),
            'description' => (string)$bw->description,
        ];
        $this->showFormModal = true;
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
    }

    public function save()
    {
        $this->validate([
            'form.name' => 'required|string|max:100',
            'form.download_max' => 'required|numeric|min:0.01',
            'form.upload_max' => 'required|numeric|min:0.01',
            'form.unit' => 'required|in:Mbps,Kbps,Gbps',
        ]);

        try {
            $payload = [
                'name' => $this->form['name'],
                'download_speed' => "{$this->form['download_max']} {$this->form['unit']}",
                'upload_speed'   => "{$this->form['upload_max']} {$this->form['unit']}",
                'description'    => $this->form['description'] ?? '',
                'burst_limit_download'     => $this->form['burst_limit_download'],
                'burst_limit_upload'       => $this->form['burst_limit_upload'],
                'burst_threshold_download' => $this->form['burst_threshold_download'],
                'burst_threshold_upload'   => $this->form['burst_threshold_upload'],
                'burst_time_download'       => (int)$this->form['burst_time_download'],
                'burst_time_upload'       => (int)$this->form['burst_time_upload'],
            ];

            $user = Auth::user();
            if ($this->editingId) {
                $sp = ServiceProfile::findOrFail($this->editingId);
                $payload['updated_by'] = $user->id;
                $sp->update($payload);
                session()->flash('success', 'Bandwidth Profile diperbarui!');
            } else {
                $payload['service_type'] = 'pppoe';
                $payload['status'] = 'active';
                $payload['code'] = 'BW' . strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $this->form['name']), 0, 6)) . now()->format('dm');
                $payload['created_by'] = $user->id;
                $payload['tenant_id'] = $user->tenant_id ?? null;
                $payload['base_price'] = 0;
                $payload['reseller_price'] = 0;
                $payload['owner_price'] = 0;
                ServiceProfile::create($payload);
                session()->flash('success', 'Bandwidth Profile dibuat! Burst limit & threshold AUTO-GENERATE: OK');
            }

            $this->closeFormModal();
        } catch (Throwable $e) {
            Log::error('Bandwidth Save Error', ['msg' => $e->getMessage()]);
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            ServiceProfile::findOrFail($id)->delete();
            session()->flash('success', 'Bandwidth Profile dihapus.');
        } catch (Throwable $e) {
            session()->flash('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = ServiceProfile::with('owner')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('download_speed', 'like', "%{$this->search}%")
                  ->orWhere('upload_speed', 'like', "%{$this->search}%")
            )
            ->when($this->filters['unit'] ?? null, function ($q, $unit) {
                $q->where(function ($sq) use ($unit) {
                    $sq->where('download_speed', 'like', "%{$unit}%")
                       ->orWhere('upload_speed', 'like', "%{$unit}%");
                });
            });

        $stats = [
            'total'    => (clone $query)->count(),
            'avg_dl'   => 0,
            'avg_ul'   => 0,
            'with_burst' => (clone $query)->whereNotNull('burst_limit_download')->count(),
        ];

        $allProfiles = (clone $query)->get();
        if ($allProfiles->count()) {
            $totalDl = 0; $totalUl = 0; $counted = 0;
            foreach ($allProfiles as $p) {
                preg_match('/([0-9.]+)\s*Mbps/i', $p->download_speed ?? '', $m1);
                preg_match('/([0-9.]+)\s*Mbps/i', $p->upload_speed ?? '', $m2);
                if (isset($m1[1]) && isset($m2[1])) {
                    $totalDl += (float)$m1[1];
                    $totalUl += (float)$m2[1];
                    $counted++;
                }
            }
            $stats['avg_dl'] = $counted ? round($totalDl / $counted, 2) : 0;
            $stats['avg_ul'] = $counted ? round($totalUl / $counted, 2) : 0;
        }

        $bandwidths = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.profile-paket.bandwidth', compact('bandwidths', 'stats'));
    }
}
