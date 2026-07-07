<?php

namespace Src\Domain\Outage\Services;

use Src\Domain\Outage\RootCause;
use Src\Domain\Outage\Repositories\RootCauseRepositoryInterface;
use Src\Domain\Outage\Repositories\AffectedNodeRepositoryInterface;
use Src\Domain\Outage\Enums\NodeType;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RootCauseAnalysisService
{
    private const CAUSE_CODES = [
        'POWER_FAILURE' => [
            'weight' => 30,
            'indicators' => ['power_status', 'ups_battery', 'generator_status'],
            'similar_patterns' => ['power_outage', 'ups_failure', 'generator_failure']
        ],
        'FIBER_CUT' => [
            'weight' => 25,
            'indicators' => ['fiber_integrity', 'signal_loss', 'distance_to_break'],
            'similar_patterns' => ['backhoe_cut', 'rodent_damage', 'weather_damage']
        ],
        'HARDWARE_FAILURE' => [
            'weight' => 20,
            'indicators' => ['device_health', 'temperature', 'error_logs'],
            'similar_patterns' => ['olt_failure', 'switch_failure', 'splitter_failure']
        ],
        'CONFIGURATION_ERROR' => [
            'weight' => 15,
            'indicators' => ['config_version', 'recent_changes', 'validation_status'],
            'similar_patterns' => ['vlan_misconfig', 'spanning_tree', 'routing_error']
        ],
        'SOFTWARE_BUG' => [
            'weight' => 5,
            'indicators' => ['software_version', 'crash_logs', 'memory_usage'],
            'similar_patterns' => ['firmware_crash', 'daemon_failure', 'memory_leak']
        ],
        'OVERLOAD' => [
            'weight' => 3,
            'indicators' => ['bandwidth_usage', 'cpu_usage', 'concurrent_sessions'],
            'similar_patterns' => ['bandwidth_exceeded', 'cpu_overload', 'session_limit']
        ],
        'WEATHER' => [
            'weight' => 1,
            'indicators' => ['weather_alerts', 'temperature', 'humidity'],
            'similar_patterns' => ['lightning', 'flood', 'wind_damage']
        ],
        'HUMAN_ERROR' => [
            'weight' => 1,
            'indicators' => ['recent_changes', 'change_logs', 'approval_status'],
            'similar_patterns' => ['misconfiguration', 'accidental_shutdown', 'cable_disconnect']
        ]
    ];

    public function __construct(
        private RootCauseRepositoryInterface $rootCauseRepository,
        private AffectedNodeRepositoryInterface $nodeRepository
    ) {}

    public function analyze(
        Uuid $outageId,
        NodeType $affectedNodeType,
        string $affectedNodeId,
        string $affectedNodeName,
        array $metrics = []
    ): RootCause {
        $causeCode = $this->determineCauseCode($metrics);
        $causeDescription = $this->getCauseDescription($causeCode);
        $rootCause = $this->generateRootCauseStatement($causeCode, $affectedNodeType);
        $impactDescription = $this->generateImpactDescription($causeCode, $metrics);

        $rootCauseEntity = RootCause::create(
            Uuid::generate(),
            $outageId,
            $affectedNodeType,
            $affectedNodeId,
            $affectedNodeName,
            $causeCode,
            $causeDescription,
            $rootCause,
            $impactDescription
        );

        $this->rootCauseRepository->save($rootCauseEntity);

        $this->addSupportingEvidence($rootCauseEntity, $metrics);
        $this->findSimilarCases($rootCauseEntity);

        return $rootCauseEntity;
    }

    public function identifyRootCause(
        RootCause $rootCause,
        string $identifiedBy,
        int $confidenceScore = 100
    ): void {
        $rootCause->identify($identifiedBy, $confidenceScore);
        $this->rootCauseRepository->save($rootCause);

        $rootCauseNode = $this->nodeRepository->findRootCauseNode($rootCause->outageId);
        if ($rootCauseNode) {
            $rootCauseNode->markAsRootCause();
            $this->nodeRepository->save($rootCauseNode);
        }
    }

    private function determineCauseCode(array $metrics): string
    {
        $scores = [];

        foreach (self::CAUSE_CODES as $code => $config) {
            $score = 0;

            foreach ($config['indicators'] as $indicator) {
                if (isset($metrics[$indicator])) {
                    $score += $config['weight'];
                }
            }

            if (isset($metrics['cause_code_hint']) && $metrics['cause_code_hint'] === $code) {
                $score += 50;
            }

            $scores[$code] = $score;
        }

        if (empty($scores) || max($scores) === 0) {
            return 'HARDWARE_FAILURE';
        }

        return array_keys($scores, max($scores))[0];
    }

    private function getCauseDescription(string $causeCode): string
    {
        return match($causeCode) {
            'POWER_FAILURE' => 'Gangguan catu daya pada infrastruktur jaringan',
            'FIBER_CUT' => 'Pemutusan kabel fiber optic',
            'HARDWARE_FAILURE' => 'Kegagalan perangkat keras',
            'CONFIGURATION_ERROR' => 'Kesalahan konfigurasi jaringan',
            'SOFTWARE_BUG' => 'Bug atau masalah perangkat lunak',
            'OVERLOAD' => 'Beban berlebih pada sistem',
            'WEATHER' => 'Pengaruh cuaca buruk',
            'HUMAN_ERROR' => 'Kesalahan manusia dalam pengoperasian',
            default => 'Penyebab tidak diketahui',
        };
    }

    private function generateRootCauseStatement(string $causeCode, NodeType $nodeType): string
    {
        return match($causeCode) {
            'POWER_FAILURE' => "Kegagalan catu daya menyebabkan {$nodeType->label()} tidak dapat beroperasi",
            'FIBER_CUT' => "Kabel fiber terputus yang menghubungkan ke {$nodeType->label()}",
            'HARDWARE_FAILURE' => "Perangkat {$nodeType->label()} mengalami kegagalan komponen internal",
            'CONFIGURATION_ERROR' => "Konfigurasi yang salah pada {$nodeType->label()}",
            'SOFTWARE_BUG' => "Bug pada perangkat lunak {$nodeType->label()}",
            'OVERLOAD' => "Beban berlebih pada {$nodeType->label()}",
            'WEATHER' => "Kondisi cuaca buruk mempengaruhi operasional {$nodeType->label()}",
            'HUMAN_ERROR' => "Kesalahan operasional pada {$nodeType->label()}",
            default => "Gangguan pada {$nodeType->label()}",
        };
    }

    private function generateImpactDescription(string $causeCode, array $metrics): string
    {
        $affectedNodes = $metrics['affected_nodes_count'] ?? 0;
        $affectedCustomers = $metrics['affected_customers_count'] ?? 0;

        return match($causeCode) {
            'POWER_FAILURE' => "{$affectedCustomers} pelanggan kehilangan layanan karena pemadaman catu daya",
            'FIBER_CUT' => "{$affectedCustomers} pelanggan terputus koneksi karena kabel fiber rusak",
            'HARDWARE_FAILURE' => "{$affectedNodes} node tidak berfungsi karena kerusakan hardware",
            'CONFIGURATION_ERROR' => "{$affectedCustomers} pelanggan tidak dapat mengakses layanan karena salah konfigurasi",
            'SOFTWARE_BUG' => "{$affectedNodes} perangkat mengalami gangguan karena bug software",
            'OVERLOAD' => "Layanan degrade untuk {$affectedCustomers} pelanggan karena overload",
            'WEATHER' => "{$affectedCustomers} pelanggan terdampak akibat cuaca buruk",
            'HUMAN_ERROR' => "{$affectedCustomers} pelanggan mengalami gangguan akibat kesalahan operasional",
            default => "{$affectedCustomers} pelanggan terdampak",
        };
    }

    private function addSupportingEvidence(RootCause $rootCause, array $metrics): void
    {
        foreach ($metrics as $key => $value) {
            if (is_scalar($value)) {
                $rootCause->addEvidence(
                    'metric',
                    "Metric {$key}: {$value}",
                    ['metric_key' => $key, 'metric_value' => $value]
                );
            }
        }
    }

    private function findSimilarCases(RootCause $rootCause): void
    {
        $similarCauses = $this->rootCauseRepository->findSimilarRootCauses(
            $rootCause->causeCode,
            5
        );

        foreach ($similarCauses as $similar) {
            $rootCause->addSimilarCase(
                $similar->id->value,
                $similar->causeDescription,
                $similar->getRecommendedAction()
            );
        }

        $this->rootCauseRepository->save($rootCause);
    }

    public function getCauseStatistics(): array
    {
        $allCauses = [];
        $causeCounts = [];

        foreach (array_keys(self::CAUSE_CODES) as $code) {
            $causes = $this->rootCauseRepository->findByCauseCode($code);
            $causeCounts[$code] = count($causes);
            $allCauses = array_merge($allCauses, $causes);
        }

        arsort($causeCounts);

        return [
            'total_analyzed' => count($allCauses),
            'by_cause_code' => $causeCounts,
            'most_common' => array_keys($causeCounts)[0] ?? null,
        ];
    }

    public function getRecommendedActions(string $causeCode): array
    {
        return match($causeCode) {
            'POWER_FAILURE' => [
                'Check power supply status',
                'Verify UPS battery level',
                'Test generator functionality',
                'Inspect power cables',
                'Contact power company if needed'
            ],
            'FIBER_CUT' => [
                'Locate fiber break point using OTDR',
                'Dispatch field team',
                'Prepare splice equipment',
                'Notify affected customers',
                'Schedule repair window'
            ],
            'HARDWARE_FAILURE' => [
                'Check device logs for errors',
                'Verify warranty status',
                'Prepare replacement hardware',
                'Schedule maintenance window',
                'Update firmware after replacement'
            ],
            'CONFIGURATION_ERROR' => [
                'Review recent configuration changes',
                'Compare with backup configuration',
                'Rollback if necessary',
                'Document correct configuration',
                'Test after configuration'
            ],
            'SOFTWARE_BUG' => [
                'Check for known issues',
                'Review software version',
                'Apply patch if available',
                'Consider rollback',
                'Report to vendor if new bug'
            ],
            'OVERLOAD' => [
                'Check current bandwidth usage',
                'Identify bandwidth hogs',
                'Implement traffic shaping',
                'Plan capacity upgrade',
                'Consider load balancing'
            ],
            'WEATHER' => [
                'Monitor weather conditions',
                'Check infrastructure damage',
                'Wait for conditions to improve if safety concern',
                'Inspect after storm passes',
                'Document damage for insurance'
            ],
            'HUMAN_ERROR' => [
                'Review change logs',
                'Identify responsible person',
                'Provide retraining if needed',
                'Update procedures',
                'Implement safeguards'
            ],
            default => [
                'Investigate further',
                'Collect more data',
                'Consult with experts'
            ]
        };
    }
}
