<?php

namespace App\Livewire\Shared;

use Livewire\Component;
use App\Models\EstimateAssembly;
use App\Models\Assembly;
use App\Models\LaborRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssemblyTable extends Component
{
    public $assembly;
    public $parentType = 'estimate'; // or 'package'
    public $parentId;
    public $isCollapsed = false;
    
    // For editing assembly
    public $editingQuantity = false;
    public $quantity;
    
    protected $listeners = [
        'assembly-items-updated' => 'handleItemsUpdate',
        'refreshAssembly' => '$refresh'
    ];

    public function mount($assembly, $parentType, $parentId, $isCollapsed = false)
    {
        $this->assembly = $assembly;
        $this->parentType = $parentType;
        $this->parentId = $parentId;
        $this->isCollapsed = $isCollapsed;
        $this->quantity = $assembly->quantity;
    }

    public function toggleCollapse()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function editQuantity()
    {
        $this->editingQuantity = true;
    }

    public function updateQuantity()
    {
        try {
            if ($this->quantity <= 0) {
                throw new \Exception('Quantity must be greater than zero.');
            }

            $this->assembly->quantity = $this->quantity;
            $this->assembly->save();
            
            $this->calculateAndEmitTotals();
            $this->editingQuantity = false;

        } catch (\Exception $e) {
            Log::error('Error updating assembly quantity:', [
                'error' => $e->getMessage(),
                'assembly_id' => $this->assembly->id
            ]);
            session()->flash('error', 'Error updating quantity: ' . $e->getMessage());
        }
    }

    public function cancelEditQuantity()
    {
        $this->editingQuantity = false;
        $this->quantity = $this->assembly->quantity;
    }

    public function handleItemsUpdate()
    {
        try {
            // Refresh the assembly with its items and their labor rates
            $this->assembly = EstimateAssembly::with(['items.laborRate'])->find($this->assembly->id);
            
            if (!$this->assembly) {
                throw new \Exception('Assembly not found after refresh');
            }

            Log::info('Assembly refreshed after item update:', [
                'assembly_id' => $this->assembly->id,
                'items_count' => $this->assembly->items->count(),
                'first_item' => $this->assembly->items->first() ? [
                    'id' => $this->assembly->items->first()->id,
                    'name' => $this->assembly->items->first()->name,
                    'labor_rate_id' => $this->assembly->items->first()->labor_rate_id,
                    'labor_units' => $this->assembly->items->first()->labor_units,
                ] : null
            ]);

            $this->calculateAndEmitTotals();
        } catch (\Exception $e) {
            Log::error('Error handling items update:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function calculateAndEmitTotals()
    {
        $totals = $this->calculateTotals();
        
        // Emit the totals up to the parent
        $this->dispatch($this->parentType . '-assembly-updated', [
            'assembly_id' => $this->assembly->id,
            'totals' => $totals
        ]);
    }

    protected function calculateTotals()
    {
        $materialCost = 0;
        $materialCharge = 0;
        $laborCost = 0;
        $laborCharge = 0;

        foreach ($this->assembly->items as $item) {
            Log::info('Calculating item totals:', [
                'item_id' => $item->id,
                'name' => $item->name,
                'labor_rate_id' => $item->labor_rate_id,
                'labor_units' => $item->labor_units,
                'quantity' => $item->quantity
            ]);

            // Material calculations
            $materialCost += $item->total_material_cost;
            $materialCharge += $item->total_material_charge;
            
            // Labor calculations
            $laborCost += $item->total_labor_cost;
            $laborCharge += $item->total_labor_charge;
        }

        // Multiply by assembly quantity
        $totalMaterialCost = $materialCost * $this->assembly->quantity;
        $totalMaterialCharge = $materialCharge * $this->assembly->quantity;
        $totalLaborCost = $laborCost * $this->assembly->quantity;
        $totalLaborCharge = $laborCharge * $this->assembly->quantity;

        $totals = [
            'material_cost' => $totalMaterialCost,
            'material_charge' => $totalMaterialCharge,
            'labor_cost' => $totalLaborCost,
            'labor_charge' => $totalLaborCharge,
            'total_cost' => $totalMaterialCost + $totalLaborCost,
            'total_charge' => $totalMaterialCharge + $totalLaborCharge,
        ];

        Log::info('Assembly totals calculated:', [
            'assembly_id' => $this->assembly->id,
            'totals' => $totals
        ]);

        return $totals;
    }

    public function render()
    {
        // Ensure we have fresh data with relationships
        $this->assembly = EstimateAssembly::with(['items.laborRate'])->find($this->assembly->id);
        $totals = $this->calculateTotals();
        
        return view('livewire.shared.assembly-table', [
            'totals' => $totals
        ]);
    }
} 