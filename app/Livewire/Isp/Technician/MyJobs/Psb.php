<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use App\Models\Support\Ticket;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.technician-app')]
class Psb extends Component
{
    use WithPagination;

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin', 'admin', 'manager']));

        $hasTicketTechIds = \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'technician_ids');

        $jobs = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->whereIn('category', ['psb', 'installation'])
            ->when(!$isAdmin, function ($q) use ($user, $hasTicketTechIds) {
                $q->where(function ($sub) use ($user, $hasTicketTechIds) {
                    $sub->where('assigned_to', $user->id);
                    if ($hasTicketTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $user->id);
                    }
                });
            })
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->paginate(10);

        return view('livewire.isp.technician.my-jobs.psb', ['jobs' => $jobs]);
    }
}
