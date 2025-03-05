<?php

namespace App\Livewire;

use Livewire\Component;

class TestComponent extends Component
{
    public function render()
    {
        return <<<'blade'
            <div>
                <h1>Test Component</h1>
            </div>
        blade;
    }
}