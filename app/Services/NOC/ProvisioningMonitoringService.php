<?php

namespace App\Services\NOC;

use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Provisioning\ServiceInstance;
use App\Models\Customer\CustomerService;
use App\Models\CRM\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProvisioningMonitoringService
{
    const CACHE_TTL = 15; // seconds

    /**
     * Get provisioning queue summary counts.
     */
    public function getQueueSummary(): array
    {
        return Cache::remember('noc.provisioning.summary', self::CACHE_TTL, function () {
            $counts = ProvisionPipeline::withoutTrashed()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return [
                'queued'    => $counts['pending'] ?? 0,
                'running'   => $counts['running'] ?? 0,
                'completed' => $counts['completed'] ?? 0,
                'failed'    => $counts['failed'] ?? 0,
                'total'     => array_sum($counts),
            ];
        });
    }

    /**
     * Get paginated provisioning pipelines for NOC monitoring.
     * Never exposes credentials or sensitive data.
     */
    public function getPipelines(
        string $statusFilter = 'all',
        string $search = '',
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = ProvisionPipeline::withoutTrashed()
            ->with([
                'steps:id,provision_pipeline_id,step_name,step_type,order,status,started_at,completed_at,failed_at,error_message',
                'serviceInstance.customerService.customer:id,name,code',
                'createdBy:id,name',
            ])
            ->latest();

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                  ->orWhereHas('serviceInstance.customerService.customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a single pipeline with steps for NOC detail view.
     * Strips any sensitive step payload data before returning.
     */
    public function getPipelineDetail(int $pipelineId): ?ProvisionPipeline
    {
        $pipeline = ProvisionPipeline::with([
            'steps:id,provision_pipeline_id,step_name,step_type,order,status,started_at,completed_at,failed_at,error_message',
            'serviceInstance.customerService.customer:id,name,code',
            'serviceInstance.customerService.onu:id,serial_number,code,status,olt_id',
            'serviceInstance.customerService.onu.olt:id,name,ip_address',
            'createdBy:id,name',
        ])->find($pipelineId);

        return $pipeline;
    }

    /**
     * Retry a failed pipeline using the existing PipelineOrchestrator.
     * Never bypasses the provisioning orchestrator.
     */
    public function retryPipeline(int $pipelineId, int $userId): bool
    {
        $pipeline = ProvisionPipeline::where('status', 'failed')->find($pipelineId);
        if (!$pipeline) {
            return false;
        }

        try {
            // Reset failed steps for retry
            $pipeline->steps()
                ->where('status', 'failed')
                ->update([
                    'status'       => 'pending',
                    'failed_at'    => null,
                    'error_message' => null,
                ]);

            $pipeline->update([
                'status'        => 'pending',
                'failed_at'     => null,
                'error_message' => null,
                'updated_by'    => $userId,
            ]);

            // Use existing orchestrator — no bypass
            app(\App\Services\Provisioning\Pipeline\PipelineOrchestrator::class)->processNext($pipeline);

            Cache::forget('noc.provisioning.summary');

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('NOC: Pipeline retry failed', [
                'pipeline_id' => $pipelineId,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get recent failed pipelines for alarm-like display.
     */
    public function getRecentFailures(int $limit = 10): \Illuminate\Support\Collection
    {
        return ProvisionPipeline::with([
            'steps' => fn ($q) => $q->where('status', 'failed')->select('id', 'provision_pipeline_id', 'step_name', 'error_message', 'failed_at'),
            'serviceInstance.customerService.customer:id,name,code',
        ])
        ->where('status', 'failed')
        ->latest('failed_at')
        ->limit($limit)
        ->get();
    }
}
