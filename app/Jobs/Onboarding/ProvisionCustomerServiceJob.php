<?php

namespace App\Jobs\Onboarding;

use App\Models\Customer\CustomerService;
use App\Services\Adapters\Provisioning\OltRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionCustomerServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected CustomerService $customerService)
    {
    }

    public function handle(OltRegistry $registry): void
    {
        if ($this->customerService->onu && $this->customerService->onu->olt) {
            $adapter = $registry->get($this->customerService->onu->olt->vendor->name ?? 'default');
            if ($adapter) {
                $adapter->provision($this->customerService);
                $this->customerService->update([
                    'status' => 'active',
                    'activated_at' => now(),
                ]);
            }
        }
    }
}
