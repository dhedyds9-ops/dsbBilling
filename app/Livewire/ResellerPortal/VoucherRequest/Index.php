<?php

namespace App\Livewire\ResellerPortal\VoucherRequest;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\VoucherRequest;
use App\Models\ISP\VoucherTemplate;
use App\Services\ISP\VoucherRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends BaseNetworkComponent
{
    public string $activeTab = 'all'; // all, pending, in_progress, completed

    // Create Modal
    public bool $showCreateModal = false;
    public $service_profile_id = null;
    public int $quantity = 100;
    public string $delivery_type = 'physical_print';
    public string $payment_method = 'deposit';
    public $voucher_template_id = null;
    public string $notes = '';

    // Calculated amounts
    public float $calc_unit_price = 0;
    public float $calc_cost_price = 0;
    public float $calc_total_amount = 0;
    public float $calc_estimated_profit = 0;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'sales.voucher-request';
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Voucher', 'url' => route('reseller-portal.sales.voucher')],
            ['label' => 'Permintaan Cetak Voucher'],
        ];
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $defaultProfile = ServiceProfile::whereIn(DB::raw('LOWER(service_type)'), ['hotspot', 'voucher'])
            ->where('status', 'active')
            ->first();

        if ($defaultProfile) {
            $this->service_profile_id = $defaultProfile->id;
            $this->recalculatePricing();
        }

        $defaultTemplate = VoucherTemplate::where('is_default', true)->where('is_active', true)->first();
        $this->voucher_template_id = $defaultTemplate?->id;

        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    private function resetCreateForm()
    {
        $this->service_profile_id = null;
        $this->quantity = 100;
        $this->delivery_type = 'physical_print';
        $this->payment_method = 'deposit';
        $this->voucher_template_id = null;
        $this->notes = '';
        $this->calc_unit_price = 0;
        $this->calc_cost_price = 0;
        $this->calc_total_amount = 0;
        $this->calc_estimated_profit = 0;
    }

    public function updatedServiceProfileId()
    {
        $this->recalculatePricing();
    }

    public function updatedQuantity()
    {
        $this->recalculatePricing();
    }

    public function setQuantityPreset(int $qty)
    {
        $this->quantity = $qty;
        $this->recalculatePricing();
    }

    private function recalculatePricing()
    {
        if (!$this->service_profile_id) {
            $this->calc_unit_price = 0;
            $this->calc_cost_price = 0;
            $this->calc_total_amount = 0;
            $this->calc_estimated_profit = 0;
            return;
        }

        $profile = ServiceProfile::find($this->service_profile_id);
        if ($profile) {
            $this->calc_unit_price = (float) ($profile->promo_price ?: ($profile->base_price ?: 0));
            $this->calc_cost_price = (float) ($profile->reseller_price ?: ($profile->cost_price ?: 0));
            $this->calc_total_amount = $this->calc_cost_price * $this->quantity;
            $this->calc_estimated_profit = max(0, ($this->calc_unit_price - $this->calc_cost_price) * $this->quantity);
        }
    }

    public function submitRequest(VoucherRequestService $service)
    {
        $this->validate([
            'service_profile_id' => 'required|exists:service_profiles,id',
            'quantity' => 'required|integer|min:1|max:5000',
            'delivery_type' => 'required|in:physical_print,digital',
            'payment_method' => 'required|in:deposit,cash,tempo',
            'voucher_template_id' => 'nullable|exists:voucher_templates,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $data = [
                'service_profile_id' => $this->service_profile_id,
                'quantity' => $this->quantity,
                'delivery_type' => $this->delivery_type,
                'payment_method' => $this->payment_method,
                'voucher_template_id' => $this->voucher_template_id,
                'notes' => $this->notes,
            ];

            $voucherRequest = $service->createRequest($data, Auth::user());

            $this->closeCreateModal();
            session()->flash('success', "Permintaan voucher #{$voucherRequest->request_no} berhasil dikirim ke Admin!");
        } catch (\Throwable $e) {
            Log::error('Create Voucher Request Failed: ' . $e->getMessage());
            session()->flash('error', 'Gagal membuat permintaan: ' . $e->getMessage());
        }
    }

    public function cancelRequest(int $id, VoucherRequestService $service)
    {
        try {
            $request = VoucherRequest::findOrFail($id);
            $service->cancelRequest($request, Auth::user());
            session()->flash('success', "Permintaan #{$request->request_no} telah dibatalkan.");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal membatalkan permintaan: ' . $e->getMessage());
        }
    }

    public function printVouchers(int $id)
    {
        $request = VoucherRequest::findOrFail($id);
        if (empty($request->generated_voucher_ids)) {
            session()->flash('error', 'Voucher belum dibuat oleh admin.');
            return;
        }

        $ids = implode(',', $request->generated_voucher_ids);
        $this->dispatch('open-print-dialog', ids: $ids, template_id: $request->voucher_template_id);
    }

    public function render()
    {
        $user = Auth::user();
        $resellerId = $user->getEffectiveResellerId() ?? $user->id;

        $query = VoucherRequest::withoutGlobalScopes()
            ->where('reseller_id', $resellerId)
            ->with(['serviceProfile', 'voucherTemplate', 'approvedBy']);

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('request_no', 'like', "%{$s}%")
                    ->orWhereHas('serviceProfile', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        if ($this->activeTab === 'pending') {
            $query->where('status', VoucherRequest::STATUS_PENDING);
        } elseif ($this->activeTab === 'in_progress') {
            $query->whereIn('status', [VoucherRequest::STATUS_APPROVED, VoucherRequest::STATUS_PRINTING, VoucherRequest::STATUS_READY_FOR_PICKUP]);
        } elseif ($this->activeTab === 'completed') {
            $query->where('status', VoucherRequest::STATUS_COMPLETED);
        }

        // Stats calculation for Reseller
        $baseStats = VoucherRequest::withoutGlobalScopes()->where('reseller_id', $resellerId);
        $stats = [
            'total' => (clone $baseStats)->count(),
            'pending' => (clone $baseStats)->where('status', VoucherRequest::STATUS_PENDING)->count(),
            'printing' => (clone $baseStats)->whereIn('status', [VoucherRequest::STATUS_APPROVED, VoucherRequest::STATUS_PRINTING])->count(),
            'ready' => (clone $baseStats)->where('status', VoucherRequest::STATUS_READY_FOR_PICKUP)->count(),
            'completed' => (clone $baseStats)->where('status', VoucherRequest::STATUS_COMPLETED)->count(),
        ];

        $requests = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int) $this->perPage);

        $packages = ServiceProfile::whereIn(DB::raw('LOWER(service_type)'), ['hotspot', 'voucher'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $templates = VoucherTemplate::where('is_active', true)->orderBy('name')->get();

        return view('livewire.reseller-portal.voucher-request.index', compact(
            'requests',
            'stats',
            'packages',
            'templates'
        ));
    }
}
