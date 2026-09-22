<?php

namespace App\Livewire\ISP\Onu;

use App\Livewire\NOC\Onu\Index as BaseIndex;

class Index extends BaseIndex
{
    public function configure(): void
    {
    }

    public function mount($id = null): void
    {
        if (method_exists(get_parent_class($this), 'mount')) {
            parent::mount();
        }
        $this->activeModule = 'isp';
        $this->activePage   = 'onus';
    }

    public function deleteOnu(int $id): void
    {
        try {
            $onu = \App\Models\ISP\Onu::findOrFail($id);
            // Only allow deleting offline ONUs
            $isOffline = !$onu->last_seen_at || $onu->last_seen_at < now()->subMinutes(5);
            if (!$isOffline) {
                session()->flash('error', 'Hanya ONU yang offline yang bisa dihapus.');
                return;
            }
            $onu->delete();
            session()->flash('success', 'ONU ' . $onu->serial_number . ' berhasil dihapus.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menghapus ONU: ' . $e->getMessage());
        }
    }

    public function deleteAllOfflineOnu(): void
    {
        try {
            $staleAt = now()->subMinutes(5);
            $offlineOnus = \App\Models\ISP\Onu::where(function ($q) use ($staleAt) {
                $q->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $staleAt);
            })->get();

            $count = $offlineOnus->count();
            if ($count === 0) {
                session()->flash('error', 'Tidak ada ONU offline yang ditemukan.');
                return;
            }

            foreach ($offlineOnus as $onu) {
                $onu->delete();
            }
            
            session()->flash('success', $count . ' ONU offline berhasil dihapus.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menghapus masal ONU: ' . $e->getMessage());
        }
    }

    #[\Livewire\Attributes\Layout('layouts.app')]
    public function render()
    {
        return view('livewire.isp.onu.index-v2', [
            'onus'    => $this->onus,
            'olts'    => $this->olts,
            'summary' => $this->summary,
        ]);
    }
}

