<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $mode = 'create';
    public $item = null;

    public function render()
    {
        return view('livewire.items.item-form');
    }
}; ?>

<div>
    <h1>Test Form</h1>
    <livewire:items.item-form :mode="$mode" :item="$item" />
</div>