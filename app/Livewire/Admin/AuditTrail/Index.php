<?php

namespace App\Livewire\Admin\AuditTrail;

use App\Livewire\Admin\BaseAdminComponent;
use App\Models\AuditLog;

class Index extends BaseAdminComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'audit-trail';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Audit Trail'],
        ];
    }

    public function render()
    {
        $query = AuditLog::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('event', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%');
            });
        }

        $auditLogs = $query->orderBy($this->sortField, $this->sortDirection)
                      ->paginate($this->perPage);

        return view('livewire.admin.audit-trail.index', compact('auditLogs'));
    }
}
