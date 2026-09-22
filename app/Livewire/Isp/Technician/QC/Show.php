<?php
namespace App\Livewire\ISP\Technician\QC;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.technician-app')]
class Show extends Component {
    public $jobId;
    public function mount($id = null) {
        $this->jobId = $id;
        $this->breadcrumbs = [['label' => 'Technician Dashboard', 'url' => route('technician.dashboard')], ['label' => 'Quality Control', 'url' => '#']];
    }
    public function render() {
        return view('livewire.isp.technician.qc.show');
    }
}
