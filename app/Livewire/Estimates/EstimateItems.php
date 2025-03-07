<?php

namespace App\Livewire\Estimates;

use Livewire\Component;
use App\Models\Item;
use App\Models\EstimateItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\LaborRate;

class EstimateItems extends Component
{
    public $estimate;
    public $items;
    
    // For adding new items
    public $selectedItem = '';
    public $itemQuantity = 1;
    
    // For editing items
    public $editingItemIndex = null;
    public $editingItemQuantity = null;
    
    public function mount($estimate = null)
    {
        $this->items = collect();
        
        if ($estimate) {
            $this->estimate = $estimate;
            $this->items = collect($estimate->items);
        }
    }

    public function addItem()
    {
        if (!$this->selectedItem || $this->itemQuantity <= 0) {
            return;
        }

        $item = Item::find($this->selectedItem);
        if (!$item) {
            return;
        }

        \Log::info('EstimateItems - Creating new item:', [
            'selected_item_id' => $this->selectedItem,
            'quantity' => $this->itemQuantity,
            'estimate_id' => $this->estimate ? $this->estimate->id : null
        ]);

        try {
            DB::transaction(function () use ($item) {
                // Get default labor rate
                $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                    ->where('is_default', true)
                    ->first();
                    
                if (!$laborRate) {
                    // Fallback to any labor rate if no default is set
                    $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                        ->first();
                }
                
                if (!$laborRate) {
                    throw new \Exception('No labor rate found. Please create at least one labor rate.');
                }

                $estimateItem = new EstimateItem([
                    'tenant_id' => auth()->user()->current_tenant_id,
                    'estimate_id' => $this->estimate->id,
                    'item_id' => $item->id,
                    'original_item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'unit_of_measure' => $item->unit_of_measure,
                    'quantity' => $this->itemQuantity,
                    'material_cost_rate' => $item->material_cost_rate,
                    'material_charge_rate' => $item->material_charge_rate,
                    'labor_units' => $item->labor_units,
                    'labor_rate_id' => $laborRate->id,
                    'original_cost_rate' => $item->material_cost_rate,
                    'original_charge_rate' => $item->material_charge_rate,
                ]);

                $estimateItem->save();

                // Refresh the estimate and items collection
                $this->estimate->refresh();
                $this->items = collect($this->estimate->items()->get());
                
                // Reset form
                $this->selectedItem = '';
                $this->itemQuantity = 1;
                
                // Emit the update event
                $this->dispatch('estimate-items-updated', [
                    'items' => $this->items->toArray()
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Error adding item to estimate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding item: ' . $e->getMessage());
        }
    }

    public function editItem($index)
    {
        $this->editingItemIndex = $index;
        $this->editingItemQuantity = $this->items[$index]->quantity;
    }

    public function updateItem()
    {
        if ($this->editingItemIndex === null || $this->editingItemQuantity <= 0) {
            return;
        }

        $this->items[$this->editingItemIndex]->quantity = $this->editingItemQuantity;
        
        $this->cancelEditItem();
        $this->emitItemsChanged();
    }

    public function cancelEditItem()
    {
        $this->editingItemIndex = null;
        $this->editingItemQuantity = null;
    }

    public function removeItem($index)
    {
        $this->items = $this->items->forget($index)->values();
        $this->emitItemsChanged();
    }

    protected function emitItemsChanged()
    {
        \Log::info('EstimateItems - Emitting items changed:', [
            'items_count' => $this->items->count(),
            'items' => $this->items->map(fn($item) => [
                'id' => $item->id ?? null,
                'estimate_id' => $item->estimate_id ?? null,
                'name' => $item->name ?? null
            ])->toArray()
        ]);

        $this->dispatch('estimate-items-updated', [
            'items' => $this->items->toArray()
        ]);
    }

    public function render()
    {
        $availableItems = Item::orderBy('name')->get();
        
        return view('livewire.estimates.estimate-items', [
            'availableItems' => $availableItems
        ]);
    }
} 