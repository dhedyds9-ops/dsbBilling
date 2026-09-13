<?php
namespace App\Livewire\ISP\Technician\Material;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.technician-app')]
class Index extends Component {
    public function mount() {
        $this->breadcrumbs = [['label' => 'Technician Dashboard', 'url' => route('technician.dashboard')], ['label' => 'Material Consumption', 'url' => '#']];
    }
    public function render() {
        return view('livewire.isp.technician.material.index');
    }
}
