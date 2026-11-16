<?php

namespace App\Livewire\Isp\Technician\Dismantle;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\ACS\ACSDevice;
use App\Models\Support\Ticket;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithFileUploads;

    // URL Parameters
    #[Url]
    public ?int $onu_id = null;

    #[Url]
    public ?int $customer_id = null;

    #[Url]
    public ?int $ticket_id = null;

    // Search
    public string $searchQuery = '';
    public array $searchResults = [];

    // Selected Target Data
    public ?int $selectedOnuId = null;
    public ?Onu $onu = null;
    public ?Customer $customer = null;
    public ?CustomerService $customerService = null;
    public ?Ticket $ticket = null;

    // Form inputs
    public string $dismantleReason = 'Berhenti Berlangganan (Permintaan Pelanggan)';
    public string $deviceCondition = 'good'; // 'good' (kembali ke stok saya), 'damaged' (afkir/rusak)
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

    public function mount()
    {
        $this->transaction_id = 'DISMANTLE-' . (string) \Illuminate\Support\Str::uuid();

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

        $results = Onu::withoutGlobalScopes()
            ->with([
                'customerService' => fn($sq) => $sq->withoutGlobalScopes(),
                'customerService.customer' => fn($cq) => $cq->withoutGlobalScopes(),
                'customerService.serviceProfile' => fn($pq) => $pq->withoutGlobalScopes(),
                'customerService.pppoeUser' => fn($puq) => $puq->withoutGlobalScopes(),
                'acsDevice' => fn($aq) => $aq->withoutGlobalScopes(),
            ])
            ->where(function ($sub) use ($q) {
                $sub->where('serial_number', 'like', "%{$q}%")
                    ->orWhere('mac_address', 'like', "%{$q}%")
                    ->orWhereHas('customerService.customer', function ($cq) use ($q) {
                        $cq->where('name', 'like', "%{$q}%")
                           ->orWhere('phone', 'like', "%{$q}%");
                    })
                    ->orWhereHas('customerService.pppoeUser', function ($pu) use ($q) {
                        $pu->where('username', 'like', "%{$q}%");
                    });
            })
            ->limit(8)
            ->get();

        $this->searchResults = $results->map(function ($o) {
            $cs = $o->customerService;
            if (!$cs && $o->acsDevice?->customer_service_id) {
                $cs = CustomerService::withoutGlobalScopes()
                    ->with(['customer' => fn($q) => $q->withoutGlobalScopes(), 'serviceProfile' => fn($q) => $q->withoutGlobalScopes(), 'pppoeUser' => fn($q) => $q->withoutGlobalScopes()])
                    ->find($o->acsDevice->customer_service_id);
            }
            $cust = $cs?->customer ?? ($o->customer_id ? Customer::withoutGlobalScopes()->find($o->customer_id) : null);
            return [
                'onu_id' => $o->id,
                'serial_number' => $o->serial_number,
                'model' => $o->model ?: 'ONT',
                'customer_name' => $cust?->name ?? 'Belum Terikat Pelanggan',
                'customer_code' => $cust?->customer_code ?? ($cust?->code ?? ('ID #' . $cust?->id)),
                'pppoe_username' => $cs?->pppoeUser?->username ?? '-',
                'package' => $cs?->serviceProfile?->name ?? 'Layanan',
                'status' => $o->status,
            ];
        })->toArray();
    }

    public function loadTicket(int $ticketId)
    {
        $this->ticket = Ticket::withoutGlobalScopes()->with(['customer.customerServices.onu'])->find($ticketId);
        if ($this->ticket && $this->ticket->customer_id) {
            $this->selectCustomer($this->ticket->customer_id);
        }
    }

    public function selectCustomer(int $customerId)
    {
        $this->customer = Customer::withoutGlobalScopes()->find($customerId);
        if (!$this->customer) return;

        $cs = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->whereNotNull('onu_id')
            ->latest('id')
            ->first();

        if ($cs && $cs->onu_id) {
            $this->selectOnu($cs->onu_id);
        }
    }

    public function selectOnu(int $onuId)
    {
        $this->selectedOnuId = $onuId;
        $this->onu = Onu::withoutGlobalScopes()
            ->with(['customerService.customer', 'customerService.serviceProfile', 'customerService.pppoeUser', 'olt', 'odp', 'vendor'])
            ->find($onuId);

        if ($this->onu) {
            $this->customerService = CustomerService::withoutGlobalScopes()
                ->with([
                    'customer' => fn($q) => $q->withoutGlobalScopes(),
                    'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                    'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                ])
                ->where('onu_id', $this->onu->id)
                ->latest('id')
                ->first();

            $this->customer = $this->customerService?->customer
                ?? ($this->onu->customer_id ? Customer::withoutGlobalScopes()->find($this->onu->customer_id) : null);
        }

        $this->searchResults = [];
        $this->searchQuery = '';
    }

    public function executeDismantle()
    {
        $this->validate([
            'selectedOnuId' => 'required',
            'dismantleReason' => 'required|string',
            'photoProof' => 'nullable|image|max:10240',
        ], [
            'selectedOnuId.required' => 'Pilih perangkat ONT yang akan dibongkar.',
            'dismantleReason.required' => 'Pilih alasan pembongkaran perangkat.',
        ]);

        $user = Auth::user();
        $this->isProcessing = true;

        try {
            $sn = $this->onu->serial_number;
            $custName = $this->customer?->name ?? 'Pelanggan';
            $modelName = $this->onu->model ?: 'ONT GPON';
            $photoPath = $this->photoProof ? $this->photoProof->store('technician/dismantle', 'public') : null;

            app(\App\Services\ISP\OntLifecycleService::class)->executeDismantle([
                'transaction_id' => $this->transaction_id ?: ('DISMANTLE-' . (string) \Illuminate\Support\Str::uuid()),
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
                . ($this->deviceCondition === 'good' ? "Unit telah ditambahkan (+1) ke Stok Saya untuk pemasangan baru." : "Unit dicatat sebagai afkir.");
            $this->isCompleted = true;

            session()->flash('success', 'Pembongkaran perangkat berhasil diproses dan dicatat!');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memproses pembongkaran: ' . $e->getMessage());
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
        $this->transaction_id = 'DISMANTLE-' . (string) \Illuminate\Support\Str::uuid();
    }

    public function render()
    {
        return view('livewire.isp.technician.dismantle.index');
    }
}
