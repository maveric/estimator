<?php

namespace App\Livewire\Estimates;

use Livewire\Component;
use App\Models\Item;
use App\Models\LaborRate;
use Illuminate\Support\Facades\Log;

class ItemComponent extends Component
{
    public $item;
    public $quantity = 1;
    public $parentType; // 'estimate', 'assembly', or 'package'
    public $parentId;
    public $isEditing = false;
    
    // Item properties that can be modified
    public $name;
    public $description;
    public $unitOfMeasure;
    public $materialCostRate;
    public $materialChargeRate;
    public $laborUnits;
    public $laborRateId;

    protected $rules = [
        'quantity' => 'required|numeric|min:0.01',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'unitOfMeasure' => 'required|string|max:50',
        'materialCostRate' => 'required|numeric|min:0',
        'materialChargeRate' => 'required|numeric|min:0',
        'laborUnits' => 'required|numeric|min:0',
        'laborRateId' => 'required|exists:labor_rates,id'
    ];

    public function mount($item = null, $parentType = 'estimate', $parentId = null)
    {
        $this->parentType = $parentType;
        $this->parentId = $parentId;
        
        // Get the primary labor rate if none is set
        $primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->first();

        if (!$primaryLaborRate) {
            // Fallback to any labor rate
            $primaryLaborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                ->first();
            
            if (!$primaryLaborRate) {
                Log::error('No labor rate found');
            }
        }
        
        if ($item) {
            $this->item = $item;
            
            // Ensure labor rate relationship is loaded
            if (!$this->item->relationLoaded('laborRate')) {
                $this->item->load('laborRate');
            }
            
            // Set primary labor rate if none is set
            if (!$this->item->labor_rate_id && $primaryLaborRate) {
                $this->item->labor_rate_id = $primaryLaborRate->id;
                $this->item->save();
                
                // Reload the labor rate relationship
                $this->item->load('laborRate');
            }
            
            $this->loadItemData();
            
            // Log the loaded data for debugging
            Log::info('ItemComponent mounted with item:', [
                'item_id' => $this->item->id,
                'labor_rate_id' => $this->item->labor_rate_id,
                'labor_rate' => $this->item->laborRate ? [
                    'id' => $this->item->laborRate->id,
                    'name' => $this->item->laborRate->name,
                    'cost_rate' => $this->item->laborRate->cost_rate,
                    'charge_rate' => $this->item->laborRate->charge_rate
                ] : null
            ]);
        }
    }

    protected function loadItemData()
    {
        $this->name = $this->item->name;
        $this->description = $this->item->description;
        $this->unitOfMeasure = $this->item->unit_of_measure;
        $this->quantity = $this->item->quantity;
        $this->materialCostRate = $this->item->material_cost_rate;
        $this->materialChargeRate = $this->item->material_charge_rate;
        $this->laborUnits = $this->item->labor_units;
        $this->laborRateId = $this->item->labor_rate_id;

        // Log the loaded data for debugging
        \Log::info('ItemComponent - Loaded item data:', [
            'item_id' => $this->item->id,
            'labor_rate_id' => $this->laborRateId,
            'labor_rate' => $this->item->laborRate ? [
                'id' => $this->item->laborRate->id,
                'name' => $this->item->laborRate->name,
                'cost_rate' => $this->item->laborRate->cost_rate,
                'charge_rate' => $this->item->laborRate->charge_rate
            ] : null
        ]);
    }

    public function startEditing()
    {
        $this->isEditing = true;
    }

    public function cancelEditing()
    {
        $this->isEditing = false;
        $this->loadItemData(); // Reset to original values
    }

    public function save()
    {
        $this->validate();

        try {
            $this->item->update([
                'quantity' => $this->quantity,
                'name' => $this->name,
                'description' => $this->description,
                'unit_of_measure' => $this->unitOfMeasure,
                'material_cost_rate' => $this->materialCostRate,
                'material_charge_rate' => $this->materialChargeRate,
                'labor_units' => $this->laborUnits,
                'labor_rate_id' => $this->laborRateId,
            ]);

            // Refresh the item to get the latest data
            $this->item->refresh();
            
            // Calculate new totals
            $calculatedValues = $this->calculateTotals();
            
            // Dispatch the event with the new totals
            $this->dispatch('item-changed', [
                'itemId' => $this->item->id,
                'totals' => $calculatedValues
            ])->self();

            $this->isEditing = false;
            
        } catch (\Exception $e) {
            Log::error('Error saving item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving item: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $this->item->delete();
            $this->dispatch('item-deleted', ['itemId' => $this->item->id]);
        } catch (\Exception $e) {
            Log::error('Error deleting item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error deleting item: ' . $e->getMessage());
        }
    }

    protected function emitItemChanged()
    {
        $calculatedValues = $this->calculateTotals();
        $this->dispatch('item-changed', [
            'itemId' => $this->item->id,
            'totals' => $calculatedValues
        ]);
    }

    protected function calculateTotals()
    {
        $materialCost = $this->quantity * $this->materialCostRate;
        $materialCharge = $this->quantity * $this->materialChargeRate;
        $laborHours = ($this->quantity * $this->laborUnits) / 60; // Convert minutes to hours
        
        // Get both labor cost and charge rates
        $laborRate = $this->getLaborRate();
        $laborCost = $laborHours * $laborRate['cost_rate'];
        $laborCharge = $laborHours * $laborRate['charge_rate'];

        return [
            'material_cost' => $materialCost,
            'material_charge' => $materialCharge,
            'labor_cost' => $laborCost,
            'labor_charge' => $laborCharge,
            'total_cost' => $materialCost + $laborCost,
            'total_charge' => $materialCharge + $laborCharge
        ];
    }

    protected function getLaborRate()
    {
        if (!$this->laborRateId) {
            // Get the primary labor rate if none is set
            $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                ->where('is_primary', true)
                ->first();

            if (!$laborRate) {
                // Fallback to any labor rate
                $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                    ->first();
            }

            if ($laborRate) {
                $this->laborRateId = $laborRate->id;
                if ($this->item) {
                    $this->item->labor_rate_id = $laborRate->id;
                    $this->item->save();
                }
            }
        } else {
            $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                ->find($this->laborRateId);
        }

        return [
            'cost_rate' => $laborRate ? $laborRate->cost_rate : 0,
            'charge_rate' => $laborRate ? $laborRate->charge_rate : 0
        ];
    }

    // Add computed properties
    public function getLaborRateNameProperty()
    {
        return $this->item->laborRate ? $this->item->laborRate->name : 'No Rate';
    }

    public function getLaborChargeRateProperty()
    {
        return $this->item->laborRate ? $this->item->laborRate->charge_rate : 0;
    }

    public function getLaborCostRateProperty()
    {
        return $this->item->laborRate ? $this->item->laborRate->cost_rate : 0;
    }

    public function render()
    {
        $laborRates = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
            ->orderBy('name')
            ->get();

        return view('livewire.estimates.item', [
            'laborRates' => $laborRates,
            'totals' => $this->calculateTotals()
        ]);
    }
} 