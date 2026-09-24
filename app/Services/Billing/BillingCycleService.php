<?php

namespace App\Services\Billing;

use App\Models\Billing\BillingCycle;
use App\Models\Billing\Subscription;
use App\Repositories\Billing\BillingCycleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingCycleService
{
    public function __construct(
        protected BillingCycleRepository $billingCycleRepository,
    ) {}

    public function createBillingCycle(
        string $name,
        string $cycleType,
        \DateTimeInterface $cycleStart,
        ?\DateTimeInterface $cycleEnd = null,
        int $invoiceDueDays = 7,
        int $userId = null,
    ): BillingCycle {
        return DB::transaction(function () use ($name, $cycleType, $cycleStart, $cycleEnd, $invoiceDueDays, $userId) {
            return $this->billingCycleRepository->create([
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'cycle_type' => $cycleType,
                'cycle_start' => $cycleStart,
                'cycle_end' => $cycleEnd,
                'invoice_due_days' => $invoiceDueDays,
                'status' => 'active',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    public function closeBillingCycle(int $cycleId, int $userId): BillingCycle
    {
        return DB::transaction(function () use ($cycleId, $userId) {
            $cycle = $this->billingCycleRepository->find($cycleId);
            $cycle->update([
                'status' => 'closed',
                'cycle_end' => now(),
                'updated_by' => $userId,
            ]);

            return $cycle;
        });
    }
}
