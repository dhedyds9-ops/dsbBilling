<?php

namespace App\Services\Provisioning;

use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Provisioning\ProvisionPipelineStep;
use App\Models\Provisioning\ServiceInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Src\Domain\Provisioning\Events\ProvisionStartedEvent;

class ProvisionPipelineService
{
    public function createPipeline(int $serviceInstanceId, int $userId): ProvisionPipeline
    {
        return DB::transaction(function () use ($serviceInstanceId, $userId) {
            $pipeline = ProvisionPipeline::create([
                'uuid' => (string)Str::uuid(),
                'service_instance_id' => $serviceInstanceId,
                'status' => 'pending',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->createDefaultSteps($pipeline);

            event(new ProvisionStartedEvent(
                ServiceInstance::find($serviceInstanceId)->uuid,
                $pipeline->uuid
            ));

            return $pipeline;
        });
    }

    protected function createDefaultSteps(ProvisionPipeline $pipeline): void
    {
        $steps = [
            ['name' => 'Customer Validation', 'type' => 'customer_validation', 'order' => 1],
            ['name' => 'Resource Reservation', 'type' => 'resource_reservation', 'order' => 2],
            ['name' => 'ONU Registration', 'type' => 'olt_onu_registration', 'order' => 3],
            ['name' => 'OLT Service Provisioning', 'type' => 'olt_service_provisioning', 'order' => 4],
            ['name' => 'RADIUS Provisioning', 'type' => 'radius_provisioning', 'order' => 5],
            ['name' => 'TR-069 Provisioning', 'type' => 'onu_tr069_provisioning', 'order' => 6],
            ['name' => 'Service Verification', 'type' => 'service_verification', 'order' => 7],
            ['name' => 'Activation', 'type' => 'activation', 'order' => 8],
        ];

        foreach ($steps as $step) {
            ProvisionPipelineStep::create([
                'provision_pipeline_id' => $pipeline->id,
                'step_name' => $step['name'],
                'step_type' => $step['type'],
                'order' => $step['order'],
                'status' => 'pending',
            ]);
        }

        $pipeline->update(['total_steps' => count($steps)]);
    }

    public function startPipeline(int $pipelineId): void
    {
        $pipeline = ProvisionPipeline::findOrFail($pipelineId);
        
        $orchestrator = app(\App\Services\Provisioning\Pipeline\PipelineOrchestrator::class);
        $orchestrator->processNext($pipeline);
    }

    public function updateStepStatus(int $pipelineId, string $stepName, string $status, ?string $error = null): ProvisionPipelineStep
    {
        $step = ProvisionPipelineStep::where('provision_pipeline_id', $pipelineId)
            ->where('step_name', $stepName)
            ->firstOrFail();

        $data = ['status' => $status];
        if ($status === 'in_progress') {
            $data['started_at'] = now();
        } elseif ($status === 'completed') {
            $data['completed_at'] = now();
        } elseif ($status === 'failed') {
            $data['failed_at'] = now();
            $data['error_message'] = $error;
        }

        $step->update($data);
        return $step;
    }
}
