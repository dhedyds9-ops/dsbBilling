<?php

namespace Src\Domain\AI\Services;

use Src\Domain\AI\AIAnomaly;
use Src\Domain\AI\Repositories\AIAnomalyRepositoryInterface;
use Src\Domain\AI\Repositories\AIModelRepositoryInterface;
use Src\Domain\AI\Events\AnomalyDetected;
use Src\Domain\AI\Enums\AnomalySeverity;
use Src\Domain\AI\ValueObjects\AnomalyResult;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class AnomalyDetectionService
{
    public function __construct(
        private readonly AIAnomalyRepositoryInterface $anomalyRepository,
        private readonly AIModelRepositoryInterface $modelRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AnomalyDetector $detector
    ) {}

    /**
     * Detect network anomalies from metrics
     */
    public function detectNetworkAnomaly(
        string $deviceId,
        array $metrics,
        ?Uuid $modelId = null
    ): AnomalyResult {
        // Get network anomaly detector model
        $model = $modelId
            ? $this->modelRepository->findById($modelId)
            : $this->modelRepository->findActiveByType(\Src\Domain\AI\Enums\AIModelType::NETWORK_ANOMALY);

        // Execute anomaly detection
        $result = $this->detector->detect(
            type: 'network',
            entityId: $deviceId,
            metrics: $metrics,
            model: $model
        );

        // Create anomaly record
        $anomaly = AIAnomaly::create(
            detectorType: 'network',
            entityType: 'device',
            entityId: $deviceId,
            isAnomaly: $result->isAnomaly,
            modelId: $model?->id
        );

        if ($result->isAnomaly) {
            $anomaly->setScore($result->score);
            $anomaly->setType($result->type ?? 'network_performance');
            $anomaly->setDescription($result->description ?? 'Network anomaly detected');
            $anomaly->setDetails($result->details);
            $anomaly->setRecommendations($result->getRecommendations());
        }

        $this->anomalyRepository->save($anomaly);

        // Dispatch event if anomaly detected
        if ($result->isAnomaly && $result->score >= 0.7) {
            $this->eventDispatcher->dispatch(new AnomalyDetected(
                anomalyId: $anomaly->id,
                entityType: 'device',
                entityId: $deviceId,
                severity: $anomaly->getSeverity()->value,
                score: $result->score
            ));
        }

        return $result;
    }

    /**
     * Detect capacity anomalies
     */
    public function detectCapacityAnomaly(
        string $resourceType,
        string $resourceId,
        array $capacityMetrics,
        ?Uuid $modelId = null
    ): AnomalyResult {
        $model = $modelId
            ? $this->modelRepository->findById($modelId)
            : $this->modelRepository->findActiveByType(\Src\Domain\AI\Enums\AIModelType::CAPACITY_ANOMALY);

        $result = $this->detector->detect(
            type: 'capacity',
            entityId: $resourceId,
            metrics: $capacityMetrics,
            model: $model
        );

        $anomaly = AIAnomaly::create(
            detectorType: 'capacity',
            entityType: $resourceType,
            entityId: $resourceId,
            isAnomaly: $result->isAnomaly,
            modelId: $model?->id
        );

        if ($result->isAnomaly) {
            $anomaly->setScore($result->score);
            $anomaly->setType('capacity_exceeded');
            $anomaly->setDescription($result->description ?? 'Capacity threshold exceeded');
            $anomaly->setDetails($result->details);
            $anomaly->setRecommendations($result->getRecommendations());
        }

        $this->anomalyRepository->save($anomaly);

        if ($result->isAnomaly && $result->score >= 0.7) {
            $this->eventDispatcher->dispatch(new AnomalyDetected(
                anomalyId: $anomaly->id,
                entityType: $resourceType,
                entityId: $resourceId,
                severity: $anomaly->getSeverity()->value,
                score: $result->score
            ));
        }

        return $result;
    }

    /**
     * Batch detect anomalies for multiple devices
     */
    public function batchDetectNetworkAnomalies(array $deviceMetrics): array
    {
        $results = [];

        foreach ($deviceMetrics as $deviceId => $metrics) {
            try {
                $results[$deviceId] = $this->detectNetworkAnomaly($deviceId, $metrics);
            } catch (\Exception $e) {
                $results[$deviceId] = new AnomalyResult(
                    anomalyId: '',
                    isAnomaly: false,
                    score: 0,
                    severity: AnomalySeverity::LOW->value,
                    type: 'error',
                    description: $e->getMessage()
                );
            }
        }

        return $results;
    }

    /**
     * Get active anomalies
     */
    public function getActiveAnomalies(string $type = null, int $limit = 100): array
    {
        return $this->anomalyRepository->findActive($type, $limit);
    }

    /**
     * Get anomaly statistics
     */
    public function getAnomalyStats(string $type = null): array
    {
        $anomalies = $this->anomalyRepository->findRecent(1000);

        if ($type) {
            $anomalies = array_filter($anomalies, fn($a) => $a->detectorType === $type);
        }

        $critical = count(array_filter($anomalies, fn($a) => $a->getSeverity() === AnomalySeverity::CRITICAL));
        $high = count(array_filter($anomalies, fn($a) => $a->getSeverity() === AnomalySeverity::HIGH));
        $medium = count(array_filter($anomalies, fn($a) => $a->getSeverity() === AnomalySeverity::MEDIUM));
        $low = count(array_filter($anomalies, fn($a) => $a->getSeverity() === AnomalySeverity::LOW));

        $acknowledged = count(array_filter($anomalies, fn($a) => $a->isAcknowledged()));
        $unacknowledged = count($anomalies) - $acknowledged;

        return [
            'total' => count($anomalies),
            'by_severity' => [
                'critical' => $critical,
                'high' => $high,
                'medium' => $medium,
                'low' => $low,
            ],
            'acknowledged' => $acknowledged,
            'unacknowledged' => $unacknowledged,
        ];
    }

    /**
     * Acknowledge an anomaly
     */
    public function acknowledgeAnomaly(Uuid $anomalyId, Uuid $acknowledgedBy): void
    {
        $anomaly = $this->anomalyRepository->findById($anomalyId);

        if (!$anomaly) {
            throw new \DomainException('Anomaly not found');
        }

        $anomaly->acknowledge($acknowledgedBy);
        $this->anomalyRepository->save($anomaly);
    }
}

