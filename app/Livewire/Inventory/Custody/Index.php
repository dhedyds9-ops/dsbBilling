<?php

namespace App\Livewire\Inventory\Custody;

use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryCustodyTransfer;
use App\Models\User;
use App\Services\Inventory\StockMutationService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'custody'; // 'custody' atau 'transfers'
    public string $search = '';
    public string $holderTypeFilter = ''; // reseller, technician, all
    public ?int $holderFilter = null;

    // Modal Serah Terima ke Teknisi (Handover - Metode A)
    public bool $showHandoverModal = false;
    public ?int $selectedCustodyId = null;
    public ?int $handover_to_user_id = null;
    public string $handover_notes = '';

    protected $queryString = [
        'activeTab' => ['except' => 'custody'],
        'search' => ['except' => ''],
        'holderTypeFilter' => ['except' => ''],
        'holderFilter' => ['except' => null],
    ];

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingHolderTypeFilter()
    {
        $this->resetPage();
    }

    public function openHandoverModal(int $custodyId)
    {
        $this->selectedCustodyId = $custodyId;
        $this->handover_to_user_id = null;
        $this->handover_notes = '';
        $this->showHandoverModal = true;
    }

    public function submitHandover(StockMutationService $service)
    {
        $this->validate([
            'handover_to_user_id' => 'required|exists:users,id',
            'handover_notes' => 'nullable|string|max:500',
        ], [
            'handover_to_user_id.required' => 'Teknisi penerima barang wajib dipilih.',
        ]);

        try {
            $transfer = $service->handoverCustody(
                custodyId: $this->selectedCustodyId,
                toUserId: $this->handover_to_user_id,
                toUserType: 'technician',
                notes: $this->handover_notes ?: 'Serah terima material ke teknisi pelaksana'
            );

            session()->flash('success', "Serah terima material No. {$transfer->transfer_number} telah dibuat dan menunggu konfirmasi penerimaan oleh Teknisi.");
            $this->showHandoverModal = false;
            $this->selectedCustodyId = null;
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses serah terima: ' . $e->getMessage());
        }
    }

    public function acceptTransfer(int $transferId, StockMutationService $service)
    {
        try {
            $service->acceptHandover($transferId, auth()->id());
            session()->flash('success', 'Konfirmasi penerimaan barang berhasil. Material kini resmi berada dalam penguasaan teknisi.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menerima serah terima: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();

        // 1. Query Custody (Stok di tangan)
        $custodyQuery = InventoryCustody::with(['item', 'itemSerial', 'holder', 'customer'])
            ->activeCustody()
            ->latest('id');

        if ($user && $user->isReseller()) {
            $custodyQuery->where('holder_id', $user->id);
        } elseif ($user && $user->isTechnician()) {
            $custodyQuery->where('holder_id', $user->id);
        } else {
            if (!empty($this->holderTypeFilter)) {
                $custodyQuery->where('holder_type', $this->holderTypeFilter);
            }
            if (!empty($this->holderFilter)) {
                $custodyQuery->where('holder_id', $this->holderFilter);
            }
        }

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $custodyQuery->where(function ($q) use ($s) {
                $q->whereHas('item', function ($iq) use ($s) {
                    $iq->where('name', 'like', $s)->orWhere('sku', 'like', $s);
                })->orWhereHas('itemSerial', function ($sq) use ($s) {
                    $sq->where('serial_number', 'like', $s);
                })->orWhereHas('holder', function ($hq) use ($s) {
                    $hq->where('name', 'like', $s);
                });
            });
        }

        $custodies = $custodyQuery->paginate(15);

        // 2. Query Transfers (Riwayat Serah Terima)
        $transferQuery = InventoryCustodyTransfer::with(['fromUser', 'toUser', 'item', 'itemSerial'])
            ->latest('id');

        if ($user && $user->isReseller()) {
            $transferQuery->where('from_user_id', $user->id);
        } elseif ($user && $user->isTechnician()) {
            $transferQuery->where('to_user_id', $user->id);
        }

        $transfers = $transferQuery->paginate(15);

        // List Teknisi untuk dropdown modal serah terima
        $technicians = User::getEligibleTechnicians();

        $selectedCustody = null;
        if ($this->selectedCustodyId) {
            $selectedCustody = InventoryCustody::with(['item', 'itemSerial', 'holder'])->find($this->selectedCustodyId);
        }

        return view('livewire.inventory.custody.index', [
            'custodies' => $custodies,
            'transfers' => $transfers,
            'technicians' => $technicians,
            'selectedCustody' => $selectedCustody,
        ])->layout('layouts.app', ['title' => 'Distribusi & Serah Terima Material - dsBilling']);
    }
}
