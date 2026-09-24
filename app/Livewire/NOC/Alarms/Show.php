<?php

namespace App\Livewire\NOC\Alarms;

use App\Livewire\AdminComponent;
use App\Models\Alarm;
use Livewire\Attributes\Computed;

class Show extends AdminComponent
{
    public int $id;
    public string $ackNote = '';
    public string $resolveNote = '';

    public function configure(): void
    {
        }

    public function mount($alarm = null): void
    {
        parent::mount();
        $this->id           = (int) $alarm;
        $this->activeModule = 'noc';
        $this->activePage   = 'alarms';
    }

    #[Computed]
    public function alarm(): Alarm
    {
        return Alarm::with([
            'acknowledgedBy:id,name',
            'source',
        ])->findOrFail($this->id);
    }

    public function acknowledge(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasPermission(\App\Enums\UserPermission::NocAlarmsManage->value)) {
            $this->dispatch('toast', type: 'error', message: 'Anda tidak punya izin acknowledge alarm (butuh noc.alarms.manage).');
            return;
        }

        if ($this->alarm->status === 'resolved') {
            return;
        }

        $this->alarm->update([
            'status'            => 'acknowledged',
            'acknowledged_by'   => auth()->id(),
            'acknowledged_note' => $this->ackNote ?: null,
            'acknowledged_at'   => now(),
        ]);

        // Audit
        app(\App\Services\Auth\AuditLogService::class)->log(
            event: 'noc.alarm.acknowledged',
            auditable: $this->alarm,
            oldValues: ['status' => 'open'],
            newValues: ['status' => 'acknowledged', 'acknowledged_by' => auth()->id()],
            notes: $this->ackNote ?: 'Alarm acknowledged by operator',
        );

        $this->ackNote = '';
        $this->dispatch('toast', type: 'success', message: 'Alarm acknowledged.');
    }

    public function resolve(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasPermission(\App\Enums\UserPermission::NocAlarmsManage->value)) {
            $this->dispatch('toast', type: 'error', message: 'Anda tidak punya izin resolve alarm (butuh noc.alarms.manage).');
            return;
        }

        $oldStatus = $this->alarm->status;
        $update = [
            'status'      => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ];

        if ($this->alarm->status !== 'acknowledged') {
            $update['acknowledged_by']   = auth()->id();
            $update['acknowledged_note'] = $this->resolveNote ?: 'Auto-acknowledge on resolve';
            $update['acknowledged_at']   = now();
        } elseif ($this->resolveNote) {
            $update['acknowledged_note'] = $this->resolveNote;
        }

        $this->alarm->update($update);

        // Audit
        app(\App\Services\Auth\AuditLogService::class)->log(
            event: 'noc.alarm.resolved',
            auditable: $this->alarm,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => 'resolved', 'resolved_by' => auth()->id()],
            notes: $this->resolveNote ?: 'Alarm resolved by operator',
        );

        $this->resolveNote = '';
        $this->dispatch('toast', type: 'success', message: 'Alarm resolved.');
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        $user = auth()->user();
        $canManage = $user ? $user->hasPermission(\App\Enums\UserPermission::NocAlarmsManage->value) : false;

        return view('livewire.noc.alarms.show', [
            'alarm'     => $this->alarm,
            'canManage' => $canManage,
        ]);
    }
}



