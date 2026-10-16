<?php

namespace App\Livewire\Isp\Onu;

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
            $sn = $onu->serial_number ?: $onu->id;
            $onu->delete();
            session()->flash('success', 'ONU ' . $sn . ' berhasil dihapus dari database.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menghapus ONU: ' . $e->getMessage());
        }
    }

    public function cleanupDuplicates(): void
    {
        try {
            $duplicates = \App\Models\ISP\Onu::select('serial_number')
                ->whereNotNull('serial_number')
                ->where('serial_number', '!=', '')
                ->groupBy('serial_number')
                ->havingRaw('count(*) > 1')
                ->pluck('serial_number');

            $deletedCount = 0;
            foreach ($duplicates as $sn) {
                $records = \App\Models\ISP\Onu::where('serial_number', $sn)
                    ->with('customerService')
                    ->get();
                
                // Prioritize keeping the record that has a customer or PON port or signal
                $sorted = $records->sortByDesc(function ($o) {
                    $score = 0;
                    if ($o->customerService) $score += 100;
                    if ($o->pon_port) $score += 50;
                    if ($o->rx_power_dbm !== null) $score += 20;
                    if ($o->last_seen_at !== null) $score += 10;
                    return $score;
                });

                // Keep the top 1, delete all remaining duplicate rows
                $keep = $sorted->first();
                foreach ($sorted->slice(1) as $orphan) {
                    $orphan->delete();
                    $deletedCount++;
                }
            }

            if ($deletedCount > 0) {
                session()->flash('success', "Berhasil membersihkan {$deletedCount} data ONU duplikat!");
            } else {
                session()->flash('info', 'Tidak ditemukan data ONU duplikat.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal membersihkan duplikat: ' . $e->getMessage());
        }
    }

    public function deleteAllOfflineOnu(): void
    {
        try {
            $offlineOnus = \App\Models\ISP\Onu::where(function ($q) {
                $q->where('status', 'inactive')
                  ->orWhere(function ($sq) {
                      $sq->whereNull('rx_power_dbm')->whereNull('pon_port');
                  });
            })->get();

            $count = $offlineOnus->count();
            if ($count === 0) {
                session()->flash('error', 'Tidak ada ONU offline yang ditemukan.');
                return;
            }

            foreach ($offlineOnus as $onu) {
                $onu->delete();
            }
            
            session()->flash('success', $count . ' ONU offline / orphan berhasil dihapus.');
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
            'ponPorts' => $this->ponPorts,
            'summary' => $this->summary,
        ]);
    }
}