class AnomalyDetector
{
    /**
     * Detect anomalies using statistical methods or ML model
     */
    public function detect(
        string $type,
        string $entityId,
        array $metrics,
        ?\Src\Domain\AI\AIModel $model = null
    ): AnomalyResult {
        $anomalyId = Uuid::generate()->toString();

        // Use statistical anomaly detection
        $result = $this->statisticalDetection($type, $metrics);

        if ($result['is_anomaly']) {
            return new AnomalyResult(
                anomalyId: $anomalyId,
                isAnomaly: true,
                score: $result['score'],
                severity: $this->scoreToSeverity($result['score']),
                type: $result['type'] ?? $type,
                description: $result['description'] ?? "Anomaly detected in {$type}",
                details: $result['details'] ?? []
            );
        }

        return new AnomalyResult(
            anomalyId: $anomalyId,
            isAnomaly: false,
            score: $result['score'],
            severity: AnomalySeverity::LOW->value,
            type: $type
        );
    }

    /**
     * Statistical anomaly detection
     */
    private function statisticalDetection(string $type, array $metrics): array
    {
        $isAnomaly = false;
        $score = 0.0;
        $type = 'unknown';
        $description = '';
        $details = [];

        switch ($type) {
            case 'network':
                $result = $this->detectNetworkAnomaly($metrics);
                break;
            case 'capacity':
                $result = $this->detectCapacityAnomaly($metrics);
                break;
            default:
                $result = $this->detectGeneralAnomaly($metrics);
        }

        return $result;
    }

