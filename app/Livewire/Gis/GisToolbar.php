<?php

namespace App\Livewire\Gis;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class GisToolbar extends Component
{
    public string $activeTool = 'select';
    public bool $isDrawing = false;
    public bool $isMeasuring = false;
    public bool $isFullscreen = false;
    
    public array $tools = [
        'select' => ['icon' => 'cursor', 'label' => 'Select'],
        'pan' => ['icon' => 'move', 'label' => 'Pan'],
        'draw_point' => ['icon' => 'map-pin', 'label' => 'Draw Point'],
        'draw_line' => ['icon' => 'minus', 'label' => 'Draw Line'],
        'draw_polygon' => ['icon' => 'square', 'label' => 'Draw Polygon'],
        'measure' => ['icon' => 'ruler', 'label' => 'Measure'],
        'route' => ['icon' => 'route', 'label' => 'Route'],
    ];

    protected $listeners = [
        'setActiveTool',
    ];

    public function setActiveTool($tool)
    {
        $this->activeTool = $tool;
        
        $this->isDrawing = in_array($tool, ['draw_point', 'draw_line', 'draw_polygon']);
        $this->isMeasuring = $tool === 'measure';
        
        $this->dispatch('toolChanged', tool: $tool);
    }

    public function toggleFullscreen()
    {
        $this->isFullscreen = !$this->isFullscreen;
        $this->dispatch('toggleFullscreen', state: $this->isFullscreen);
    }

    public function zoomIn()
    {
        $this->dispatch('zoomIn');
    }

    public function zoomOut()
    {
        $this->dispatch('zoomOut');
    }

    public function resetView()
    {
        $this->dispatch('resetMapView');
    }

    public function locateMe()
    {
        $this->dispatch('locateUser');
    }

    public function render()
    {
        return view('livewire.gis.components.toolbar');
    }
}
