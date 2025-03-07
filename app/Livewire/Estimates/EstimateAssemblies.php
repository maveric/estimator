<?php

namespace App\Livewire\Estimates;

use Livewire\Component;
use App\Models\Assembly;
use App\Models\EstimateAssembly;
use App\Models\EstimateItem;
use Illuminate\Support\Collection;

class EstimateAssemblies extends Component
{
    public $estimate;
    public $assemblies;
    
    // For adding new assemblies
    public $selectedAssembly = '';
    public $assemblyQuantity = 1;
    
    // For adding items to assemblies
    public $addingItemToAssemblyIndex = null;
    public $selectedItem = '';
    public $selectedItemQuantity = 1;
    
    // For editing assemblies
    public $editingAssemblyIndex = null;
    public $editingAssemblyQuantity = null;
    
    // For editing assembly items
    public $editingAssemblyItemData = [
        'assembly_index' => null,
        'item_index' => null,
        'quantity' => null
    ];
    
    // Collapse state tracking
    public $collapsedAssemblies = [];

    public function mount($estimate = null)
    {
        $this->assemblies = collect();
        
        if ($estimate) {
            $this->estimate = $estimate;
            $this->assemblies = collect($estimate->assemblies->map(function ($assembly) {
                $assembly->items = collect($assembly->items->map(function ($item) {
                    return new EstimateItem([
                        'item_id' => $item->item_id,
                        'original_item_id' => $item->original_item_id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'unit_of_measure' => $item->unit_of_measure,
                        'quantity' => $item->quantity,
                        'material_cost_rate' => $item->material_cost_rate,
                        'material_charge_rate' => $item->material_charge_rate,
                        'labor_units' => $item->labor_units,
                        'labor_rate_id' => $item->labor_rate_id,
                        'original_cost_rate' => $item->original_cost_rate,
                        'original_charge_rate' => $item->original_charge_rate,
                    ]);
                }));
                return $assembly;
            }));

            // Initialize collapse states
            foreach ($this->assemblies as $assemblyIndex => $assembly) {
                $this->collapsedAssemblies[] = $assemblyIndex;
            }
        }
    }

    public function addAssembly()
    {
        if (!$this->selectedAssembly || $this->assemblyQuantity <= 0) {
            return;
        }

        $assembly = Assembly::with('items')->find($this->selectedAssembly);
        if (!$assembly) {
            return;
        }

        \Log::info('EstimateAssemblies - Creating new assembly:', [
            'selected_assembly_id' => $this->selectedAssembly,
            'quantity' => $this->assemblyQuantity,
            'estimate_id' => $this->estimate ? $this->estimate->id : null
        ]);

        // Create and save the estimate assembly
        $estimateAssembly = new EstimateAssembly([
            'tenant_id' => auth()->user()->current_tenant_id,
            'estimate_id' => $this->estimate->id,
            'assembly_id' => $assembly->id,
            'original_assembly_id' => $assembly->id,
            'name' => $assembly->name,
            'description' => $assembly->description,
            'quantity' => $this->assemblyQuantity,
        ]);

        $estimateAssembly->save();

        \Log::info('EstimateAssemblies - Created and saved assembly:', [
            'assembly_data' => [
                'id' => $estimateAssembly->id,
                'estimate_id' => $estimateAssembly->estimate_id,
                'name' => $estimateAssembly->name
            ]
        ]);

        // Create and save items for this assembly
        foreach ($assembly->items as $item) {
            $estimateItem = new EstimateItem([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_assembly_id' => $estimateAssembly->id,
                'item_id' => $item->id,
                'original_item_id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'unit_of_measure' => $item->unit_of_measure,
                'quantity' => $item->pivot->quantity,
                'material_cost_rate' => $item->material_cost_rate,
                'material_charge_rate' => $item->material_charge_rate,
                'labor_units' => $item->labor_units,
                'labor_rate_id' => $item->labor_rate_id,
                'original_cost_rate' => $item->material_cost_rate,
                'original_charge_rate' => $item->material_charge_rate,
            ]);

            $estimateItem->save();
        }

        // Refresh assemblies from database
        $this->assemblies = collect($this->estimate->assemblies()->with('items')->get());
        
        \Log::info('EstimateAssemblies - After refreshing assemblies:', [
            'assemblies_count' => $this->assemblies->count(),
            'assemblies' => $this->assemblies->map(fn($assembly) => [
                'id' => $assembly->id,
                'estimate_id' => $assembly->estimate_id,
                'name' => $assembly->name,
                'items_count' => $assembly->items->count()
            ])->toArray()
        ]);

        // Reset form
        $this->selectedAssembly = '';
        $this->assemblyQuantity = 1;
        
        // Add to collapsed state
        $this->collapsedAssemblies[] = $this->assemblies->count() - 1;
        
        $this->emitAssembliesChanged();
    }

