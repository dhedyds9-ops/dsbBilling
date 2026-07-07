<?php

namespace App\Livewire\Gis;

use Livewire\Component;

class LayerControl extends Component
{
    public array $layers = [
        'olt' => true,
        'odp' => true,
        'onu' => false,
        'fiber' => true,
        'customer' => false,
        'heatmap' => false,
        'alarm' => true,
    ];

    protected $listeners = [
        'toggleLayer' => 'onToggleLayer',
    ];

    public function onToggleLayer($layerName)
    {
        if (isset($this->layers[$layerName])) {
            $this->layers[$layerName] = !$this->layers[$layerName];
            $this->dispatch('layersUpdated', layers: $this->layers);
        }
    }

    public function toggleLayer($layerName)
    {
        $this->onToggleLayer($layerName);
    }

    public function showAll()
    {
        foreach ($this->layers as $key => $value) {
            $this->layers[$key] = true;
        }
        $this->dispatch('layersUpdated', layers: $this->layers);
    }

    public function hideAll()
    {
        foreach ($this->layers as $key => $value) {
            $this->layers[$key] = false;
        }
        $this->dispatch('layersUpdated', layers: $this->layers);
    }

    public function render()
    {
        return view('livewire.gis.layer-control');
    }
}
