<?php

namespace App\Services\Billing;

use App\Models\Billing\Subscription;
use App\Models\Customer\Contract;
use App\Models\Customer\CustomerService;
use App\Repositories\Billing\SubscriptionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Src\Domain\Billing\Events\SubscriptionCreatedEvent;
use Src\Domain\Billing\Events\SubscriptionCancelledEvent;

class SubscriptionService
{
    public function __construct(
        protected SubscriptionRepository $subscriptionRepository,
    ) {}

    public function createSubscription(
        CustomerService $customerService,
        Contract $contract,
        float $recurringPrice,
        int $userId,
        string $billingCycle = 'monthly',
        ?\DateTimeInterface $startDate = null,
    ): Subscription {
        return DB::transaction(function () use ($customerService, $contract, $recurringPrice, $userId, $billingCycle, $startDate) {
            $subscription = $this->subscriptionRepository->create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $contract->customer_id,
                'contract_id' => $contract->id,
                'customer_service_id' => $customerService->id,
                'status' => 'active',
                'start_date' => $startDate ?? now(),
                'billing_cycle' => $billingCycle,
                'recurring_price' => $recurringPrice,
                'next_billing_date' => $this->calculateNextBillingDate($startDate ?? now(), $billingCycle),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            Event::dispatch(new SubscriptionCreatedEvent(
                $subscription->uuid,
                $subscription->customer_id,
                $subscription->customer_service_id,
            ));

            return $subscription;
        });
    }

    public function cancelSubscription(int $subscriptionId, string $reason, int $userId): Subscription
    {
        return DB::transaction(function () use ($subscriptionId, $reason, $userId) {
            $subscription = $this->subscriptionRepository->find($subscriptionId);
            $subscription->update([
                'status' => 'cancelled',
                'end_date' => now(),
                'updated_by' => $userId,
            ]);

            Event::dispatch(new SubscriptionCancelledEvent(
                $subscription->uuid,
                $subscription->customer_id,
                $reason,
            ));

            return $subscription;
        });
    }

    protected function calculateNextBillingDate(\DateTimeInterface $startDate, string $billingCycle): \DateTimeInterface
    {
        $nextDate = \Illuminate\Support\Carbon::instance($startDate)->copy();

        switch ($billingCycle) {
            case 'monthly':
                $nextDate->addMonthNoOverflow();
                break;
            case 'quarterly':
                $nextDate->addMonthsNoOverflow(3);
                break;
            case 'yearly':
                $nextDate->addYearNoOverflow();
                break;
        }

        return $nextDate;
    }
}
