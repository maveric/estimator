<?php

namespace App\Livewire\Estimates;

use Livewire\Component;
use App\Models\Assembly;
use App\Models\Item;
use Illuminate\Support\Facades\Log;
use App\Models\EstimateItem;

class AssemblyComponent extends Component
{
    public $assembly;
    public $parentType; // 'estimate' or 'package'
    public $parentId;
    public $isEditing = false;
    public $isCollapsed = true;

    // Assembly properties
    public $name;
    public $description;
    public $quantity = 1;

    // For adding new items
    public $selectedItemId;
    public $newItemQuantity = 1;
    public $availableItems;

    protected $listeners = [
        'item-changed' => 'handleItemChanged',
        'item-deleted' => 'handleItemDeleted'
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'quantity' => 'required|numeric|min:0.01',
    ];

    public function mount($assembly = null, $parentType = 'estimate', $parentId = null)
    {
        $this->parentType = $parentType;
        $this->parentId = $parentId;

        if ($assembly) {
            $this->assembly = $assembly;
            $this->loadAssemblyData();
        }

        // Load available items
        $this->loadAvailableItems();
    }

    protected function loadAssemblyData()
    {
        $this->name = $this->assembly->name;
        $this->description = $this->assembly->description;
        $this->quantity = $this->assembly->quantity;
    }

    protected function loadAvailableItems()
    {
        $this->availableItems = Item::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function startEditing()
    {
        $this->isEditing = true;
    }

    public function cancelEditing()
    {
        $this->isEditing = false;
        $this->loadAssemblyData();
    }

    public function save()
    {
        $this->validate();

        try {
            $this->assembly->update([
                'name' => $this->name,
                'description' => $this->description,
                'quantity' => $this->quantity,
            ]);

            // Refresh the assembly to get the latest data
            $this->assembly->refresh();
            
            // Calculate new totals
            $calculatedValues = $this->calculateTotals();
            
            // Dispatch the event with the new totals
            $this->dispatch('assembly-changed', [
                'assemblyId' => $this->assembly->id,
                'totals' => $calculatedValues
            ])->self();

            $this->isEditing = false;
            
        } catch (\Exception $e) {
            Log::error('Error saving assembly:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving assembly: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $this->assembly->delete();
            $this->dispatch('assembly-deleted', ['assemblyId' => $this->assembly->id]);
        } catch (\Exception $e) {
            Log::error('Error deleting assembly:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error deleting assembly: ' . $e->getMessage());
        }
    }

    public function handleItemChanged($data)
    {
        // Recalculate assembly totals when an item changes
        $this->emitAssemblyChanged();
    }

    public function handleItemDeleted($data)
    {
        // Refresh the assembly data and recalculate totals
        $this->assembly->refresh();
        $this->emitAssemblyChanged();
    }

    protected function calculateTotals()
    {
        $materialCost = 0;
        $materialCharge = 0;
        $laborCost = 0;
        $laborCharge = 0;

        // Calculate totals from all items
        foreach ($this->assembly->items as $item) {
            // Calculate material totals
            $materialCost += $item->quantity * $item->material_cost_rate;
            $materialCharge += $item->quantity * $item->material_charge_rate;
            
            // Convert labor units to hours and calculate using the appropriate rates
            $laborHours = ($item->quantity * $item->labor_units) / 60;
            if ($item->laborRate) {
                $laborCost += $laborHours * $item->laborRate->cost_rate;
                $laborCharge += $laborHours * $item->laborRate->charge_rate;
            }
        }

        // Multiply by assembly quantity
        $materialCost *= $this->assembly->quantity;
        $materialCharge *= $this->assembly->quantity;
        $laborCost *= $this->assembly->quantity;
        $laborCharge *= $this->assembly->quantity;

        return [
            'material_cost' => $materialCost,
            'material_charge' => $materialCharge,
            'labor_cost' => $laborCost,
            'labor_charge' => $laborCharge,
            'total_cost' => $materialCost + $laborCost,
            'total_charge' => $materialCharge + $laborCharge
        ];
    }

    public function render()
    {
        if ($this->assembly) {
            // Ensure items are loaded with their labor rates
            $this->assembly->load('items.laborRate');
        }
        
        return view('livewire.estimates.assembly', [
            'items' => $this->assembly ? $this->assembly->items : collect(),
            'totals' => $this->calculateTotals()
        ]);
    }

    public function toggleCollapse()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function addItem()
    {
        if (!$this->selectedItemId || $this->newItemQuantity <= 0) {
            return;
        }

        try {
            $sourceItem = Item::findOrFail($this->selectedItemId);
            
            // Create new estimate item
            $estimateItem = new EstimateItem([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_assembly_id' => $this->assembly->id,
                'item_id' => $sourceItem->id,
                'original_item_id' => $sourceItem->id,
                'name' => $sourceItem->name,
                'description' => $sourceItem->description,
                'unit_of_measure' => $sourceItem->unit_of_measure,
                'quantity' => $this->newItemQuantity,
                'material_cost_rate' => $sourceItem->material_cost_rate,
                'material_charge_rate' => $sourceItem->material_charge_rate,
                'labor_units' => $sourceItem->labor_units,
                'labor_rate_id' => $sourceItem->labor_rate_id,
                'original_cost_rate' => $sourceItem->material_cost_rate,
                'original_charge_rate' => $sourceItem->material_charge_rate,
            ]);

            $estimateItem->save();
            
            // Reset form
            $this->selectedItemId = null;
            $this->newItemQuantity = 1;
            
            // Refresh assembly and emit changes
            $this->assembly->refresh();
            $this->emitAssemblyChanged();
            
        } catch (\Exception $e) {
            Log::error('Error adding item to assembly:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding item: ' . $e->getMessage());
        }
    }
} 