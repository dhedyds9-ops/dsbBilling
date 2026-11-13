<?php

namespace App\Livewire\Inventory\Usage;

use App\Models\CRM\Customer;
use App\Models\Inventory\InventoryCustody;
use App\Models\Inventory\InventoryUsage;
use App\Models\User;
use App\Services\Inventory\StockMutationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $workOrderFilter = '';
    public ?int $technicianFilter = null;
    public string|int $perPage = 20;

    // Modal Form Catat Pemakaian
    public bool $showCreateModal = false;
    public ?int $technician_id = null;
    public ?int $customer_id = null;
    public string $work_order_type = 'psb';
    public string $usage_date = '';
    public string $optical_power = '';
    public string $notes = '';
    public array $usageItems = [];

    // Modal Detail
    public bool $showDetailModal = false;
    public ?int $selectedUsageId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'workOrderFilter' => ['except' => ''],
        'technicianFilter' => ['except' => null],
        'perPage' => ['except' => 20],
    ];

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->usage_date = date('Y-m-d');
        $user = auth()->user();
        if ($user && $user->isTechnician()) {
            $this->technician_id = $user->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedTechnicianId()
    {
        $this->usageItems = [];
        $this->addItemRow();
    }

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $user = auth()->user();
        if ($user && $user->isTechnician()) {
            $this->technician_id = $user->id;
        } else {
            $firstTech = User::getEligibleTechnicians()->first();
            $this->technician_id = $firstTech?->id;
        }
        $this->addItemRow();
        $this->showCreateModal = true;
    }

    public function resetCreateForm()
    {
        $this->customer_id = null;
        $this->work_order_type = 'psb';
        $this->usage_date = date('Y-m-d');
        $this->optical_power = '';
        $this->notes = '';
        $this->usageItems = [];
        $this->resetErrorBag();
    }

    public function addItemRow()
    {
        $this->usageItems[] = [
            'custody_id' => '',
            'item_id' => '',
            'item_serial_id' => null,
            'quantity' => 1,
            'unit' => 'unit',
            'max_available' => 1,
            'notes' => '',
        ];
    }

    public function removeItemRow(int $index)
    {
        if (isset($this->usageItems[$index])) {
            unset($this->usageItems[$index]);
            $this->usageItems = array_values($this->usageItems);
        }
        if (empty($this->usageItems)) {
            $this->addItemRow();
        }
    }

    public function updatedUsageItems($value, $key)
    {
        if (str_ends_with($key, '.custody_id')) {
            $index = explode('.', $key)[0];
            $custodyId = (int) $value;
            if ($custodyId) {
                $custody = InventoryCustody::with(['item', 'itemSerial'])->find($custodyId);
                if ($custody) {
                    $this->usageItems[$index]['item_id'] = $custody->item_id;
                    $this->usageItems[$index]['item_serial_id'] = $custody->item_serial_id;
                    $this->usageItems[$index]['unit'] = $custody->item?->unit ?? 'unit';
                    $this->usageItems[$index]['max_available'] = $custody->available_quantity;
                    $this->usageItems[$index]['quantity'] = $custody->itemSerial ? 1 : min(1, $custody->available_quantity);
                }
            }
        }
    }

    public function saveUsage(StockMutationService $service)
    {
        $this->validate([
            'technician_id' => 'required|exists:users,id',
            'customer_id' => 'nullable|exists:customers,id',
            'work_order_type' => 'required|string',
            'usage_date' => 'required|date',
            'optical_power' => 'nullable|string|max:30',
            'usageItems' => 'required|array|min:1',
            'usageItems.*.item_id' => 'required|exists:items,id',
            'usageItems.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'technician_id.required' => 'Teknisi pelapor wajib dipilih.',
            'usageItems.required' => 'Minimal 1 barang pemakaian harus dilaporkan.',
            'usageItems.*.item_id.required' => 'Barang pada setiap baris wajib dipilih.',
            'usageItems.*.quantity.min' => 'Kuantitas pemakaian harus lebih besar dari 0.',
        ]);

        $tech = User::findOrFail($this->technician_id);

        try {
            DB::transaction(function () use ($tech, $service) {
                $usage = InventoryUsage::create([
                    'technician_id' => $tech->id,
                    'customer_id' => $this->customer_id,
                    'work_order_type' => $this->work_order_type,
                    'usage_date' => $this->usage_date,
                    'optical_power' => $this->optical_power ?: null,
                    'notes' => $this->notes ?: null,
                    'branch_id' => $tech->branch_id,
                ]);

                $service->recordUsage($usage, $this->usageItems, auth()->id());
            });

            session()->flash('success', 'Laporan pemakaian material aktual berhasil dicatat. Status Serial Number telah terpasang ke pelanggan.');
            $this->showCreateModal = false;
            $this->resetCreateForm();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menyimpan laporan pemakaian: ' . $e->getMessage());
        }
    }

    public function showDetail(int $id)
    {
        $this->selectedUsageId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedUsageId = null;
    }

    public function render()
    {
        $user = auth()->user();

        $query = InventoryUsage::with(['technician', 'customer', 'items.item', 'items.itemSerial'])
            ->latest('id');

        if ($user && $user->isTechnician()) {
            $query->where('technician_id', $user->id);
        } elseif (!empty($this->technicianFilter)) {
            $query->where('technician_id', $this->technicianFilter);
        }

        if (!empty($this->workOrderFilter)) {
            $query->where('work_order_type', $this->workOrderFilter);
        }

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('usage_number', 'like', $s)
                    ->orWhere('notes', 'like', $s)
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('name', 'like', $s);
                    });
            });
        }

        $perPageInt = match ((string) $this->perPage) {
            '50' => 50,
            '100' => 100,
            'all' => max(1, (clone $query)->count()),
            default => 20,
        };

        $usages = $query->paginate($perPageInt);

        $technicians = User::getEligibleTechnicians();

        // Pelanggan langganan riil (eksklusifkan voucher guest)
        $customers = Customer::query()
            ->whereRaw("LOWER(COALESCE(name, '')) NOT LIKE 'guest%'")
            ->whereRaw("LOWER(COALESCE(code, '')) NOT LIKE 'gst-%'")
            ->where('status', '!=', 'inactive')
            ->with(['customerServices.serviceProfile'])
            ->orderBy('name')
            ->limit(100)
            ->get();

        // Material yang saat ini dipegang oleh teknisi yang dipilih di form modal
        $availableCustodies = collect();
        if ($this->technician_id) {
            $availableCustodies = InventoryCustody::with(['item', 'itemSerial'])
                ->where('holder_id', $this->technician_id)
                ->activeCustody()
                ->get();
        }

        $selectedUsage = null;
        if ($this->selectedUsageId) {
            $selectedUsage = InventoryUsage::with(['technician', 'customer', 'items.item', 'items.itemSerial'])->find($this->selectedUsageId);
        }

        return view('livewire.inventory.usage.index', [
            'usages' => $usages,
            'technicians' => $technicians,
            'customers' => $customers,
            'availableCustodies' => $availableCustodies,
            'selectedUsage' => $selectedUsage,
            'showCreateModal' => $this->showCreateModal,
            'showDetailModal' => $this->showDetailModal,
            'usageItems' => $this->usageItems,
        ])->layout('layouts.app', ['title' => 'Pemakaian Material Lapangan - dsBilling']);
    }
}
