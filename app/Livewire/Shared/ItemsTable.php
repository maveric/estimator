<?php

namespace App\Livewire\Shared;

use Livewire\Component;
use App\Models\Item;
use App\Models\EstimateItem;
use App\Models\LaborRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemsTable extends Component
{
    public $parentType; // 'estimate' or 'assembly'
    public $parentId;
    public $items;
    
    // For adding new items
    public $selectedItem = '';
    public $itemQuantity = 1;
    
    // For editing items
    public $editingItemIndex = null;
    public $editingItemQuantity = null;

    protected $listeners = ['refreshItems' => '$refresh'];

    public function mount($parentType, $parentId, $items = null)
    {
        $this->parentType = $parentType;
        $this->parentId = $parentId;
        $this->items = $items ?? collect();
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

        try {
            DB::transaction(function () use ($item) {
                // Get default labor rate
                $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                    ->where('is_default', true)
                    ->first();
                    
                if (!$laborRate) {
                    $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                        ->first();
                }
                
                if (!$laborRate) {
                    throw new \Exception('No labor rate found. Please create at least one labor rate.');
                }

                Log::info('Creating new estimate item:', [
                    'parent_type' => $this->parentType,
                    'parent_id' => $this->parentId,
                    'item_id' => $item->id,
                    'labor_rate_id' => $laborRate->id,
                    'labor_units' => $item->labor_units
                ]);

                $estimateItem = new EstimateItem([
                    'tenant_id' => auth()->user()->current_tenant_id,
                    'estimate_id' => $this->parentType === 'estimate' ? $this->parentId : null,
                    'estimate_assembly_id' => $this->parentType === 'assembly' ? $this->parentId : null,
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

                Log::info('Added new item:', [
                    'parent_type' => $this->parentType,
                    'parent_id' => $this->parentId,
                    'item_id' => $estimateItem->id,
                    'labor_rate_id' => $estimateItem->labor_rate_id,
                    'labor_units' => $estimateItem->labor_units
                ]);

                // Refresh items collection
                $this->refreshItems();
                
                // Reset form
                $this->selectedItem = '';
                $this->itemQuantity = 1;
                
                // Emit update events
                $this->emitItemsChanged();
                if ($this->parentType === 'assembly') {
                    $this->dispatch('assembly-items-updated', [
                        'assembly_id' => $this->parentId,
                        'items' => $this->items->toArray()
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Error adding item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'parent_type' => $this->parentType,
                'parent_id' => $this->parentId
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

        try {
            $item = $this->items[$this->editingItemIndex];
            $item->quantity = $this->editingItemQuantity;
            $item->save();
            
            $this->cancelEditItem();
            
            // Refresh items collection
            $this->refreshItems();
            
            // Emit update events
            $this->emitItemsChanged();
            if ($this->parentType === 'assembly') {
                $this->dispatch('assembly-items-updated', [
                    'assembly_id' => $this->parentId,
                    'items' => $this->items->toArray()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating item: ' . $e->getMessage());
        }
    }

    public function cancelEditItem()
    {
        $this->editingItemIndex = null;
        $this->editingItemQuantity = null;
    }

    public function removeItem($index)
    {
        try {
            $item = $this->items[$index];
            $item->delete();
            
            // Refresh items collection
            $this->refreshItems();
            
            // Emit update events
            $this->emitItemsChanged();
            if ($this->parentType === 'assembly') {
                $this->dispatch('assembly-items-updated', [
                    'assembly_id' => $this->parentId,
                    'items' => $this->items->toArray()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error removing item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error removing item: ' . $e->getMessage());
        }
    }

    protected function refreshItems()
    {
        if ($this->parentType === 'estimate') {
            $this->items = EstimateItem::with(['laborRate'])
                ->where('estimate_id', $this->parentId)
                ->get();
        } else {
            $this->items = EstimateItem::with(['laborRate'])
                ->where('estimate_assembly_id', $this->parentId)
                ->get();
        }

        Log::info('Items refreshed:', [
            'parent_type' => $this->parentType,
            'parent_id' => $this->parentId,
            'items_count' => $this->items->count(),
            'first_item' => $this->items->first() ? [
                'id' => $this->items->first()->id,
                'name' => $this->items->first()->name,
                'labor_rate_id' => $this->items->first()->labor_rate_id,
                'labor_units' => $this->items->first()->labor_units,
            ] : null
        ]);
    }

    protected function emitItemsChanged()
    {
        $this->dispatch($this->parentType . '-items-updated', [
            'items' => $this->items->toArray()
        ]);
    }

    public function render()
    {
        // Ensure we have fresh data
        $this->refreshItems();
        
        $availableItems = Item::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('livewire.shared.items-table', [
            'availableItems' => $availableItems
        ]);
    }
} 