    public function editAssembly($index)
    {
        $this->editingAssemblyIndex = $index;
        $this->editingAssemblyQuantity = $this->assemblies[$index]->quantity;
    }

    public function updateAssembly()
    {
        if ($this->editingAssemblyIndex === null || $this->editingAssemblyQuantity <= 0) {
            return;
        }

        $assembly = $this->assemblies[$this->editingAssemblyIndex];
        $assembly->quantity = $this->editingAssemblyQuantity;
        $assembly->save();
        
        $this->cancelEditAssembly();
        $this->emitAssembliesChanged();
    }

    public function cancelEditAssembly()
    {
        $this->editingAssemblyIndex = null;
        $this->editingAssemblyQuantity = null;
    }

    public function removeAssembly($index)
    {
        $assembly = $this->assemblies->get($index);
        if ($assembly && $assembly->id) {
            $assembly->delete(); // This will cascade delete the items due to foreign key constraint
        }
        
        $this->assemblies = collect($this->estimate->assemblies()->with('items')->get());
        $this->emitAssembliesChanged();
    }

    public function editAssemblyItem($assemblyIndex, $itemIndex)
    {
        $this->editingAssemblyItemData = [
            'assembly_index' => $assemblyIndex,
            'item_index' => $itemIndex,
            'quantity' => $this->assemblies[$assemblyIndex]->items[$itemIndex]->quantity
        ];
    }

    public function updateAssemblyItem()
    {
        \Log::info('updateAssemblyItem called with data:', [
            'editingAssemblyItemData' => $this->editingAssemblyItemData
        ]);

        try {
            if ($this->editingAssemblyItemData['assembly_index'] === null || 
                $this->editingAssemblyItemData['item_index'] === null || 
                !is_numeric($this->editingAssemblyItemData['quantity']) ||
                $this->editingAssemblyItemData['quantity'] <= 0) {
                throw new \Exception('Invalid assembly item data');
            }

            $assemblyIndex = $this->editingAssemblyItemData['assembly_index'];
            $itemIndex = $this->editingAssemblyItemData['item_index'];
            
            if (!isset($this->assemblies[$assemblyIndex])) {
                throw new \Exception('Assembly not found');
            }
            
            $assembly = $this->assemblies[$assemblyIndex];
            
            if (!isset($assembly->items[$itemIndex])) {
                throw new \Exception('Item not found in assembly');
            }
            
            $item = $assembly->items[$itemIndex];
            
            \Log::info('Updating item quantity:', [
                'assembly_id' => $assembly->id,
                'item_id' => $item->id,
                'old_quantity' => $item->quantity,
                'new_quantity' => $this->editingAssemblyItemData['quantity']
            ]);
            
            $item->quantity = $this->editingAssemblyItemData['quantity'];
            $saved = $item->save();
            
            \Log::info('Save result:', ['saved' => $saved]);
            
            // Refresh the assemblies to get the updated data
            $this->assemblies = collect($this->estimate->assemblies()->with('items')->get());
            
            $this->cancelEditAssemblyItem();
            $this->emitAssembliesChanged();
            
            session()->flash('message', 'Item quantity updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Error updating assembly item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating item quantity: ' . $e->getMessage());
        }
    }

    public function cancelEditAssemblyItem()
    {
        $this->editingAssemblyItemData = [
            'assembly_index' => null,
            'item_index' => null,
            'quantity' => null
        ];
    }

    public function toggleAssemblyCollapse($assemblyIndex)
    {
        $key = array_search($assemblyIndex, $this->collapsedAssemblies);
        if ($key !== false) {
            unset($this->collapsedAssemblies[$key]);
        } else {
            $this->collapsedAssemblies[] = $assemblyIndex;
        }
    }

