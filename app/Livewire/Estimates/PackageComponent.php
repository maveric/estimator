<?php

namespace App\Livewire\Estimates;

use Livewire\Component;
use App\Models\Package;
use App\Models\Assembly;
use App\Models\EstimatePackage;
use App\Models\EstimateAssembly;
use Illuminate\Support\Facades\Log;

class PackageComponent extends Component
{
    public $package;
    public $parentType; // 'estimate'
    public $parentId;
    public $isEditing = false;
    public $isCollapsed = true;

    // Package properties
    public $name;
    public $description;
    public $quantity = 1;

    // For adding new assemblies
    public $selectedAssemblyId;
    public $newAssemblyQuantity = 1;
    public $availableAssemblies;

    protected $listeners = [
        'assembly-changed' => 'handleAssemblyChanged',
        'assembly-deleted' => 'handleAssemblyDeleted'
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'quantity' => 'required|numeric|min:0.01',
    ];

    public function mount($package = null, $parentType = 'estimate', $parentId = null)
    {
        $this->parentType = $parentType;
        $this->parentId = $parentId;

        if ($package) {
            $this->package = $package;
            $this->loadPackageData();
        }

        // Load available assemblies
        $this->loadAvailableAssemblies();
    }

    protected function loadPackageData()
    {
        $this->name = $this->package->name;
        $this->description = $this->package->description;
        $this->quantity = $this->package->quantity;
    }

    protected function loadAvailableAssemblies()
    {
        $this->availableAssemblies = Assembly::where('tenant_id', auth()->user()->current_tenant_id)
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
        $this->loadPackageData();
    }

    public function save()
    {
        $this->validate();

        try {
            $this->package->update([
                'name' => $this->name,
                'description' => $this->description,
                'quantity' => $this->quantity,
            ]);

            // Refresh the package to get the latest data
            $this->package->refresh();
            
            // Calculate new totals
            $calculatedValues = $this->calculateTotals();
            
            // Dispatch the event with the new totals
            $this->dispatch('package-changed', [
                'packageId' => $this->package->id,
                'totals' => $calculatedValues
            ])->self();

            $this->isEditing = false;
            
        } catch (\Exception $e) {
            Log::error('Error saving package:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving package: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $this->package->delete();
            $this->dispatch('package-deleted', ['packageId' => $this->package->id]);
        } catch (\Exception $e) {
            Log::error('Error deleting package:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error deleting package: ' . $e->getMessage());
        }
    }

    public function handleAssemblyChanged($data)
    {
        // Recalculate package totals when an assembly changes
        $this->emitPackageChanged();
    }

    public function handleAssemblyDeleted($data)
    {
        // Refresh the package data and recalculate totals
        $this->package->refresh();
        $this->emitPackageChanged();
    }

    protected function calculateTotals()
    {
        $materialCost = 0;
        $materialCharge = 0;
        $laborCost = 0;
        $laborCharge = 0;

        // Calculate totals from all assemblies
        foreach ($this->package->assemblies as $assembly) {
            $assemblyTotals = $assembly->calculateTotals();
            $materialCost += $assemblyTotals['material_cost'];
            $materialCharge += $assemblyTotals['material_charge'];
            $laborCost += $assemblyTotals['labor_cost'];
            $laborCharge += $assemblyTotals['labor_charge'];
        }

        // Multiply by package quantity
        $materialCost *= $this->package->quantity;
        $materialCharge *= $this->package->quantity;
        $laborCost *= $this->package->quantity;
        $laborCharge *= $this->package->quantity;

        return [
            'material_cost' => $materialCost,
            'material_charge' => $materialCharge,
            'labor_cost' => $laborCost,
            'labor_charge' => $laborCharge,
            'total_cost' => $materialCost + $laborCost,
            'total_charge' => $materialCharge + $laborCharge
        ];
    }

    public function toggleCollapse()
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    public function addAssembly()
    {
        if (!$this->selectedAssemblyId || $this->newAssemblyQuantity <= 0) {
            return;
        }

        try {
            // Load the source assembly with its items and their pivot data
            $sourceAssembly = Assembly::with(['items' => function($query) {
                $query->withPivot(['quantity']);
            }])->findOrFail($this->selectedAssemblyId);
            
            // Create new estimate assembly
            $estimateAssembly = new EstimateAssembly([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_package_id' => $this->package->id,
                'original_assembly_id' => $sourceAssembly->id,
                'name' => $sourceAssembly->name,
                'description' => $sourceAssembly->description,
                'quantity' => $this->newAssemblyQuantity,
            ]);

            $estimateAssembly->save();

            // Clone items from the source assembly
            foreach ($sourceAssembly->items as $item) {
                $estimateAssembly->items()->create([
                    'tenant_id' => auth()->user()->current_tenant_id,
                    'item_id' => $item->id,
                    'original_item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'unit_of_measure' => $item->unit_of_measure,
                    'quantity' => $item->pivot->quantity,
                    'material_cost_rate' => $item->material_cost_rate,
                    'material_charge_rate' => $item->material_charge_rate,
                    'original_cost_rate' => $item->material_cost_rate,
                    'original_charge_rate' => $item->material_charge_rate,
                    'labor_units' => $item->labor_units,
                    'labor_rate_id' => $item->labor_rate_id ?? $this->getPrimaryLaborRateId(),
                ]);
            }
            
            // Reset form
            $this->selectedAssemblyId = null;
            $this->newAssemblyQuantity = 1;
            
            // Refresh package and emit changes
            $this->package->refresh();
            $this->emitPackageChanged();
            
        } catch (\Exception $e) {
            Log::error('Error adding assembly to package:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding assembly: ' . $e->getMessage());
        }
    }

    protected function getPrimaryLaborRateId()
    {
        $primaryLaborRate = \App\Models\LaborRate::where('is_primary', true)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->first();
            
        if (!$primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }

        return $primaryLaborRate->id;
    }

    public function render()
    {
        if ($this->package) {
            // Ensure assemblies are loaded with their items and labor rates
            $this->package->load('assemblies.items.laborRate');
        }
        
        return view('livewire.estimates.package', [
            'assemblies' => $this->package ? $this->package->assemblies : collect(),
            'totals' => $this->calculateTotals()
        ]);
    }
} 