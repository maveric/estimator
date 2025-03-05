<?php

namespace App\Livewire\Items;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ItemList extends Component
{
    use WithPagination;

    #[On('item-selected')]
    public function handleItemSelected($itemId)
    {
        // For now, let's just redirect to the edit page of the selected item
        return redirect()->route('items.edit', $itemId);
    }

    public function deleteItem(Item $item)
    {
        $item->delete();
        session()->flash('status', 'Item permanently deleted.');
    }

    public function render()
    {
        return view('livewire.items.list', [
            'items' => Item::paginate(10)
        ]);
    }
}