    public function removeAssemblyItem($assemblyIndex, $itemIndex)
    {
        try {
            \Log::info('Removing assembly item:', [
                'assembly_index' => $assemblyIndex,
                'item_index' => $itemIndex
            ]);

            if (!isset($this->assemblies[$assemblyIndex])) {
                throw new \Exception('Assembly not found');
            }

            $assembly = $this->assemblies[$assemblyIndex];
            
            if (!isset($assembly->items[$itemIndex])) {
                throw new \Exception('Item not found in assembly');
            }

            $item = $assembly->items[$itemIndex];
            
            // Delete the item from the database
            if ($item->id) {
                $item->delete();
            }

            // Refresh the assemblies to get the updated data
            $this->assemblies = collect($this->estimate->assemblies()->with('items')->get());
            
            $this->emitAssembliesChanged();
            
            session()->flash('message', 'Item removed successfully.');
        } catch (\Exception $e) {
            \Log::error('Error removing assembly item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error removing item: ' . $e->getMessage());
        }
    }

    public function addItemToAssembly()
    {
        \Log::info('Adding item to assembly:', [
            'assembly_index' => $this->addingItemToAssemblyIndex,
            'selected_item' => $this->selectedItem,
            'quantity' => $this->selectedItemQuantity
        ]);

        try {
            if ($this->addingItemToAssemblyIndex === null || 
                !$this->selectedItem || 
                $this->selectedItemQuantity <= 0) {
                throw new \Exception('Invalid item data');
            }

            $assembly = $this->assemblies[$this->addingItemToAssemblyIndex];
            $item = \App\Models\Item::find($this->selectedItem);

            if (!$assembly || !$item) {
                throw new \Exception('Assembly or item not found');
            }

            // Create and save the new estimate item
            $estimateItem = new EstimateItem([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_assembly_id' => $assembly->id,
                'item_id' => $item->id,
                'original_item_id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'unit_of_measure' => $item->unit_of_measure,
                'quantity' => $this->selectedItemQuantity,
                'material_cost_rate' => $item->material_cost_rate,
                'material_charge_rate' => $item->material_charge_rate,
                'labor_units' => $item->labor_units,
                'labor_rate_id' => $item->labor_rate_id,
                'original_cost_rate' => $item->material_cost_rate,
                'original_charge_rate' => $item->material_charge_rate,
            ]);

            $estimateItem->save();

            // Refresh assemblies from database
            $this->assemblies = collect($this->estimate->assemblies()->with('items')->get());

            // Reset form
            $this->selectedItem = '';
            $this->selectedItemQuantity = 1;
            $this->addingItemToAssemblyIndex = null;

            $this->emitAssembliesChanged();
            
            session()->flash('message', 'Item added to assembly successfully.');
        } catch (\Exception $e) {
            \Log::error('Error adding item to assembly:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding item: ' . $e->getMessage());
        }
    }

    public function startAddingItemToAssembly($assemblyIndex)
    {
        $this->addingItemToAssemblyIndex = $assemblyIndex;
        $this->selectedItem = '';
        $this->selectedItemQuantity = 1;
    }

    public function cancelAddingItemToAssembly()
    {
        $this->addingItemToAssemblyIndex = null;
        $this->selectedItem = '';
        $this->selectedItemQuantity = 1;
    }

    protected function emitAssembliesChanged()
    {
        \Log::info('EstimateAssemblies - Emitting assemblies changed:', [
            'assemblies_count' => $this->assemblies->count(),
            'assemblies' => $this->assemblies->map(fn($assembly) => [
                'id' => $assembly->id ?? null,
                'estimate_id' => $assembly->estimate_id ?? null,
                'name' => $assembly->name ?? null,
                'items_count' => $assembly->items->count()
            ])->toArray()
        ]);

        $this->dispatch('estimate-assemblies-updated', [
            'assemblies' => $this->assemblies->toArray()
        ]);
    }

    public function render()
    {
        $availableAssemblies = Assembly::orderBy('name')->get();
        $availableItems = \App\Models\Item::orderBy('name')->get();
        
        return view('livewire.estimates.estimate-assemblies', [
            'availableAssemblies' => $availableAssemblies,
            'availableItems' => $availableItems
        ]);
    }
} 