<?php

namespace App\Livewire\Shared;

use Livewire\Component;
use App\Models\Package;
use App\Models\Assembly;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageTable extends Component
{
    public $package;
    public $parentType = 'estimate'; // or 'package'
    public $parentId;
    public $isCollapsed = false;
    
    // For editing package
    public $editingQuantity = false;
    public $quantity;
    
    protected $listeners = [
        'assembly-updated' => 'handleAssemblyUpdate',
        'refreshPackage' => '$refresh'
    ];

    public function mount($package, $parentType, $parentId, $isCollapsed = false)
    {
        $this->package = $package;
        $this->parentType = $parentType;
        $this->parentId = $parentId;
        $this->isCollapsed = $isCollapsed;
        $this->quantity = $package->quantity;
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

            $this->package->quantity = $this->quantity;
            $this->package->save();
            
            $this->calculateAndEmitTotals();
            $this->editingQuantity = false;

        } catch (\Exception $e) {
            Log::error('Error updating package quantity:', [
                'error' => $e->getMessage(),
                'package_id' => $this->package->id
            ]);
            session()->flash('error', 'Error updating quantity: ' . $e->getMessage());
        }
    }

    public function cancelEditQuantity()
    {
        $this->editingQuantity = false;
        $this->quantity = $this->package->quantity;
    }

    public function handleAssemblyUpdate()
    {
        try {
            // Refresh the package with its assemblies and their items
            $this->package = Package::with(['assemblies.items.laborRate'])
                ->find($this->package->id);

            // Log the first assembly for debugging
            if ($this->package->assemblies->isNotEmpty()) {
                $firstAssembly = $this->package->assemblies->first();
                Log::info('First assembly after refresh:', [
                    'assembly_id' => $firstAssembly->id,
                    'name' => $firstAssembly->name,
                    'items_count' => $firstAssembly->items->count()
                ]);
            }

            $this->calculateAndEmitTotals();

        } catch (\Exception $e) {
            Log::error('Error handling assembly update:', [
                'error' => $e->getMessage(),
                'package_id' => $this->package->id
            ]);
        }
    }

    protected function calculateAndEmitTotals()
    {
        try {
            $totals = [
                'material_cost' => 0,
                'labor_cost' => 0,
                'material_charge' => 0,
                'labor_charge' => 0,
                'total_cost' => 0,
                'total_charge' => 0,
            ];

            foreach ($this->package->assemblies as $assembly) {
                // Log assembly details for debugging
                Log::info('Calculating totals for assembly:', [
                    'assembly_id' => $assembly->id,
                    'name' => $assembly->name,
                    'quantity' => $assembly->quantity,
                    'items_count' => $assembly->items->count()
                ]);

                foreach ($assembly->items as $item) {
                    $itemTotalMaterialCost = $item->material_cost_rate * $item->quantity;
                    $itemTotalMaterialCharge = $item->material_charge_rate * $item->quantity;
                    
                    // Convert labor units from minutes to hours and use correct rate properties
                    $laborHours = ($item->labor_units * $item->quantity) / 60;
                    $itemTotalLaborCost = ($item->labor_rate->cost_rate ?? 0) * $laborHours;
                    $itemTotalLaborCharge = ($item->labor_rate->charge_rate ?? 0) * $laborHours;

                    $totals['material_cost'] += $itemTotalMaterialCost * $assembly->quantity;
                    $totals['labor_cost'] += $itemTotalLaborCost * $assembly->quantity;
                    $totals['material_charge'] += $itemTotalMaterialCharge * $assembly->quantity;
                    $totals['labor_charge'] += $itemTotalLaborCharge * $assembly->quantity;
                }
            }

            // Multiply all totals by package quantity
            foreach ($totals as $key => $value) {
                $totals[$key] = $value * $this->package->quantity;
            }

            $totals['total_cost'] = $totals['material_cost'] + $totals['labor_cost'];
            $totals['total_charge'] = $totals['material_charge'] + $totals['labor_charge'];

            // Log final totals for debugging
            Log::info('Package totals calculated:', [
                'package_id' => $this->package->id,
                'totals' => $totals
            ]);

            $this->totals = $totals;
            $this->dispatch('package-updated', [
                'package' => $this->package->toArray(),
                'totals' => $totals
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating package totals:', [
                'error' => $e->getMessage(),
                'package_id' => $this->package->id
            ]);
        }
    }

    public function render()
    {
        // Ensure we have fresh data
        $this->package->load(['assemblies.items.laborRate']);
        
        return view('livewire.shared.package-table', [
            'totals' => $this->totals ?? []
        ]);
    }
} 