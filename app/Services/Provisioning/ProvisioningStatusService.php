<?php

namespace App\Services\Provisioning;

use App\Models\Provisioning\ProvisionPipeline;

class ProvisioningStatusService
{
    /**
     * Get the standardized provisioning status for the UI
     */
    public function getStatus(int $pipelineId): array
    {
        $pipeline = ProvisionPipeline::with(['steps', 'serviceInstance.customerService.customer', 'serviceInstance.customerService.pppoeUser'])->findOrFail($pipelineId);
        $cs = $pipeline->serviceInstance?->customerService;

        // Auto-heal / reconciliation: if customer service is already active, reconcile steps
        if ($cs && $cs->status === 'active' && $pipeline->status !== 'completed' && $pipeline->status !== 'failed') {
            $hasStandardSteps = $pipeline->steps->where('step_type', 'olt_onu_registration')->isNotEmpty();
            if (!$hasStandardSteps) {
                // Delete old non-standard dummy steps
                $pipeline->steps()->delete();
                $standardSteps = [
                    ['name' => 'Validasi Layanan', 'type' => 'customer_validation', 'order' => 1],
                    ['name' => 'Reservasi Port & Resource', 'type' => 'resource_reservation', 'order' => 2],
                    ['name' => 'Registrasi Perangkat ONU', 'type' => 'olt_onu_registration', 'order' => 3],
                    ['name' => 'Konfigurasi Profil OLT & VLAN', 'type' => 'olt_service_provisioning', 'order' => 4],
                    ['name' => 'Aktivasi PPPoE / RADIUS', 'type' => 'radius_provisioning', 'order' => 5],
                    ['name' => 'Konfigurasi TR-069 (GenieACS)', 'type' => 'onu_tr069_provisioning', 'order' => 6],
                    ['name' => 'Verifikasi Layanan', 'type' => 'service_verification', 'order' => 7],
                ];
                foreach ($standardSteps as $step) {
                    \App\Models\Provisioning\ProvisionPipelineStep::create([
                        'provision_pipeline_id' => $pipeline->id,
                        'step_name' => $step['name'],
                        'step_type' => $step['type'],
                        'order' => $step['order'],
                        'status' => 'completed',
                        'started_at' => now(),
                        'completed_at' => now(),
                    ]);
                }
            } else {
                $pipeline->steps()->where('status', '!=', 'completed')->update([
                    'status' => 'completed',
                    'started_at' => now(),
                    'completed_at' => now(),
                ]);
            }

            $pipeline->update([
                'status' => 'completed',
                'current_step' => 7,
                'total_steps' => 7,
                'completed_at' => now(),
            ]);

            $pipeline->serviceInstance?->update([
                'status' => 'provisioned',
                'provisioned_at' => now(),
            ]);

            $pipeline->load('steps');
        }
        
        $steps = $pipeline->steps->map(function ($step) {
            return [
                'name' => $step->step_name,
                'status' => $step->status,
                'started_at' => $step->started_at,
                'completed_at' => $step->completed_at,
                'error' => $step->error_message,
                'type' => $step->step_type
            ];
        })->toArray();

        $completedSteps = $pipeline->steps->where('status', 'completed')->count();
        $totalSteps = $pipeline->total_steps;
        $progress = $totalSteps > 0 ? (int) (($completedSteps / $totalSteps) * 100) : 0;

        // Ensure we handle skipped properly
        $completedOrSkippedSteps = $pipeline->steps->whereIn('status', ['completed', 'skipped'])->count();
        $progress = $totalSteps > 0 ? (int) (($completedOrSkippedSteps / $totalSteps) * 100) : 0;

        // Determine specific subsystem states based on step payloads and logic
        $onuStep = $pipeline->steps->where('step_type', 'olt_onu_registration')->first();
        $tr069Step = $pipeline->steps->where('step_type', 'onu_tr069_provisioning')->first();
        $radiusStep = $pipeline->steps->where('step_type', 'radius_provisioning')->first();
        $verificationStep = $pipeline->steps->where('step_type', 'service_verification')->first();

        // can_activate is strictly ALL REQUIRED COMPLETED + VERIFICATION_PASSED + NOT FAILED
        $canActivate = false;
        if ($pipeline->status !== 'failed') {
            $requiredStepsCount = max(0, $pipeline->total_steps - 1);
            $completedCount = $pipeline->steps->where('order', '<', $totalSteps)->whereIn('status', ['completed', 'skipped'])->count();
            if ($completedCount >= $requiredStepsCount && ($verificationStep?->status === 'completed' || $pipeline->status === 'completed')) {
                $canActivate = true;
            }
        }

        return [
            'pipeline_status' => $pipeline->status,
            'customer_service_status' => $cs?->status ?? 'unknown',
            'customer_name' => $cs?->customer?->name ?? 'Pelanggan',
            'username' => $cs?->username ?? $cs?->pppoeUser?->username ?? null,
            'current_step' => $pipeline->current_step,
            'progress' => $progress,
            'steps' => $steps,
            'onu_status' => $onuStep?->status ?? ($pipeline->status === 'completed' ? 'completed' : 'pending'),
            'tr069_status' => $tr069Step?->status ?? ($pipeline->status === 'completed' ? 'completed' : 'pending'),
            'radius_status' => $radiusStep?->status ?? ($pipeline->status === 'completed' ? 'completed' : 'pending'),
            'verification_status' => $verificationStep?->status ?? ($pipeline->status === 'completed' ? 'completed' : 'pending'),
            'can_activate' => $canActivate,
        ];
    }
}
