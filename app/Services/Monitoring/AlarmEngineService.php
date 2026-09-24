<?php

namespace App\Services\Monitoring;

use Src\Domain\Monitoring\Alarm;
use Src\Domain\Monitoring\AlarmSeverity;
use Src\Domain\Monitoring\AlarmStatus;
use Src\Domain\Monitoring\Incident;
use Src\Domain\Monitoring\IncidentStatus;
use Src\Domain\Monitoring\Events\AlarmTriggeredEvent;
use Src\Domain\Monitoring\Events\IncidentCreatedEvent;
use Illuminate\Support\Facades\Event;

class AlarmEngineService
{
    private array $activeAlarms = [];
    private array $activeIncidents = [];

    public function triggerAlarm(
        string $deviceId,
        string $metricId,
        string $deviceType,
        string $metricName,
        AlarmSeverity $severity,
        string $message,
        float $value,
        float $threshold,
    ): Alarm {
        $alarm = Alarm::create(
            $deviceId,
            $metricId,
            $deviceType,
            $metricName,
            $severity,
            $message,
            $value,
            $threshold,
        );

        $this->activeAlarms[$alarm->id->toString()] = $alarm;

        Event::dispatch(new AlarmTriggeredEvent(
            $alarm->id->toString(),
            $deviceId,
            $metricName,
            $severity,
            $message,
            $value,
            $threshold,
        ));

        $this->correlateAndCreateIncident($alarm);

        return $alarm;
    }

    public function correlateAndCreateIncident(Alarm $alarm): ?Incident
    {
        $relatedAlarms = $this->findRelatedAlarms($alarm);

        if (count($relatedAlarms) >= 1) {
            $incident = Incident::create(
                $alarm->id,
                array_map(fn($a) => $a->id->toString(), $relatedAlarms),
                $this->determineCategory($alarm),
                $alarm->severity,
                "Multiple alarms triggered, likely root cause: {$alarm->message}",
            );

            $this->activeIncidents[$incident->id->toString()] = $incident;

            Event::dispatch(new IncidentCreatedEvent(
                $incident->id->toString(),
                $alarm->id->toString(),
                $incident->relatedAlarmIds,
                $incident->category,
                $incident->severity,
                $incident->description,
            ));

            return $incident;
        }

        return null;
    }

    private function findRelatedAlarms(Alarm $alarm): array
    {
        return array_filter($this->activeAlarms, function ($a) use ($alarm) {
            // Sederhana: Alarms pada device yang sama atau metric yang sama
            return $a->deviceId->toString() === $alarm->deviceId->toString() &&
                   $a->status === AlarmStatus::OPEN;
        });
    }

    private function determineCategory(Alarm $alarm): string
    {
        return match ($alarm->deviceType) {
            'olt' => 'olt_failure',
            'onu' => 'onu_failure',
            'router' => 'router_failure',
            default => 'device_failure',
        };
    }

    public function acknowledgeAlarm(string $alarmId, int $userId): void
    {
        if (!isset($this->activeAlarms[$alarmId])) {
            return;
        }

        $this->activeAlarms[$alarmId]->acknowledge($userId);
    }

    public function resolveAlarm(string $alarmId, int $userId): void
    {
        if (!isset($this->activeAlarms[$alarmId])) {
            return;
        }

        $this->activeAlarms[$alarmId]->resolve($userId);
        $this->checkAndResolveIncidents();
    }

    private function checkAndResolveIncidents(): void
    {
        foreach ($this->activeIncidents as $incident) {
            $allResolved = true;
            foreach ($incident->relatedAlarmIds as $alarmId) {
                if (isset($this->activeAlarms[$alarmId]) && $this->activeAlarms[$alarmId]->status !== AlarmStatus::RESOLVED) {
                    $allResolved = false;
                    break;
                }
            }

            if ($allResolved) {
                $incident->resolve(
                    "All related alarms resolved",
                    "Root cause resolved",
                );
                $incident->close();
            }
        }
    }

    public function getActiveAlarms(): array
    {
        return $this->activeAlarms;
    }

    public function getActiveIncidents(): array
    {
        return $this->activeIncidents;
    }
}
