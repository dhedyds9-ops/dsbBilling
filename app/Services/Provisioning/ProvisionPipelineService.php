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
            ['name' => 'Reserve Resources', 'type' => 'reservation', 'order' => 1],
            ['name' => 'Assign Device', 'type' => 'device_assignment', 'order' => 2],
            ['name' => 'Allocate VLAN', 'type' => 'vlan_allocation', 'order' => 3],
            ['name' => 'Allocate IP', 'type' => 'ip_allocation', 'order' => 4],
            ['name' => 'Allocate Queue', 'type' => 'queue_allocation', 'order' => 5],
            ['name' => 'Provision Router', 'type' => 'router_provision', 'order' => 6],
            ['name' => 'Provision RADIUS', 'type' => 'radius_provision', 'order' => 7],
            ['name' => 'Provision ONU', 'type' => 'onu_provision', 'order' => 8],
            ['name' => 'Verify Provisioning', 'type' => 'verification', 'order' => 9],
            ['name' => 'Release Reservations', 'type' => 'release', 'order' => 10],
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
