<?php

namespace App\Livewire\Estimates;

use Livewire\Component;
use App\Models\Package;
use App\Models\EstimatePackage;
use App\Models\EstimateAssembly;
use App\Models\EstimateItem;
use Illuminate\Support\Collection;

class EstimatePackages extends Component
{
    public $estimate;
    public $packages;
    
    // For adding new packages
    public $selectedPackage = '';
    public $packageQuantity = 1;
    
    // For editing packages
    public $editingPackageIndex = null;
    public $editingPackageQuantity = null;
    
    // For adding assemblies to packages
    public $addingAssemblyToPackageIndex = null;
    public $selectedAssembly = '';
    public $selectedAssemblyQuantity = 1;
    
    // For editing assemblies in packages
    public $editingPackageAssemblyData = [
        'package_index' => null,
        'assembly_index' => null,
        'quantity' => null
    ];
    
    // For adding items to assemblies
    public $addingItemToAssemblyData = [
        'package_index' => null,
        'assembly_index' => null,
        'selected_item' => '',
        'quantity' => 1
    ];
    
    // For editing items in assemblies
    public $editingAssemblyItemData = [
        'package_index' => null,
        'assembly_index' => null,
        'item_index' => null,
        'quantity' => null
    ];
    
    // Collapse state tracking
    public $collapsedPackages = [];
    public $collapsedAssemblies = [];

