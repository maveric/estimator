<?php

namespace App\Livewire\Packages;

use App\Models\Assembly;
use App\Models\Package;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PackageForm extends Component
{
    public ?Package $package = null;
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public array $selectedAssemblies = [];
    public array $assemblyQuantities = [];
    public array $packageAssemblies = [];
    public $availableAssemblies = [];
    public $selectedAssembly = null;
    public $quantity = 1;
    public $editingAssemblyIndex = null;
    public $editingAssemblyQuantity = null;
    public $mode = 'create';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'packageAssemblies' => 'required|array|min:1',
        'packageAssemblies.*.assembly_id' => 'exists:assemblies,id',
        'packageAssemblies.*.quantity' => 'required|numeric|min:0.01|max:999.99',
    ];

    protected $messages = [
        'packageAssemblies.required' => 'Please add at least one assembly to this package.',
        'packageAssemblies.min' => 'Please add at least one assembly to this package.',
        'packageAssemblies.*.quantity.required' => 'Quantity is required.',
        'packageAssemblies.*.quantity.numeric' => 'Quantity must be a number.',
        'packageAssemblies.*.quantity.min' => 'Quantity must be at least 0.01.',
        'packageAssemblies.*.quantity.max' => 'Quantity cannot exceed 999.99.',
    ];

    public function mount(Package $package = null)
    {
        $this->mode = $package && $package->exists ? 'edit' : 'create';
        
        if ($this->mode === 'edit') {
            $this->package = $package;
            $this->name = $package->name;
            $this->description = $package->description;
            $this->is_active = $package->is_active;
            
            // Load the selected assemblies with their quantities
            $this->loadPackageAssemblies();
        }
        
        // Load available assemblies for the current tenant
        $this->loadAvailableAssemblies();
    }
    
    protected function loadPackageAssemblies()
    {
        $this->packageAssemblies = [];
        
        foreach ($this->package->assemblies as $assembly) {
            $this->packageAssemblies[] = [
                'assembly_id' => $assembly->id,
                'name' => $assembly->name,
                'description' => $assembly->description,
                'quantity' => $assembly->pivot->quantity,
            ];
            
            // Also maintain the selected assemblies array for the UI
            $this->selectedAssemblies[] = $assembly->id;
        }
    }
    
    protected function loadAvailableAssemblies()
    {
        $tenantId = Auth::user()->current_tenant_id;
        $this->availableAssemblies = Assembly::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
    
    public function save()
    {
        $this->validate();
        
        $tenantId = Auth::user()->current_tenant_id;
        
        if ($this->mode === 'create') {
            $this->package = new Package();
        }
        
        $this->package->name = $this->name;
        $this->package->description = $this->description;
        $this->package->is_active = $this->is_active;
        $this->package->tenant_id = $tenantId;
        $this->package->save();
        
        // Sync the assemblies with their quantities
        $syncData = [];
        foreach ($this->packageAssemblies as $item) {
            $syncData[$item['assembly_id']] = ['quantity' => $item['quantity'], 'tenant_id' => $tenantId];
        }
        
        $this->package->assemblies()->detach();
        foreach ($syncData as $assemblyId => $attributes) {
            $this->package->assemblies()->attach($assemblyId, $attributes);
        }
        
        session()->flash('message', $this->mode === 'create' 
            ? 'Package created successfully!' 
            : 'Package updated successfully!');
            
        return redirect()->route('packages.index');
    }
    
    public function addAssembly()
    {
        if (!$this->selectedAssembly) {
            session()->flash('error', 'Please select an assembly to add.');
            return;
        }
        
        if ($this->quantity <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }
        
        $assembly = $this->availableAssemblies->find($this->selectedAssembly);
        
        if ($assembly) {
            $this->packageAssemblies[] = [
                'assembly_id' => $assembly->id,
                'name' => $assembly->name,
                'description' => $assembly->description,
                'quantity' => $this->quantity,
            ];
            
            // Add to selected assemblies if not already there
            if (!in_array($assembly->id, $this->selectedAssemblies)) {
                $this->selectedAssemblies[] = $assembly->id;
            }
            
            // Reset the form
            $this->selectedAssembly = null;
            $this->quantity = 1;
        }
    }
    
    public function removeAssembly($index)
    {
        $assemblyId = $this->packageAssemblies[$index]['assembly_id'];
        
        // Remove from packageAssemblies
        array_splice($this->packageAssemblies, $index, 1);
        
        // Check if this assembly is used elsewhere in the package
        $stillUsed = false;
        foreach ($this->packageAssemblies as $item) {
            if ($item['assembly_id'] == $assemblyId) {
                $stillUsed = true;
                break;
            }
        }
        
        // If not used elsewhere, remove from selectedAssemblies
        if (!$stillUsed) {
            $this->selectedAssemblies = array_diff($this->selectedAssemblies, [$assemblyId]);
        }
    }
    
    public function editAssembly($index)
    {
        $this->editingAssemblyIndex = $index;
        $this->editingAssemblyQuantity = $this->packageAssemblies[$index]['quantity'];
    }
    
    public function updateAssembly()
    {
        if ($this->editingAssemblyIndex !== null) {
            $this->packageAssemblies[$this->editingAssemblyIndex]['quantity'] = $this->editingAssemblyQuantity;
            $this->editingAssemblyIndex = null;
            $this->editingAssemblyQuantity = null;
        }
    }
    
    public function cancelEdit()
    {
        $this->editingAssemblyIndex = null;
        $this->editingAssemblyQuantity = null;
    }
    
    public function render()
    {
        return view('livewire.packages.form');
    }

    public function calculateCosts(): array
    {
        $totalMaterialCost = 0;
        $totalLaborCost = 0;
        $totalMaterialCharge = 0;
        $totalLaborCharge = 0;

        foreach ($this->packageAssemblies as $item) {
            $assembly = $this->availableAssemblies->find($item['assembly_id']);
            
            if ($assembly) {
                $assemblyCosts = $assembly->calculateCost();
                $quantity = $item['quantity'];
                
                $totalMaterialCost += $assemblyCosts['material_cost'] * $quantity;
                $totalLaborCost += $assemblyCosts['labor_cost'] * $quantity;
                $totalMaterialCharge += $assemblyCosts['material_charge'] * $quantity;
                $totalLaborCharge += $assemblyCosts['labor_charge'] * $quantity;
            }
        }

        return [
            'material_cost' => $totalMaterialCost,
            'labor_cost' => $totalLaborCost,
            'material_charge' => $totalMaterialCharge,
            'labor_charge' => $totalLaborCharge,
            'total_cost' => $totalMaterialCost + $totalLaborCost,
            'total_charge' => $totalMaterialCharge + $totalLaborCharge,
        ];
    }
} 