    private function detectNetworkAnomaly(array $metrics): array
    {
        $anomalies = [];
        $details = [];

        // Check CPU usage
        if (isset($metrics['cpu_usage'])) {
            $cpu = $metrics['cpu_usage'];
            if ($cpu > 90) {
                $anomalies[] = 'high_cpu';
                $details['cpu_usage'] = $cpu;
            } elseif ($cpu > 75) {
                $anomalies[] = 'elevated_cpu';
                $details['cpu_usage'] = $cpu;
            }
        }

        // Check memory usage
        if (isset($metrics['memory_usage'])) {
            $memory = $metrics['memory_usage'];
            if ($memory > 90) {
                $anomalies[] = 'high_memory';
                $details['memory_usage'] = $memory;
            }
        }

        // Check latency
        if (isset($metrics['latency'])) {
            $latency = $metrics['latency'];
            if ($latency > 200) {
                $anomalies[] = 'high_latency';
                $details['latency'] = $latency;
            }
        }

        // Check packet loss
        if (isset($metrics['packet_loss'])) {
            $packetLoss = $metrics['packet_loss'];
            if ($packetLoss > 1) {
                $anomalies[] = 'packet_loss';
                $details['packet_loss'] = $packetLoss;
            }
        }

        // Check bandwidth utilization
        if (isset($metrics['bandwidth_utilization'])) {
            $bw = $metrics['bandwidth_utilization'];
            if ($bw > 95) {
                $anomalies[] = 'bandwidth_saturation';
                $details['bandwidth_utilization'] = $bw;
            }
        }

        // Calculate anomaly score
        $score = 0.0;
        if (count($anomalies) > 0) {
            $score = min(1.0, count($anomalies) * 0.25);
            
            // Increase score for critical anomalies
            if (in_array('high_cpu', $anomalies) || in_array('packet_loss', $anomalies)) {
                $score = max($score, 0.8);
            }
        }

        return [
            'is_anomaly' => count($anomalies) > 0,
            'score' => $score,
            'type' => !empty($anomalies) ? implode('_', $anomalies) : 'normal',
            'description' => !empty($anomalies) ? 'Network anomaly detected: ' . implode(', ', $anomalies) : 'Normal network metrics',
            'details' => $details,
        ];
    }

    private function detectCapacityAnomaly(array $metrics): array
    {
        $anomalies = [];
        $details = [];

        // Check OLT capacity
        if (isset($metrics['olt_capacity'])) {
            $capacity = $metrics['olt_capacity'];
            if ($capacity > 90) {
                $anomalies[] = 'olt_capacity_critical';
                $details['olt_capacity'] = $capacity;
            } elseif ($capacity > 75) {
                $anomalies[] = 'olt_capacity_warning';
                $details['olt_capacity'] = $capacity;
            }
        }

        // Check ODP capacity
        if (isset($metrics['odp_capacity'])) {
            $capacity = $metrics['odp_capacity'];
            if ($capacity > 90) {
                $anomalies[] = 'odp_capacity_critical';
                $details['odp_capacity'] = $capacity;
            }
        }

        // Check fiber utilization
        if (isset($metrics['fiber_utilization'])) {
            $util = $metrics['fiber_utilization'];
            if ($util > 85) {
                $anomalies[] = 'fiber_saturation';
                $details['fiber_utilization'] = $util;
            }
        }

        $score = 0.0;
        if (count($anomalies) > 0) {
            $score = min(1.0, count($anomalies) * 0.35);
        }

        return [
            'is_anomaly' => count($anomalies) > 0,
            'score' => $score,
            'type' => !empty($anomalies) ? implode('_', $anomalies) : 'normal',
            'description' => !empty($anomalies) ? 'Capacity anomaly: ' . implode(', ', $anomalies) : 'Normal capacity metrics',
            'details' => $details,
        ];
    }

    private function detectGeneralAnomaly(array $metrics): array
    {
        // Z-score based detection
        $values = array_values($metrics);
        if (count($values) < 3) {
            return ['is_anomaly' => false, 'score' => 0.0, 'type' => 'insufficient_data'];
        }

        $mean = array_sum($values) / count($values);
        $variance = array_reduce($values, fn($carry, $val) => $carry + pow($val - $mean, 2), 0) / count($values);
        $stdDev = sqrt($variance);

        if ($stdDev == 0) {
            return ['is_anomaly' => false, 'score' => 0.0, 'type' => 'no_variance'];
        }

        $zScores = array_map(fn($val) => abs(($val - $mean) / $stdDev), $values);
        $maxZScore = max($zScores);

        if ($maxZScore > 3) {
            return [
                'is_anomaly' => true,
                'score' => min(1.0, $maxZScore / 5),
                'type' => 'statistical_outlier',
                'description' => 'Statistical anomaly detected',
                'details' => ['max_z_score' => $maxZScore],
            ];
        }

        return ['is_anomaly' => false, 'score' => $maxZScore / 5, 'type' => 'normal'];
    }

    private function scoreToSeverity(float $score): string
    {
        if ($score >= 0.9) {
            return AnomalySeverity::CRITICAL->value;
        } elseif ($score >= 0.7) {
            return AnomalySeverity::HIGH->value;
        } elseif ($score >= 0.5) {
            return AnomalySeverity::MEDIUM->value;
        }

        return AnomalySeverity::LOW->value;
    }
}
