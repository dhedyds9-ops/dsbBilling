<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class GisLegend extends Component
{
    public array $layers = [
        'olt' => ['visible' => true, 'label' => 'OLT', 'color' => '#3B82F6', 'icon' => 'server'],
        'odp' => ['visible' => true, 'label' => 'ODP', 'color' => '#10B981', 'icon' => 'box'],
        'onu' => ['visible' => true, 'label' => 'ONU', 'color' => '#F59E0B', 'icon' => 'wifi'],
        'fiber' => ['visible' => true, 'label' => 'Fiber Cable', 'color' => '#6366F1', 'icon' => 'link'],
        'customer' => ['visible' => false, 'label' => 'Customers', 'color' => '#EC4899', 'icon' => 'users'],
        'alarm' => ['visible' => true, 'label' => 'Alarms', 'color' => '#EF4444', 'icon' => 'alert-triangle'],
    ];

    public array $statusLegend = [
        'active' => ['label' => 'Active', 'color' => '#10B981'],
        'warning' => ['label' => 'Warning', 'color' => '#F59E0B'],
        'critical' => ['label' => 'Critical', 'color' => '#EF4444'],
        'inactive' => ['label' => 'Inactive', 'color' => '#6B7280'],
        'maintenance' => ['label' => 'Maintenance', 'color' => '#8B5CF6'],
    ];

    public array $utilizationLegend = [
        ['range' => '0-50%', 'color' => '#10B981', 'label' => 'Healthy'],
        ['range' => '50-75%', 'color' => '#84CC16', 'label' => 'Normal'],
        ['range' => '75-90%', 'color' => '#F59E0B', 'label' => 'Warning'],
        ['range' => '90-100%', 'color' => '#EF4444', 'label' => 'Critical'],
    ];

    public string $activeTab = 'layers';
    public bool $isCollapsed = false;

    protected $listeners = [
        'toggleLayer' => 'onToggleLayer',
        'layerVisibilityChanged' => 'onLayerVisibilityChanged',
    ];

    public function onToggleLayer($layerName)
    {
        if (isset($this->layers[$layerName])) {
            $this->layers[$layerName]['visible'] = !$this->layers[$layerName]['visible'];
            $this->dispatchLayerUpdate();
        }
    }

    public function onLayerVisibilityChanged($layers)
    {
        $this->layers = $layers;
    }

    public function toggleAllLayers($visibility)
    {
        foreach ($this->layers as $key => $layer) {
            $this->layers[$key]['visible'] = $visibility;
        }
        $this->dispatchLayerUpdate();
    }

    public function dispatchLayerUpdate()
    {
        $visibleLayers = array_keys(array_filter($this->layers, fn($l) => $l['visible']));
        $this->dispatch('layersUpdated', layers: $visibleLayers);
    }

    public function toggleCollapse()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getVisibleLayersProperty()
    {
        return array_keys(array_filter($this->layers, fn($l) => $l['visible']));
    }

    public function getActiveLayerCountProperty()
    {
        return count($this->visibleLayers);
    }

    public function render()
    {
        return view('livewire.gis.components.legend');
    }
}
