<?php

namespace App\Livewire\ResellerPortal\Network;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\Support\Ticket;
use App\Services\ISP\OntLifecycleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class DismantleOnt extends AdminComponent
{
    use WithFileUploads;

    #[Url]
    public ?int $onu_id = null;

    #[Url]
    public ?int $customer_id = null;

    #[Url]
    public ?int $ticket_id = null;

    public bool $embedded = false;

    // Search and Selection
    public string $searchQuery = '';
    public array $searchResults = [];
    public ?int $selectedOnuId = null;

    // Selected Target Data
    public ?Onu $onu = null;
    public ?Customer $customer = null;
    public ?CustomerService $customerService = null;
    public ?Ticket $ticket = null;

    // Form inputs
    public string $dismantleReason = 'Berhenti Berlangganan (Permintaan Pelanggan)';
    public string $deviceCondition = 'good'; // 'good' (kembali ke stok saya), 'damaged' (rusak)
    public array $checklist = [
        'modem' => true,
        'adapter' => true,
        'patchcord' => true,
        'lan_cable' => false,
        'box' => false,
    ];
    public string $notes = '';
    public $photoProof = null;
    public string $digitalSignature = '';

    // State
    public bool $isProcessing = false;
    public bool $isCompleted = false;
    public ?string $completedSummary = null;
    public string $transaction_id = '';

    public function mount(?int $customer_id = null, ?int $onu_id = null, ?int $ticket_id = null, bool $embedded = false)
    {
        if ($customer_id) $this->customer_id = $customer_id;
        if ($onu_id) $this->onu_id = $onu_id;
        if ($ticket_id) $this->ticket_id = $ticket_id;
        $this->embedded = $embedded;

        parent::mount();
        $this->activeModule = 'reseller';
        $this->activePage = 'network-dismantle-ont';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.onus.index')],
            ['label' => 'Cabut ONT (Dismantle)', 'url' => '#'],
        ];

        $this->transaction_id = 'DISMANTLE-' . (string) Str::uuid();

        if ($this->onu_id) {
            $this->selectOnu($this->onu_id);
        } elseif ($this->customer_id) {
            $this->selectCustomer($this->customer_id);
        } elseif ($this->ticket_id) {
            $this->loadTicket($this->ticket_id);
        }
    }

    public function updatedSearchQuery()
    {
        $q = trim($this->searchQuery);
        if (strlen($q) < 2) {
            $this->searchResults = [];
            return;
        }

        try {
            $user = Auth::user();
            $effectiveResellerId = $user ? $user->getEffectiveResellerId() : null;

            $this->searchResults = Onu::withoutGlobalScopes()
                ->where(function ($sub) use ($q) {
                    $sub->where('serial_number', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('mac_address', 'like', "%{$q}%")
                        ->orWhereHas('customerService.customer', function ($cq) use ($q) {
                            $cq->withoutGlobalScopes()
                                ->where('name', 'like', "%{$q}%")
                                ->orWhere('phone', 'like', "%{$q}%")
                                ->orWhere('code', 'like', "%{$q}%");
                        });
                })
                ->when($effectiveResellerId, function ($sub) use ($effectiveResellerId) {
                    $sub->where(function ($rq) use ($effectiveResellerId) {
                        $rq->where('created_by', $effectiveResellerId)
                            ->orWhereHas('customerService.customer', fn($cq) => $cq->withoutGlobalScopes()->where('reseller_id', $effectiveResellerId));
                    });
                })
                ->whereIn('status', ['active', 'online', 'unregistered'])
                ->with(['customerService.customer', 'olt', 'odp'])
                ->limit(10)
                ->get()
                ->toArray();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('DismantleOnt search error: ' . $e->getMessage());
            $this->searchResults = [];
        }
    }

    public function selectOnu(int $onuId)
    {
        $this->selectedOnuId = $onuId;
        $this->onu = Onu::withoutGlobalScopes()
            ->with(['customerService.customer', 'olt', 'odp', 'vendor'])
            ->find($onuId);

        if ($this->onu) {
            $this->customerService = CustomerService::withoutGlobalScopes()
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()])
                ->where('onu_id', $this->onu->id)
                ->latest('id')
                ->first();

            $this->customer = $this->customerService?->customer
                ?? ($this->onu->customer ? $this->onu->customer : null);
        }

        $this->searchResults = [];
        $this->searchQuery = '';
    }

    public function selectCustomer(int $customerId)
    {
        $this->customer = Customer::withoutGlobalScopes()->find($customerId);
        if ($this->customer) {
            $this->customerService = CustomerService::withoutGlobalScopes()
                ->where('customer_id', $customerId)
                ->whereNotNull('onu_id')
                ->latest('id')
                ->first();

            if ($this->customerService?->onu_id) {
                $this->selectOnu($this->customerService->onu_id);
            }
        }
    }

    public function loadTicket(int $ticketId)
    {
        $this->ticket = Ticket::withoutGlobalScopes()->find($ticketId);
        if ($this->ticket && $this->ticket->customer_id) {
            $this->selectCustomer($this->ticket->customer_id);
        }
    }

    public function executeDismantle()
    {
        $this->validate([
            'selectedOnuId' => 'required',
            'dismantleReason' => 'required|string',
            'photoProof' => 'nullable|image|max:10240',
        ], [
            'selectedOnuId.required' => 'Pilih perangkat ONT yang akan ditarik.',
            'dismantleReason.required' => 'Pilih alasan penarikan perangkat.',
        ]);

        $user = Auth::user();
        $this->isProcessing = true;

        try {
            $sn = $this->onu->serial_number;
            $custName = $this->customer?->name ?? 'Pelanggan';
            $modelName = $this->onu->model ?: 'ONT GPON';
            $photoPath = $this->photoProof ? $this->photoProof->store('reseller/dismantle', 'public') : null;

            app(OntLifecycleService::class)->executeDismantle([
                'transaction_id' => $this->transaction_id ?: ('DISMANTLE-' . (string) Str::uuid()),
                'onu_id' => $this->onu->id,
                'serial_number' => $sn,
                'dismantle_reason' => $this->dismantleReason,
                'device_condition' => $this->deviceCondition,
                'checklist' => $this->checklist,
                'notes' => $this->notes,
                'customer_id' => $this->customer?->id,
                'ticket_id' => $this->ticket?->id,
                'photo_path' => $photoPath,
                'digital_signature' => $this->digitalSignature,
            ], $user);

            $this->onu = $this->onu->fresh();
            $this->completedSummary = "Perangkat {$modelName} (SN: {$sn}) berhasil dicabut dari {$custName}. "
                . ($this->deviceCondition === 'good' ? 'Unit telah ditambahkan (+1) ke Stok Tangan Anda.' : 'Unit dicatat sebagai afkir/rusak siap retur ke gudang pusat.');
            $this->isCompleted = true;

            session()->flash('success', 'Penarikan perangkat (Dismantle) berhasil diproses!');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses penarikan: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function resetForm()
    {
        $this->reset([
            'selectedOnuId', 'onu', 'customer', 'customerService', 'ticket',
            'notes', 'photoProof', 'digitalSignature', 'isCompleted', 'completedSummary'
        ]);
        $this->deviceCondition = 'good';
        $this->dismantleReason = 'Berhenti Berlangganan (Permintaan Pelanggan)';
        $this->transaction_id = 'DISMANTLE-' . (string) Str::uuid();
    }

    public function render()
    {
        return view('livewire.reseller-portal.network.dismantle-ont');
    }
}
