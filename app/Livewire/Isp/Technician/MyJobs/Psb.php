<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use App\Models\CRM\Installation;
use App\Models\Support\Ticket;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

#[Layout('layouts.technician-app')]
class Psb extends Component
{
    use WithPagination;

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin', 'admin', 'manager']));

        $hasTicketTechIds = \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'technician_ids');
        $hasInstTechIds = \Illuminate\Support\Facades\Schema::hasColumn('installations', 'technician_ids');

        $tickets = Ticket::withoutGlobalScopes()
            ->with([
                'customer' => fn ($q) => $q->withoutGlobalScopes()->with('reseller'),
                'reseller' => fn ($q) => $q->withoutGlobalScopes(),
            ])
            ->whereIn('category', ['psb', 'installation'])
            ->when(!$isAdmin, function ($q) use ($user, $hasTicketTechIds) {
                $q->where(function ($sub) use ($user, $hasTicketTechIds) {
                    $sub->where('assigned_to', $user->id);
                    if ($hasTicketTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $user->id);
                    }
                });
            })
            ->whereIn('status', ['open', 'in_progress', 'assigned'])
            ->latest()
            ->get();

        $installations = Installation::withoutGlobalScopes()
            ->with([
                'customer' => fn ($q) => $q->withoutGlobalScopes()->with('reseller'),
                'survey.prospect',
            ])
            ->when(!$isAdmin, function ($q) use ($user, $hasInstTechIds) {
                $q->where(function ($sub) use ($user, $hasInstTechIds) {
                    $sub->where('technician_id', $user->id)
                        ->orWhere('assigned_to', $user->id);
                    if ($hasInstTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $user->id);
                    }
                });
            })
            ->whereIn('status', ['pending', 'scheduled', 'in_progress', 'assigned'])
            ->latest()
            ->get();

        $items = collect();

        foreach ($tickets as $t) {
            $items->push((object) [
                'id' => $t->id,
                'source' => 'ticket',
                'type_label' => 'Tiket PSB',
                'badge_class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300',
                'uuid' => $t->uuid ?? ('TIKET-PSB-' . $t->id),
                'customer_id' => $t->customer_id,
                'customer_name' => $t->customer?->name ?? 'Pelanggan #' . $t->customer_id,
                'customer_phone' => $t->customer?->phone ?? '-',
                'customer_address' => $t->customer?->address ?? $t->description ?? 'Alamat tidak tersedia',
                'reseller_name' => $t->reseller?->name ?? $t->customer?->reseller?->name ?? null,
                'description' => $t->description,
                'created_at' => $t->created_at,
                'wizard_url' => route('technician.installation.wizard', ['ticket' => $t->id, 'customer_id' => $t->customer_id]),
            ]);
        }

        foreach ($installations as $inst) {
            $custName = !empty($inst->customer_name) ? $inst->customer_name : ($inst->customer?->name ?? ($inst->survey?->prospect?->name ?? 'Pelanggan #' . $inst->customer_id));
            $custPhone = $inst->customer?->phone ?? ($inst->survey?->prospect?->phone ?? '-');
            $custAddr = $inst->installation_address ?? ($inst->customer?->address ?? ($inst->survey?->prospect?->address ?? 'Alamat pemasangan'));

            $items->push((object) [
                'id' => $inst->id,
                'source' => 'installation',
                'type_label' => 'SPK CRM',
                'badge_class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300',
                'uuid' => $inst->uuid ?? ('SPK-INST-' . $inst->id),
                'customer_id' => $inst->customer_id,
                'customer_name' => $custName,
                'customer_phone' => $custPhone,
                'customer_address' => $custAddr,
                'reseller_name' => $inst->customer?->reseller?->name ?? null,
                'description' => $inst->notes,
                'created_at' => $inst->scheduled_at ?? $inst->created_at,
                'wizard_url' => route('technician.installation.wizard', ['installation_id' => $inst->id, 'customer_id' => $inst->customer_id]),
            ]);
        }

        $sortedItems = $items->sortByDesc('created_at')->values();

        $page = request()->get('page', 1);
        $perPage = 10;
        $paginated = new LengthAwarePaginator(
            $sortedItems->forPage($page, $perPage),
            $sortedItems->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.isp.technician.my-jobs.psb', ['jobs' => $paginated]);
    }
}
