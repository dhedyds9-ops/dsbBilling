<?php
namespace App\Livewire\Isp\Technician\MyJobs;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.technician-app')]
class Show extends Component {
    public $jobId;
    public array $breadcrumbs = [];

    public function mount($job = null, $id = null) {
        $this->jobId = $job ?? $id;
        $this->breadcrumbs = [
            ['label' => 'Technician Dashboard', 'url' => route('technician.dashboard')],
            ['label' => 'My Jobs', 'url' => route('technician.my-jobs.index')],
            ['label' => 'Detail Job', 'url' => '#']
        ];
    }
    public function startJob() { /* TODO */ }
    public function completeJob() { /* TODO */ }
    public function render() {
        return view('livewire.isp.technician.my-jobs.show');
    }
}
