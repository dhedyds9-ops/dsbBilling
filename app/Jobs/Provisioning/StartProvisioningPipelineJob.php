<?php

namespace App\Jobs\Provisioning;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Provisioning\ProvisionPipeline;
use App\Services\Provisioning\Pipeline\PipelineOrchestrator;
use App\Models\Customer\CustomerService;

class StartProvisioningPipelineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ProvisionPipeline $pipeline
    ) {}

    public function handle(PipelineOrchestrator $orchestrator): void
    {
        // 1. Mark customer service as provisioning (decoupled from pipeline status)
        $cs = $this->pipeline->serviceInstance->customerService;
        if ($cs && $cs->status === 'pending') {
            $cs->update(['status' => 'provisioning']);
        }

        // 2. Start Orchestrator
        $orchestrator->processNext($this->pipeline);
    }
}
