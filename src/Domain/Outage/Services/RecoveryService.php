<?php

namespace Src\Domain\Outage\Services;

use Src\Domain\Outage\Outage;
use Src\Domain\Outage\RecoveryPlan;
use Src\Domain\Outage\AffectedNode;
use Src\Domain\Outage\AffectedCustomer;
use Src\Domain\Outage\Repositories\OutageRepositoryInterface;
use Src\Domain\Outage\Repositories\RecoveryPlanRepositoryInterface;
use Src\Domain\Outage\Repositories\AffectedNodeRepositoryInterface;
use Src\Domain\Outage\Repositories\AffectedCustomerRepositoryInterface;
use Src\Domain\Outage\ValueObjects\RecoveryTimeEstimate;
use Src\Domain\Outage\Enums\RecoveryStatus;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RecoveryService
{
    public function __construct(
        private OutageRepositoryInterface $outageRepository,
        private RecoveryPlanRepositoryInterface $recoveryRepository,
        private AffectedNodeRepositoryInterface $nodeRepository,
        private AffectedCustomerRepositoryInterface $customerRepository
    ) {}

    public function createRecoveryPlan(
        Uuid $outageId,
        string $title,
        string $description,
        RecoveryTimeEstimate $estimatedRecovery,
        array $steps = []
    ): RecoveryPlan {
        $outage = $this->outageRepository->findById($outageId);
        if (!$outage) {
            throw new \InvalidArgumentException("Outage not found");
        }

        $recoveryPlan = RecoveryPlan::create(
            Uuid::generate(),
            $outageId,
            $title,
            $description,
            $estimatedRecovery,
            $steps
        );

        $this->recoveryRepository->save($recoveryPlan);

        return $recoveryPlan;
    }

    public function startRecovery(
        Uuid $outageId,
        string $technicianId,
        ?string $technicianName = null,
        ?RecoveryTimeEstimate $estimatedRecovery = null
    ): RecoveryPlan {
        $outage = $this->outageRepository->findById($outageId);
        if (!$outage) {
            throw new \InvalidArgumentException("Outage not found");
        }

        $recoveryPlan = $this->recoveryRepository->findByOutageId($outageId);
        
        if (!$recoveryPlan) {
            $defaultEstimate = new RecoveryTimeEstimate(30, 120, 60, 'default', 0.5);
            $recoveryPlan = $this->createRecoveryPlan(
                $outageId,
                "Recovery for {$outage->title}",
                "Recovery plan for {$outage->originNodeType->label()}: {$outage->originNodeName}",
                $estimatedRecovery ?? $defaultEstimate,
                $this->generateDefaultSteps($outage)
            );
        }

        $recoveryPlan->assignTechnician($technicianId, $technicianName);
        $recoveryPlan->start();
        $this->recoveryRepository->save($recoveryPlan);

        $outage->startRecovery($recoveryPlan->estimatedRecovery, $technicianId, $technicianName);
        $this->outageRepository->save($outage);

        return $recoveryPlan;
    }

    public function executeStep(
        Uuid $recoveryPlanId,
        int $stepIndex,
        string $result,
        bool $success = true
    ): void {
        $recoveryPlan = $this->recoveryRepository->findById($recoveryPlanId);
        if (!$recoveryPlan) {
            throw new \InvalidArgumentException("Recovery plan not found");
        }

        if ($success) {
            $recoveryPlan->completeStep($stepIndex, $result);
        } else {
            $recoveryPlan->failStep($stepIndex, $result);
        }

        $this->recoveryRepository->save($recoveryPlan);
    }

    public function verifyRecovery(Uuid $outageId): bool
    {
        $affectedNodes = $this->nodeRepository->findUnrecoveredNodes($outageId);
        
        if (!empty($affectedNodes)) {
            return false;
        }

        $affectedCustomers = $this->customerRepository->findUnrecoveredCustomers($outageId);
        
        if (!empty($affectedCustomers)) {
            return false;
        }

        $recoveryPlan = $this->recoveryRepository->findByOutageId($outageId);
        if ($recoveryPlan) {
            $recoveryPlan->verify();
            $this->recoveryRepository->save($recoveryPlan);
        }

        return true;
    }

    public function completeRecovery(
        Uuid $outageId,
        bool $fullyRestored = true,
        ?string $notes = null
    ): void {
        $outage = $this->outageRepository->findById($outageId);
        if (!$outage) {
            throw new \InvalidArgumentException("Outage not found");
        }

        $recoveryPlan = $this->recoveryRepository->findByOutageId($outageId);
        
        $customerRestoredCount = 0;
        $totalAffected = $outage->affectedCustomerCount;

        if ($fullyRestored) {
            $affectedCustomers = $this->customerRepository->findUnrecoveredCustomers($outageId);
            foreach ($affectedCustomers as $customer) {
                $customer->recover();
                $this->customerRepository->save($customer);
                $customerRestoredCount++;
            }

            $affectedNodes = $this->nodeRepository->findUnrecoveredNodes($outageId);
            foreach ($affectedNodes as $node) {
                $node->recover();
                $this->nodeRepository->save($node);
            }
        }

        $outage->resolve($fullyRestored, $customerRestoredCount, $notes);
        $this->outageRepository->save($outage);

        if ($recoveryPlan) {
            $recoveryPlan->complete($fullyRestored, $notes);
            $this->recoveryRepository->save($recoveryPlan);
        }
    }

    public function getRecoveryProgress(Uuid $outageId): array
    {
        $outage = $this->outageRepository->findById($outageId);
        if (!$outage) {
            throw new \InvalidArgumentException("Outage not found");
        }

        $recoveryPlan = $this->recoveryRepository->findByOutageId($outageId);
        
        $affectedNodes = $this->nodeRepository->findByOutageId($outageId);
        $recoveredNodes = $this->nodeRepository->findRecoveredNodes($outageId);

        $affectedCustomers = $this->customerRepository->findByOutageId($outageId);
        $recoveredCustomers = $this->customerRepository->findRecoveredCustomers($outageId);

        return [
            'outage_id' => $outageId->value,
            'status' => $outage->status->value,
            'recovery_plan' => $recoveryPlan ? [
                'id' => $recoveryPlan->id->value,
                'status' => $recoveryPlan->status->value,
                'progress_percentage' => $recoveryPlan->getProgressPercentage(),
                'technician' => $recoveryPlan->technicianName,
                'started_at' => $recoveryPlan->startedAt?->format('Y-m-d H:i:s'),
            ] : null,
            'nodes' => [
                'total' => count($affectedNodes),
                'recovered' => count($recoveredNodes),
                'remaining' => count($affectedNodes) - count($recoveredNodes)
            ],
            'customers' => [
                'total' => count($affectedCustomers),
                'recovered' => count($recoveredCustomers),
                'remaining' => count($affectedCustomers) - count($recoveredCustomers)
            ],
            'duration_minutes' => $outage->getDurationMinutes(),
            'is_within_sla' => $outage->isWithinSla(),
            'sla_remaining_minutes' => $outage->getSlaRemainingMinutes()
        ];
    }

    public function calculateRecoveryEstimate(
        Uuid $outageId,
        string $causeCode,
        array $historicalData = []
    ): RecoveryTimeEstimate {
        return RecoveryTimeEstimate::fromHistory($historicalData);
    }

    private function generateDefaultSteps(Outage $outage): array
    {
        $steps = [
            [
                'order' => 1,
                'title' => 'Investigasi dan Konfirmasi Gangguan',
                'description' => 'Verifikasi gangguan dan kumpulkan data diagnostik',
                'status' => 'pending'
            ],
            [
                'order' => 2,
                'title' => 'Identifikasi Root Cause',
                'description' => 'Tentukan penyebab utama gangguan',
                'status' => 'pending'
            ],
            [
                'order' => 3,
                'title' => 'Persiapan Sumber Daya',
                'description' => 'Siapkan peralatan dan material yang dibutuhkan',
                'status' => 'pending'
            ],
            [
                'order' => 4,
                'title' => 'Eksekusi Perbaikan',
                'description' => 'Lakukan perbaikan pada infrastruktur',
                'status' => 'pending'
            ],
            [
                'order' => 5,
                'title' => 'Verifikasi Pemulihan',
                'description' => 'Pastikan semua layanan sudah pulih',
                'status' => 'pending'
            ],
            [
                'order' => 6,
                'title' => 'Notifikasi Pelanggan',
                'description' => 'Informasikan pelanggan tentang pemulihan layanan',
                'status' => 'pending'
            ]
        ];

        return $steps;
    }

    public function getActiveRecoveries(): array
    {
        return $this->recoveryRepository->findActivePlans();
    }

    public function getRecoveryPerformance(\DateTimeImmutable $since = null): array
    {
        $completedPlans = $this->recoveryRepository->findCompletedPlans($since);
        
        if (empty($completedPlans)) {
            return [
                'total_recoveries' => 0,
                'average_duration_minutes' => 0,
                'success_rate' => 0,
                'within_sla_rate' => 0
            ];
        }

        $totalDuration = 0;
        $successfulCount = 0;
        $withinSlaCount = 0;

        foreach ($completedPlans as $plan) {
            if ($plan->actualDurationMinutes !== null) {
                $totalDuration += $plan->actualDurationMinutes;
            }

            if ($plan->status === RecoveryStatus::COMPLETED) {
                $successfulCount++;
            }

            if ($plan->isWithinEstimate()) {
                $withinSlaCount++;
            }
        }

        return [
            'total_recoveries' => count($completedPlans),
            'average_duration_minutes' => $totalDuration / count($completedPlans),
            'success_rate' => ($successfulCount / count($completedPlans)) * 100,
            'within_sla_rate' => ($withinSlaCount / count($completedPlans)) * 100
        ];
    }
}
