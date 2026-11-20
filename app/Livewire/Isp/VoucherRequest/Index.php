<?php

namespace App\Livewire\Isp\VoucherRequest;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\NasDevice;
use App\Models\ISP\VoucherRequest;
use App\Models\ISP\VoucherTemplate;
use App\Models\User;
use App\Services\ISP\VoucherRequestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Index extends BaseNetworkComponent
{
    public string $activeTab = 'all'; // all, pending, printing, ready_for_pickup, completed, rejected

    // Modal states
    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public ?int $selectedRequestId = null;

    // Approval Options
    public $approve_nas_device_id = null;
    public $approve_voucher_template_id = null;
    public string $approve_login_method = 'voucher_code';
    public string $approve_code_combination = 'uppercase_alphanumeric';
    public int $approve_length = 6;
    public string $approve_prefix = '';

    // Rejection Options
    public string $reject_reason = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'voucher-requests';
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.vouchers.index')],
            ['label' => 'Permintaan Cetak Voucher'],
        ];
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function openApproveModal(int $id)
    {
        $this->selectedRequestId = $id;
        $request = VoucherRequest::with(['serviceProfile', 'reseller', 'voucherTemplate'])->findOrFail($id);

        $defaultTemplate = VoucherTemplate::where('is_default', true)->where('is_active', true)->first();
        $this->approve_voucher_template_id = $request->voucher_template_id ?: ($defaultTemplate?->id ?? null);
        $this->approve_prefix = $request->serviceProfile?->voucher_prefix ?? '';
        $this->approve_length = (int) ($request->serviceProfile?->voucher_length ?? 6);
        $this->approve_nas_device_id = null;

        $this->showApproveModal = true;
    }

    public function closeApproveModal()
    {
        $this->showApproveModal = false;
        $this->selectedRequestId = null;
    }

    public function approveRequest(VoucherRequestService $service)
    {
        $this->validate([
            'approve_length' => 'required|integer|min:4|max:16',
            'approve_login_method' => 'required|in:voucher_code,username_password',
            'approve_code_combination' => 'required|in:uppercase,lowercase,alphanumeric,numbers,uppercase_alphanumeric',
            'approve_voucher_template_id' => 'nullable|exists:voucher_templates,id',
            'approve_nas_device_id' => 'nullable|exists:nas_devices,id',
        ]);

        try {
            $request = VoucherRequest::findOrFail($this->selectedRequestId);
            $options = [
                'nas_device_id' => $this->approve_nas_device_id,
                'voucher_template_id' => $this->approve_voucher_template_id,
                'login_method' => $this->approve_login_method,
                'code_combination' => $this->approve_code_combination,
                'length' => $this->approve_length,
                'prefix' => $this->approve_prefix,
            ];

            $updated = $service->approveAndGenerate($request, Auth::user(), $options);

            $this->closeApproveModal();
            session()->flash('success', "Permintaan #{$updated->request_no} berhasil disetujui! " . count($updated->generated_voucher_ids ?? []) . " voucher telah di-generate.");

            // If delivery is physical print, offer printing immediately
            if ($updated->delivery_type === VoucherRequest::DELIVERY_PHYSICAL_PRINT && !empty($updated->generated_voucher_ids)) {
                $ids = implode(',', $updated->generated_voucher_ids);
                $this->dispatch('open-print-dialog', ids: $ids, template_id: $updated->voucher_template_id);
            }
        } catch (\Throwable $e) {
            Log::error('Approve Voucher Request Failed: ' . $e->getMessage());
            session()->flash('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    public function printRequestVouchers(int $id)
    {
        $request = VoucherRequest::findOrFail($id);
        if (empty($request->generated_voucher_ids)) {
            session()->flash('error', 'Voucher untuk permintaan ini belum di-generate.');
            return;
        }

        $ids = implode(',', $request->generated_voucher_ids);
        $this->dispatch('open-print-dialog', ids: $ids, template_id: $request->voucher_template_id);
    }

    public function markAsReady(int $id, VoucherRequestService $service)
    {
        try {
            $request = VoucherRequest::findOrFail($id);
            $service->markAsReady($request);
            session()->flash('success', "Permintaan #{$request->request_no} ditandai Siap Diambil/Dikirim.");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal update status: ' . $e->getMessage());
        }
    }

    public function markAsCompleted(int $id, VoucherRequestService $service)
    {
        try {
            $request = VoucherRequest::findOrFail($id);
            $service->markAsCompleted($request);
            session()->flash('success', "Permintaan #{$request->request_no} telah selesai & lunas.");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menyelesaikan permintaan: ' . $e->getMessage());
        }
    }

    public function openRejectModal(int $id)
    {
        $this->selectedRequestId = $id;
        $this->reject_reason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
        $this->selectedRequestId = null;
        $this->reject_reason = '';
    }

    public function rejectRequest(VoucherRequestService $service)
    {
        $this->validate([
            'reject_reason' => 'required|string|min:3|max:500',
        ]);

        try {
            $request = VoucherRequest::findOrFail($this->selectedRequestId);
            $service->rejectRequest($request, $this->reject_reason, Auth::user());
            $this->closeRejectModal();
            session()->flash('success', "Permintaan #{$request->request_no} telah ditolak.");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menolak permintaan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = VoucherRequest::withoutGlobalScopes()
            ->with(['reseller', 'serviceProfile', 'voucherTemplate', 'approvedBy']);

        $user = Auth::user();
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id);
            });
        }

        // Search
        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('request_no', 'like', "%{$s}%")
                    ->orWhereHas('reseller', fn($rq) => $rq->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('serviceProfile', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        // Tab Filter
        if ($this->activeTab !== 'all') {
            $query->where('status', $this->activeTab);
        }

        // Stats calculation
        $baseStats = VoucherRequest::withoutGlobalScopes();
        if ($user && $user->branch_id && !$user->isSuperAdmin()) {
            $baseStats->where(function ($q) use ($user) {
                $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id);
            });
        }
        $stats = [
            'total' => (clone $baseStats)->count(),
            'pending' => (clone $baseStats)->where('status', VoucherRequest::STATUS_PENDING)->count(),
            'printing' => (clone $baseStats)->where('status', VoucherRequest::STATUS_PRINTING)->count(),
            'ready' => (clone $baseStats)->where('status', VoucherRequest::STATUS_READY_FOR_PICKUP)->count(),
            'completed' => (clone $baseStats)->where('status', VoucherRequest::STATUS_COMPLETED)->count(),
        ];

        $requests = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int) $this->perPage);

        $selectedRequest = $this->selectedRequestId ? VoucherRequest::find($this->selectedRequestId) : null;
        $nasDevices = NasDevice::orderBy('name')->get();
        $voucherTemplates = VoucherTemplate::where('is_active', true)->orderBy('name')->get();

        return view('livewire.isp.voucher-request.index', compact(
            'requests',
            'stats',
            'selectedRequest',
            'nasDevices',
            'voucherTemplates'
        ));
    }
}
