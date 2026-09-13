<?php

namespace App\Jobs\Provisioning;

use App\Models\Customer\CustomerService;
use App\Services\Provisioning\ServiceProvisioningEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionCustomerServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    protected $customerServiceId;

    public function __construct(int $customerServiceId)
    {
        $this->customerServiceId = $customerServiceId;
    }

    public function handle(ServiceProvisioningEngine $engine)
    {
        $cs = CustomerService::find($this->customerServiceId);
        if (!$cs) return;

        Log::info("ProvisionCustomerServiceJob: Resuming provision for CS {$cs->id}");
        
        $result = $engine->provisionService($cs);

        if (($result['status'] ?? '') === 'SUCCESS') {
            Log::info("Provisioning completed successfully for CS {$cs->id}");
        } elseif (($result['status'] ?? '') === 'REMEDIATION_REQUIRED') {
            Log::warning("Remediation still required after job completion? CS {$cs->id}");
        } else {
            Log::error("Provisioning failed for CS {$cs->id}: " . json_encode($result));
        }
    }
}