    public function mount($estimate = null)
    {
        $this->packages = collect();
        
        if ($estimate) {
            $this->estimate = $estimate;
            $this->packages = collect($estimate->packages->map(function ($package) {
                $package->assemblies = collect($package->assemblies->map(function ($assembly) {
                    $assembly->items = collect($assembly->items->map(function ($item) {
                        return new EstimateItem([
                            'id' => $item->id,
                            'tenant_id' => $item->tenant_id,
                            'estimate_assembly_id' => $item->estimate_assembly_id,
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
                return $package;
            }));

            // Initialize collapse states
            foreach ($this->packages as $packageIndex => $package) {
                $this->collapsedPackages[] = $packageIndex;
                foreach ($package->assemblies as $assemblyIndex => $assembly) {
                    $this->collapsedAssemblies[] = "{$packageIndex}-{$assemblyIndex}";
                }
            }
        }
    }

    public function addPackage()
    {
        if (!$this->selectedPackage || $this->packageQuantity <= 0) {
            return;
        }

        $package = Package::with(['assemblies.items'])->find($this->selectedPackage);
        if (!$package) {
            return;
        }

        \Log::info('EstimatePackages - Creating new package:', [
            'selected_package_id' => $this->selectedPackage,
            'quantity' => $this->packageQuantity
        ]);

        // Create and save the estimate package
        $estimatePackage = new EstimatePackage([
            'tenant_id' => auth()->user()->current_tenant_id,
            'estimate_id' => $this->estimate->id,
            'package_id' => $package->id,
            'original_package_id' => $package->id,
            'name' => $package->name,
            'description' => $package->description,
            'quantity' => $this->packageQuantity,
        ]);

        $estimatePackage->save();

        // Create assemblies and items for this package
        foreach ($package->assemblies as $assembly) {
            $estimateAssembly = new EstimateAssembly([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_package_id' => $estimatePackage->id,
                'assembly_id' => $assembly->id,
                'original_assembly_id' => $assembly->id,
                'name' => $assembly->name,
                'description' => $assembly->description,
                'quantity' => $assembly->pivot->quantity,
            ]);

            $estimateAssembly->save();

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
        }

        // Refresh packages from database
        $this->packages = collect($this->estimate->packages()->with(['assemblies.items'])->get());
        
        // Reset form
        $this->selectedPackage = '';
        $this->packageQuantity = 1;
        
        // Add to collapsed state
        $this->collapsedPackages[] = $this->packages->count() - 1;
        
        $this->emitPackagesChanged();
        
        session()->flash('message', 'Package added successfully.');
    }

    public function editPackage($index)
    {
        $this->editingPackageIndex = $index;
        $this->editingPackageQuantity = $this->packages[$index]->quantity;
    }

    public function updatePackage()
    {
        try {
            if ($this->editingPackageIndex === null || $this->editingPackageQuantity <= 0) {
                throw new \Exception('Invalid package data');
            }

            $package = $this->packages[$this->editingPackageIndex];
            $package->quantity = $this->editingPackageQuantity;
            $package->save();
            
            $this->cancelEditPackage();
            $this->emitPackagesChanged();
            
            session()->flash('message', 'Package quantity updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating package quantity: ' . $e->getMessage());
        }
    }

    public function cancelEditPackage()
    {
        $this->editingPackageIndex = null;
        $this->editingPackageQuantity = null;
    }

    public function removePackage($index)
    {
        try {
            $package = $this->packages->get($index);
            if ($package && $package->id) {
                $package->delete(); // This will cascade delete assemblies and items
            }
            
            $this->packages = collect($this->estimate->packages()->with(['assemblies.items'])->get());
            $this->emitPackagesChanged();
            
            session()->flash('message', 'Package removed successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error removing package: ' . $e->getMessage());
        }
    }

    public function startAddingAssemblyToPackage($packageIndex)
    {
        $this->addingAssemblyToPackageIndex = $packageIndex;
        $this->selectedAssembly = '';
        $this->selectedAssemblyQuantity = 1;
    }

    public function cancelAddingAssemblyToPackage()
    {
        $this->addingAssemblyToPackageIndex = null;
        $this->selectedAssembly = '';
        $this->selectedAssemblyQuantity = 1;
    }

    public function addAssemblyToPackage()
    {
        try {
            if ($this->addingAssemblyToPackageIndex === null || 
                !$this->selectedAssembly || 
                $this->selectedAssemblyQuantity <= 0) {
                throw new \Exception('Invalid assembly data');
            }

            $package = $this->packages[$this->addingAssemblyToPackageIndex];
            $assembly = \App\Models\Assembly::with('items')->find($this->selectedAssembly);

            if (!$package || !$assembly) {
                throw new \Exception('Package or assembly not found');
            }

            // Create and save the new estimate assembly
            $estimateAssembly = new EstimateAssembly([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_package_id' => $package->id,
                'assembly_id' => $assembly->id,
                'original_assembly_id' => $assembly->id,
                'name' => $assembly->name,
                'description' => $assembly->description,
                'quantity' => $this->selectedAssemblyQuantity,
            ]);

            $estimateAssembly->save();

            // Create items for this assembly
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

            // Refresh packages from database
            $this->packages = collect($this->estimate->packages()->with(['assemblies.items'])->get());

            // Reset form
            $this->cancelAddingAssemblyToPackage();

            $this->emitPackagesChanged();
            
            session()->flash('message', 'Assembly added to package successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error adding assembly to package: ' . $e->getMessage());
        }
    }

    public function editPackageAssembly($packageIndex, $assemblyIndex)
    {
        $this->editingPackageAssemblyData = [
            'package_index' => $packageIndex,
            'assembly_index' => $assemblyIndex,
            'quantity' => $this->packages[$packageIndex]->assemblies[$assemblyIndex]->quantity
        ];
    }

    public function updatePackageAssembly()
    {
        try {
            if ($this->editingPackageAssemblyData['package_index'] === null || 
                $this->editingPackageAssemblyData['assembly_index'] === null || 
                !is_numeric($this->editingPackageAssemblyData['quantity']) ||
                $this->editingPackageAssemblyData['quantity'] <= 0) {
                throw new \Exception('Invalid assembly data');
            }

            $packageIndex = $this->editingPackageAssemblyData['package_index'];
            $assemblyIndex = $this->editingPackageAssemblyData['assembly_index'];
            
            if (!isset($this->packages[$packageIndex])) {
                throw new \Exception('Package not found');
            }
            
            $package = $this->packages[$packageIndex];
            
            if (!isset($package->assemblies[$assemblyIndex])) {
                throw new \Exception('Assembly not found in package');
            }
            
            $assembly = $package->assemblies[$assemblyIndex];
            
            $assembly->quantity = $this->editingPackageAssemblyData['quantity'];
            $assembly->save();
            
            // Refresh packages from database
            $this->packages = collect($this->estimate->packages()->with(['assemblies.items'])->get());
            
            $this->cancelEditPackageAssembly();
            $this->emitPackagesChanged();
            
            session()->flash('message', 'Assembly quantity updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating assembly quantity: ' . $e->getMessage());
        }
    }

    public function cancelEditPackageAssembly()
    {
        $this->editingPackageAssemblyData = [
            'package_index' => null,
            'assembly_index' => null,
            'quantity' => null
        ];
    }

    public function removePackageAssembly($packageIndex, $assemblyIndex)
    {
        try {
            if (!isset($this->packages[$packageIndex])) {
                throw new \Exception('Package not found');
            }

            $package = $this->packages[$packageIndex];
            
            if (!isset($package->assemblies[$assemblyIndex])) {
                throw new \Exception('Assembly not found in package');
            }

            $assembly = $package->assemblies[$assemblyIndex];
            
            // Delete the assembly from the database
            if ($assembly->id) {
                $assembly->delete(); // This will cascade delete the items
            }

            // Refresh packages from database
            $this->packages = collect($this->estimate->packages()->with(['assemblies.items'])->get());
            
            $this->emitPackagesChanged();
            
            session()->flash('message', 'Assembly removed from package successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error removing assembly: ' . $e->getMessage());
        }
    }

    public function startAddingItemToAssembly($packageIndex, $assemblyIndex)
    {
        $this->addingItemToAssemblyData = [
            'package_index' => $packageIndex,
            'assembly_index' => $assemblyIndex,
            'selected_item' => '',
            'quantity' => 1
        ];
    }

    public function cancelAddingItemToAssembly()
    {
        $this->addingItemToAssemblyData = [
            'package_index' => null,
            'assembly_index' => null,
            'selected_item' => '',
            'quantity' => 1
        ];
    }

    public function addItemToAssembly()
    {
        try {
            if ($this->addingItemToAssemblyData['package_index'] === null || 
                $this->addingItemToAssemblyData['assembly_index'] === null || 
                !$this->addingItemToAssemblyData['selected_item'] || 
                $this->addingItemToAssemblyData['quantity'] <= 0) {
                throw new \Exception('Invalid item data');
            }

            $packageIndex = $this->addingItemToAssemblyData['package_index'];
            $assemblyIndex = $this->addingItemToAssemblyData['assembly_index'];

            if (!isset($this->packages[$packageIndex])) {
                throw new \Exception('Package not found');
            }

            $package = $this->packages[$packageIndex];
            
            if (!isset($package->assemblies[$assemblyIndex])) {
                throw new \Exception('Assembly not found in package');
            }

            $assembly = $package->assemblies[$assemblyIndex];
            $item = \App\Models\Item::find($this->addingItemToAssemblyData['selected_item']);

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
                'quantity' => $this->addingItemToAssemblyData['quantity'],
                'material_cost_rate' => $item->material_cost_rate,
                'material_charge_rate' => $item->material_charge_rate,
                'labor_units' => $item->labor_units,
                'labor_rate_id' => $item->labor_rate_id,
                'original_cost_rate' => $item->material_cost_rate,
                'original_charge_rate' => $item->material_charge_rate,
            ]);

            $estimateItem->save();

            // Refresh packages from database
            $this->packages = collect($this->estimate->packages()->with(['assemblies.items'])->get());

            // Reset form
            $this->cancelAddingItemToAssembly();

            $this->emitPackagesChanged();
            
            session()->flash('message', 'Item added to assembly successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error adding item: ' . $e->getMessage());
        }
    }

    public function editAssemblyItem($packageIndex, $assemblyIndex, $itemIndex)
    {
        $this->editingAssemblyItemData = [
            'package_index' => $packageIndex,
            'assembly_index' => $assemblyIndex,
            'item_index' => $itemIndex,
            'quantity' => $this->packages[$packageIndex]->assemblies[$assemblyIndex]->items[$itemIndex]->quantity
        ];
    }

    public function updateAssemblyItem()
    {
        try {
            if ($this->editingAssemblyItemData['package_index'] === null || 
                $this->editingAssemblyItemData['assembly_index'] === null || 
                $this->editingAssemblyItemData['item_index'] === null || 
                !is_numeric($this->editingAssemblyItemData['quantity']) ||
                $this->editingAssemblyItemData['quantity'] <= 0) {
                throw new \Exception('Invalid item data');
            }

            $packageIndex = $this->editingAssemblyItemData['package_index'];
            $assemblyIndex = $this->editingAssemblyItemData['assembly_index'];
            $itemIndex = $this->editingAssemblyItemData['item_index'];
            
            if (!isset($this->packages[$packageIndex])) {
                throw new \Exception('Package not found');
            }
            
            $package = $this->packages[$packageIndex];
            
            if (!isset($package->assemblies[$assemblyIndex])) {
                throw new \Exception('Assembly not found in package');
            }
            
            $assembly = $package->assemblies[$assemblyIndex];
            
            if (!isset($assembly->items[$itemIndex])) {
                throw new \Exception('Item not found in assembly');
            }
            
            $item = $assembly->items[$itemIndex];
            
            $item->quantity = $this->editingAssemblyItemData['quantity'];
            $item->save();
            
            // Refresh packages from database
            $this->packages = collect($this->estimate->packages()->with(['assemblies.items'])->get());
            
            $this->cancelEditAssemblyItem();
            $this->emitPackagesChanged();
            
            session()->flash('message', 'Item quantity updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating item quantity: ' . $e->getMessage());
        }
    }

    public function cancelEditAssemblyItem()
    {
        $this->editingAssemblyItemData = [
            'package_index' => null,
            'assembly_index' => null,
            'item_index' => null,
            'quantity' => null
        ];
    }

    public function removeAssemblyItem($packageIndex, $assemblyIndex, $itemIndex)
    {
        try {
            if (!isset($this->packages[$packageIndex])) {
                throw new \Exception('Package not found');
            }

            $package = $this->packages[$packageIndex];
            
            if (!isset($package->assemblies[$assemblyIndex])) {
                throw new \Exception('Assembly not found in package');
            }

            $assembly = $package->assemblies[$assemblyIndex];
            
            if (!isset($assembly->items[$itemIndex])) {
                throw new \Exception('Item not found in assembly');
            }

            $item = $assembly->items[$itemIndex];
            
            // Delete the item from the database
            if ($item->id) {
                $item->delete();
            }

            // Refresh packages from database
            $this->packages = collect($this->estimate->packages()->with(['assemblies.items'])->get());
            
            $this->emitPackagesChanged();
            
            session()->flash('message', 'Item removed successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error removing item: ' . $e->getMessage());
        }
    }

    public function togglePackageCollapse($packageIndex)
    {
        $key = array_search($packageIndex, $this->collapsedPackages);
        if ($key !== false) {
            unset($this->collapsedPackages[$key]);
        } else {
            $this->collapsedPackages[] = $packageIndex;
        }
    }

    public function toggleAssemblyCollapse($packageIndex, $assemblyIndex)
    {
        $key = "{$packageIndex}-{$assemblyIndex}";
        $index = array_search($key, $this->collapsedAssemblies);
        if ($index !== false) {
            unset($this->collapsedAssemblies[$index]);
        } else {
            $this->collapsedAssemblies[] = $key;
        }
    }

    protected function emitPackagesChanged()
    {
        $this->dispatch('estimate-packages-updated', [
            'packages' => $this->packages->toArray()
        ]);
    }

    public function render()
    {
        $availablePackages = Package::orderBy('name')->get();
        $availableAssemblies = \App\Models\Assembly::orderBy('name')->get();
        $availableItems = \App\Models\Item::orderBy('name')->get();
        
        return view('livewire.estimates.estimate-packages', [
            'availablePackages' => $availablePackages,
            'availableAssemblies' => $availableAssemblies,
            'availableItems' => $availableItems
        ]);
    }
} 