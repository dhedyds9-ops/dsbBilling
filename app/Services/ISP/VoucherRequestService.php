<?php

namespace App\Services\ISP;

use App\Models\ISP\ServiceProfile;
use App\Models\ISP\VoucherRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Src\Domain\Voucher\Actions\GenerateVoucherAction;

class VoucherRequestService
{
    public function __construct(
        protected GenerateVoucherAction $generateVoucherAction
    ) {}

    /**
     * Reseller creates a new voucher request.
     */
    public function createRequest(array $data, User $reseller): VoucherRequest
    {
        return DB::transaction(function () use ($data, $reseller) {
            $serviceProfile = ServiceProfile::withoutGlobalScope('reseller_isolation')->findOrFail($data['service_profile_id']);
            $quantity = (int) ($data['quantity'] ?? 50);

            // Calculate unit price (retail) and cost price (reseller modal)
            $unitPrice = (float) ($serviceProfile->promo_price ?: ($serviceProfile->base_price ?: ($serviceProfile->price ?: 0)));
            $costPrice = (float) ($serviceProfile->reseller_price ?: ($serviceProfile->cost_price ?: $unitPrice));
            $totalAmount = $costPrice * $quantity;

            // Generate unique request number VR-YYYYMMDD-XXXX
            $datePrefix = 'VR-' . now()->format('Ymd');
            $latestReq = VoucherRequest::withoutGlobalScopes()
                ->where('request_no', 'like', "{$datePrefix}-%")
                ->orderByDesc('id')
                ->first();

            $seq = 1;
            if ($latestReq && preg_match('/-(\d{4})$/', $latestReq->request_no, $matches)) {
                $seq = ((int) $matches[1]) + 1;
            }
            $requestNo = sprintf('%s-%04d', $datePrefix, $seq);

            $effectiveResellerId = $reseller->getEffectiveResellerId();
            $branchId = $reseller->branch_id ?? $serviceProfile->branch_id;

            return VoucherRequest::create([
                'request_no' => $requestNo,
                'reseller_id' => $effectiveResellerId,
                'branch_id' => $branchId,
                'service_profile_id' => $serviceProfile->id,
                'voucher_template_id' => $data['voucher_template_id'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'cost_price' => $costPrice,
                'total_amount' => $totalAmount,
                'delivery_type' => $data['delivery_type'] ?? VoucherRequest::DELIVERY_PHYSICAL_PRINT,
                'payment_method' => $data['payment_method'] ?? VoucherRequest::PAYMENT_DEPOSIT,
                'payment_status' => VoucherRequest::PAYMENT_STATUS_UNPAID,
                'status' => VoucherRequest::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
                'created_by' => $reseller->id,
            ]);
        });
    }

    /**
     * Admin approves request and triggers batch voucher generation.
     */
    public function approveAndGenerate(VoucherRequest $request, User $admin, array $options = []): VoucherRequest
    {
        return DB::transaction(function () use ($request, $admin, $options) {
            if ($request->status !== VoucherRequest::STATUS_PENDING) {
                throw new \DomainException("Hanya permintaan berstatus 'pending' yang dapat disetujui.");
            }

            $attrs = [
                'type' => 'hotspot',
                'service_profile_id' => $request->service_profile_id,
                'reseller_id' => $request->reseller_id,
                'nas_device_id' => $options['nas_device_id'] ?? null,
                'login_method' => $options['login_method'] ?? 'voucher_code',
                'code_combination' => $options['code_combination'] ?? 'uppercase_alphanumeric',
                'length' => (int) ($options['length'] ?? 6),
                'prefix' => $options['prefix'] ?? '',
                'fee_seller' => (float) $request->cost_price,
                'notes' => "Request #{$request->request_no} Reseller #{$request->reseller_id}",
            ];

            // Generate vouchers through core domain action
            $vouchers = $this->generateVoucherAction->generateAdHocVouchers(
                $attrs,
                (int) $request->quantity,
                (int) $admin->id
            );

            $voucherIds = [];
            $poolId = null;

            foreach ($vouchers as $voucher) {
                $id = is_array($voucher) ? ($voucher['id'] ?? null) : ($voucher->id ?? null);
                if ($id) {
                    $voucherIds[] = (int) $id;
                }
                if (!$poolId) {
                    $poolId = is_array($voucher) ? ($voucher['voucher_pool_id'] ?? null) : ($voucher->voucher_pool_id ?? null);
                }
            }

            // Determine next status:
            // If physical print, set to printing so admin can proceed to print.
            // If digital softcopy, set to completed immediately.
            $nextStatus = $request->delivery_type === VoucherRequest::DELIVERY_PHYSICAL_PRINT
                ? VoucherRequest::STATUS_PRINTING
                : VoucherRequest::STATUS_COMPLETED;

            $request->update([
                'status' => $nextStatus,
                'batch_pool_id' => $poolId,
                'generated_voucher_ids' => $voucherIds,
                'voucher_template_id' => $options['voucher_template_id'] ?? $request->voucher_template_id,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'completed_at' => $nextStatus === VoucherRequest::STATUS_COMPLETED ? now() : null,
            ]);

            return $request->fresh();
        });
    }

    /**
     * Mark physical print as completed and ready for pickup/shipment.
     */
    public function markAsReady(VoucherRequest $request): VoucherRequest
    {
        if (!in_array($request->status, [VoucherRequest::STATUS_PRINTING, VoucherRequest::STATUS_APPROVED])) {
            throw new \DomainException("Status permintaan saat ini tidak dapat diubah menjadi siap diambil.");
        }

        $request->update([
            'status' => VoucherRequest::STATUS_READY_FOR_PICKUP,
        ]);

        return $request->fresh();
    }

    /**
     * Finalize request (handover completed, payment settled).
     */
    public function markAsCompleted(VoucherRequest $request): VoucherRequest
    {
        $request->update([
            'status' => VoucherRequest::STATUS_COMPLETED,
            'payment_status' => VoucherRequest::PAYMENT_STATUS_PAID,
            'completed_at' => now(),
        ]);

        return $request->fresh();
    }

    /**
     * Admin rejects a voucher request.
     */
    public function rejectRequest(VoucherRequest $request, string $reason, User $admin): VoucherRequest
    {
        if ($request->status !== VoucherRequest::STATUS_PENDING) {
            throw new \DomainException("Hanya permintaan berstatus 'pending' yang dapat ditolak.");
        }

        $request->update([
            'status' => VoucherRequest::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        return $request->fresh();
    }

    /**
     * Reseller cancels a pending request.
     */
    public function cancelRequest(VoucherRequest $request, User $reseller): VoucherRequest
    {
        if ($request->status !== VoucherRequest::STATUS_PENDING) {
            throw new \DomainException("Hanya permintaan berstatus 'pending' yang dapat dibatalkan.");
        }

        if ($request->reseller_id !== $reseller->getEffectiveResellerId()) {
            throw new \DomainException("Anda tidak berhak membatalkan permintaan ini.");
        }

        $request->update([
            'status' => VoucherRequest::STATUS_CANCELLED,
        ]);

        return $request->fresh();
    }
}
