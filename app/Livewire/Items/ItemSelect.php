<?php

namespace App\Livewire\Items;

use App\Models\Item;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ItemSelect extends Component
{
    public $search = '';
    public $selectedItemId = null;
    public $showDropdown = false;
    
    #[Computed]
    public function items()
    {
        return Item::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function updatedSearch()
    {
        $this->showDropdown = true;
    }

    public function selectItem($itemId)
    {
        $this->selectedItemId = $itemId;
        $this->dispatch('item-selected', itemId: $itemId);
        $this->showDropdown = false;
    }

    #[On('clear-selection')]
    public function clearSelection()
    {
        $this->selectedItemId = null;
        $this->search = '';
    }

    public function render()
    {
        return view('livewire.items.select');
    }
}
