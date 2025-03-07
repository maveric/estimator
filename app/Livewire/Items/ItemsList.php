<?php

namespace App\Livewire\Items;

use App\Models\Item;
use App\Models\LaborRate;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ItemsList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $showInactive = false;
    public $primaryLaborRate;
    public $defaultLaborMarkup;

    public function mount()
    {
        // Get the primary labor rate
        $this->primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->active()
            ->first();
            
        if (!$this->primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }
    }

    public function loadSettings()
    {
        $this->primaryLaborRate = Settings::getPrimaryLaborRate();
        $this->defaultLaborMarkup = Settings::getDefaultLaborMarkup();
    }

    public function render()
    {
        $query = Item::where('tenant_id', auth()->user()->current_tenant_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('categories.id', $this->categoryFilter);
                });
            })
            ->when(!$this->showInactive, function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name');

        return view('livewire.items.items-list', [
            'items' => $query->paginate(10),
            'primaryLaborRate' => $this->primaryLaborRate,
            'defaultLaborMarkup' => $this->defaultLaborMarkup
        ]);
    }

    public function deleteItem($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        session()->flash('message', 'Item deleted successfully.');
    }
} 