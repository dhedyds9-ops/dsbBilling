<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use App\Models\Support\Ticket;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.technician-app')]
class Maintenance extends Component
{
    use WithPagination;

    public function render()
    {
        $jobs = Ticket::with('customer')
            ->where('category', 'maintenance')
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->paginate(10);

        return view('livewire.isp.technician.my-jobs.maintenance', ['jobs' => $jobs]);
    }
}

