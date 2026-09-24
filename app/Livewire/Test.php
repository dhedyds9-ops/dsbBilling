<?php

namespace App\Livewire;

use Livewire\Component;

class Test extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <h1>Count: {{ $count }}</h1>
            <button wire:click="increment" class="px-4 py-2 bg-blue-500 text-white rounded">Increment</button>
        </div>
        HTML;
    }
}
