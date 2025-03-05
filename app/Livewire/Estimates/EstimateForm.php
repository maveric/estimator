<?php

namespace App\Livewire\Estimates;

use App\Models\Item;
use App\Models\Assembly;
use App\Models\LaborRate;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateAssembly;
use App\Models\EstimateAssemblyItem;
use App\Models\Package;
use App\Models\Settings;
use App\Models\EstimatePackage;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class EstimateForm extends Component
{
    public $estimate = null;
    
    // Customer information
    public $customer_name = '';
    public $customer_email = '';
    public $customer_phone = '';
    public $customer_address = '';
    
    // Estimate details
    public $status = 'draft';
    public $markup_percentage = 0;
    public $discount_percentage = 0;
    public $notes = '';
    public $valid_until = '';
    
    // Items and assemblies
    public $items = [];
    public $assemblies = [];
    public $packages = [];
    public $laborRates = [];
    
    // For adding new items
    public $selectedItem = '';
    public $itemQuantity = 1;
    
    // For adding new assemblies
    public $selectedAssembly = '';
    public $assemblyQuantity = 1;
    
    // For editing items
    public $editingItemIndex = null;
    public $editingItemQuantity = null;
    
    // For editing assemblies
    public $editingAssemblyIndex = null;
    public $editingAssemblyQuantity = null;
    
    // For editing packages
    public $editingPackageIndex = null;
    public $editingPackageQuantity = null;
    public $editingPackageAssemblyData = [
        'package_index' => null,
        'assembly_index' => null,
        'quantity' => null
    ];
    
    // For editing assembly items
    public $editingAssemblyItemData = [
        'assembly_index' => null,
        'item_index' => null,
        'quantity' => null
    ];
    
    // For editing package assembly items
    public $editingPackageAssemblyItemData = [
        'package_index' => null,
        'assembly_index' => null,
        'item_index' => null,
        'quantity' => null
    ];
    
    // Calculated totals
    public $totalCost = 0;
    public $totalCharge = 0;
    public $subtotalCost = 0;
    public $subtotalCharge = 0;
    public $markupAmount = 0;
    public $discountAmount = 0;
    
    public $itemSearch = '';
    public $assemblySearch = '';
    public $packageSearch = '';
    
    public $selectedPackage = null;
    public $packageQuantity = 1;
    
    // For adding assemblies to packages
    public $selectedPackageAssembly = null;
    public $packageAssemblyQuantity = 1;
    public $addingAssemblyToPackageIndex = null;
    
    // Collapse state tracking
    public $collapsedPackages = [];
    public $collapsedPackageAssemblies = [];
    public $collapsedAssemblies = [];
    
    protected function rules()
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'status' => 'required|in:draft,sent,approved,declined',
            'markup_percentage' => 'numeric|min:0|max:100',
            'discount_percentage' => 'numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'items' => 'array',
            'assemblies' => 'array',
        ];
    }
    
    public function mount($estimate = null)
    {
        Log::info('Mounting EstimateForm');
        
        // Initialize empty collections
        $this->items = collect();
        $this->assemblies = collect();
        $this->packages = collect();
        
        if ($estimate) {
            if (is_numeric($estimate)) {
                $estimate = Estimate::with(['items', 'assemblies.items', 'packages.assemblies.items'])->findOrFail($estimate);
            }
            
            $this->estimate = $estimate;
            
            // Load customer information
            $this->customer_name = $estimate->customer_name;
            $this->customer_email = $estimate->customer_email;
            $this->customer_phone = $estimate->customer_phone;
            $this->customer_address = $estimate->customer_address;
            
            // Load estimate details
            $this->status = $estimate->status;
            $this->markup_percentage = $estimate->markup_percentage;
            $this->discount_percentage = $estimate->discount_percentage;
            $this->notes = $estimate->notes;
            $this->valid_until = $estimate->valid_until ? $estimate->valid_until->format('Y-m-d') : '';
            
            // Load items and ensure it's a collection
            $this->items = collect($estimate->items);
            
            // Load assemblies with their items
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
            
            // Load packages and ensure it's a collection
            $this->packages = collect($estimate->packages);

            // Initialize all existing items as collapsed
            foreach ($this->packages as $packageIndex => $package) {
                $this->collapsedPackages[] = $packageIndex;
                foreach ($package->assemblies as $assemblyIndex => $assembly) {
                    $this->collapsedPackageAssemblies[] = $packageIndex . '-' . $assemblyIndex;
                }
            }
            
            foreach ($this->assemblies as $assemblyIndex => $assembly) {
                $this->collapsedAssemblies[] = $assemblyIndex;
            }
        } else {
            // Create a new temporary estimate
            $userData = auth()->user();
            
            // Get default labor rate
            $laborRate = LaborRate::where('tenant_id', $userData->current_tenant_id)
                ->where('is_default', true)
                ->first();
                
            if (!$laborRate) {
                // Fallback to any labor rate if no default is set
                $laborRate = LaborRate::where('tenant_id', $userData->current_tenant_id)
                    ->first();
            }
            
            if (!$laborRate) {
                throw new \Exception('No labor rate found. Please create at least one labor rate.');
            }
            
            $this->estimate = new Estimate([
                'tenant_id' => $userData->current_tenant_id,
                'user_id' => $userData->id,
                'name' => 'New Estimate',
                'description' => 'Temporary estimate',
                'customer_name' => 'Temporary Customer',
                'status' => 'draft',
                'version' => 1,
                'is_temporary' => true,
                'labor_rate_id' => $laborRate->id,
                'markup_percentage' => 0,
                'discount_percentage' => 0,
                'estimate_number' => Estimate::max('estimate_number') + 1
            ]);
            
            try {
                $this->estimate->save();
                Log::info('Created temporary estimate:', [
                    'id' => $this->estimate->id,
                    'user_id' => $this->estimate->user_id,
                    'tenant_id' => $this->estimate->tenant_id
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating temporary estimate:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }
        
        Log::info('Mount complete - Collections state:', [
            'items_count' => $this->items->count(),
            'assemblies_count' => $this->assemblies->count(),
            'packages_count' => $this->packages->count()
        ]);
        
        $this->calculateTotals();
    }
    
    public function calculatePackageTotal($package)
    {
        $packageCost = 0;
        $packageCharge = 0;
        
        foreach ($package->assemblies as $assembly) {
            $assemblyItemsCost = 0;
            $assemblyItemsCharge = 0;
            
            foreach ($assembly->items as $item) {
                $assemblyItemsCost += $item->quantity * ($item->material_cost_rate ?? 0);
                $assemblyItemsCharge += $item->quantity * ($item->material_charge_rate ?? 0);
            }
            
            $packageCost += $assemblyItemsCost * $assembly->quantity;
            $packageCharge += $assemblyItemsCharge * $assembly->quantity;
        }
        
        // Multiply by package quantity
        $packageCost *= $package->quantity;
        $packageCharge *= $package->quantity;
        
        return [
            'cost' => $packageCost,
            'charge' => $packageCharge
        ];
    }

    public function calculateTotals()
    {
        // Calculate items cost and charge
        $itemsCost = 0;
        $itemsCharge = 0;
        
        foreach ($this->items as $item) {
            $itemsCost += $item->quantity * $item->material_cost_rate;
            $itemsCharge += $item->quantity * $item->material_charge_rate;
        }
        
        // Calculate assemblies cost and charge
        $assembliesCost = 0;
        $assembliesCharge = 0;
        
        foreach ($this->assemblies as $assembly) {
            $assemblyItemsCost = 0;
            $assemblyItemsCharge = 0;
            
            foreach ($assembly->items as $item) {
                $assemblyItemsCost += $item->quantity * $item->material_cost_rate;
                $assemblyItemsCharge += $item->quantity * $item->material_charge_rate;
            }
            
            $assembliesCost += $assemblyItemsCost * $assembly->quantity;
            $assembliesCharge += $assemblyItemsCharge * $assembly->quantity;
        }
        
        // Calculate packages cost and charge
        $packagesCost = 0;
        $packagesCharge = 0;
        
        foreach ($this->packages as $package) {
            $packageTotal = $this->calculatePackageTotal($package);
            $packagesCost += $packageTotal['cost'];
            $packagesCharge += $packageTotal['charge'];
        }
        
        // Calculate subtotals
        $this->subtotalCost = $itemsCost + $assembliesCost + $packagesCost;
        $this->subtotalCharge = $itemsCharge + $assembliesCharge + $packagesCharge;
        
        // Apply markup
        $this->markupAmount = 0;
        if ($this->markup_percentage > 0) {
            $this->markupAmount = $this->subtotalCharge * $this->markup_percentage / 100;
        }
        
        // Apply discount
        $this->discountAmount = 0;
        $chargeAfterMarkup = $this->subtotalCharge + $this->markupAmount;
        if ($this->discount_percentage > 0) {
            $this->discountAmount = $chargeAfterMarkup * $this->discount_percentage / 100;
        }
        
        // Calculate totals
        $this->totalCost = $this->subtotalCost;
        $this->totalCharge = $chargeAfterMarkup - $this->discountAmount;
    }
    
    public function addItem()
    {
        if (empty($this->selectedItem)) {
            session()->flash('error', 'Please select an item to add.');
            return;
        }
        
        if ($this->itemQuantity <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }
        
        try {
            $item = Item::findOrFail($this->selectedItem);
            
            // Get default labor rate
            $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                ->where('is_default', true)
                ->first();
                
            if (!$laborRate) {
                // Fallback to any labor rate if no default is set
                $laborRate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                    ->first();
            }
            
            $laborRateId = $laborRate ? $laborRate->id : null;
            
            \Log::Info('items before adding item', $this->items->toArray());
            // Create a new EstimateItem model instance
            $estimateItem = new EstimateItem([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_id' => $this->estimate->id,  // Always set estimate_id since we always have one
                'item_id' => $item->id,
                'original_item_id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => $this->itemQuantity,
                'unit_of_measure' => $item->unit_of_measure,
                'material_cost_rate' => $item->material_cost_rate,
                'material_charge_rate' => $item->material_charge_rate,
                'labor_units' => $item->labor_units,
                'labor_rate_id' => $laborRateId,
                'original_cost_rate' => $item->material_cost_rate,
                'original_charge_rate' => $item->material_charge_rate,
            ]);

            // Always save since we always have an estimate
            $estimateItem->save();
            
            // Ensure items is a collection
            if (!($this->items instanceof \Illuminate\Support\Collection)) {
                $this->items = collect($this->items);
            }
            
            $this->items->push($estimateItem);
            
            $this->selectedItem = '';
            $this->itemQuantity = 1;
            
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::error('Error adding item to estimate', [
                'error' => $e->getMessage()
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
        if ($this->editingItemQuantity <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }
        
        // Get the item from the collection using get() to ensure we have the model instance
        $item = $this->items->get($this->editingItemIndex);
        $item->quantity = $this->editingItemQuantity;
        $item->save(); // Save to database
        
        $this->editingItemIndex = null;
        $this->editingItemQuantity = null;
        
        $this->calculateTotals();
    }
    
    public function cancelEditItem()
    {
        $this->editingItemIndex = null;
        $this->editingItemQuantity = null;
    }
    
    public function removeItem($index)
    {
        // Ensure items is a collection
        if (!($this->items instanceof \Illuminate\Support\Collection)) {
            $this->items = collect($this->items);
        }
        
        // Remove the item and re-index the collection
        // Note: We need to reassign because collections are immutable
        $this->items = $this->items->forget($index)->values();
        
        $this->calculateTotals();
    }
    
    public function addAssembly()
    {
        if (empty($this->selectedAssembly)) {
            session()->flash('error', 'Please select an assembly to add.');
            return;
        }
        
        if ($this->assemblyQuantity <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }
        
        try {
            Log::info('Before adding assembly - Current state:', [
                'items_count' => $this->items instanceof \Illuminate\Support\Collection ? $this->items->count() : 'not a collection',
                'items_type' => gettype($this->items),
                'assemblies_count' => $this->assemblies instanceof \Illuminate\Support\Collection ? $this->assemblies->count() : 'not a collection',
                'assemblies_type' => gettype($this->assemblies),
                'first_item' => $this->items->first() ? get_class($this->items->first()) : 'no items'
            ]);
            
            $assembly = Assembly::with('items')->findOrFail($this->selectedAssembly);
            
            // Get labor rate
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
            
            // Ensure collections are properly initialized
            if (!($this->items instanceof \Illuminate\Support\Collection)) {
                $this->items = collect($this->items);
            }
            
            if (!($this->assemblies instanceof \Illuminate\Support\Collection)) {
                $this->assemblies = collect($this->assemblies);
            }
            
            // Create the estimate assembly
            $estimateAssembly = new EstimateAssembly([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_id' => $this->estimate->id,
                'assembly_id' => $assembly->id,
                'original_assembly_id' => $assembly->id,
                'name' => $assembly->name,
                'description' => $assembly->description,
                'quantity' => $this->assemblyQuantity
            ]);

            // Always save since we always have an estimate
            $estimateAssembly->save();

            // Initialize items collection for this assembly
            $estimateAssembly->items = collect();
            
            // Add items from the assembly
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
                    'labor_rate_id' => $laborRate->id,
                    'original_cost_rate' => $item->material_cost_rate,
                    'original_charge_rate' => $item->material_charge_rate,
                ]);
                
                // Always save since we always have an assembly
                $estimateItem->save();
                
                $estimateAssembly->items->push($estimateItem);
            }
            
            // Add the assembly to our collection
            $this->assemblies->push($estimateAssembly);
            
            // Collapse the newly added assembly
            $this->collapsedAssemblies[] = $this->assemblies->count() - 1;
            
            Log::info('After adding assembly - Final state:', [
                'items_count' => $this->items->count(),
                'assemblies_count' => $this->assemblies->count(),
                'first_item' => $this->items->first() ? get_class($this->items->first()) : 'no items',
                'first_assembly' => $this->assemblies->first() ? get_class($this->assemblies->first()) : 'no assemblies',
                'items_data' => $this->items->map(function($item) {
                    return [
                        'id' => $item->item_id,
                        'name' => $item->name,
                        'quantity' => $item->quantity
                    ];
                })->toArray()
            ]);
            
            $this->selectedAssembly = '';
            $this->assemblyQuantity = 1;
            
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::error('Error adding assembly to estimate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'selectedAssembly' => $this->selectedAssembly,
                'assemblyQuantity' => $this->assemblyQuantity
            ]);
            session()->flash('error', 'Error adding assembly: ' . $e->getMessage());
        }
    }
    
    public function editAssembly($index)
    {
        $this->editingAssemblyIndex = $index;
        $this->editingAssemblyQuantity = $this->assemblies[$index]->quantity;
    }
    
    public function updateAssembly()
    {
        if ($this->editingAssemblyQuantity <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }
        
        // Get the assembly from the collection using get() to ensure we have the model instance
        $assembly = $this->assemblies->get($this->editingAssemblyIndex);
        $assembly->quantity = $this->editingAssemblyQuantity;
        $assembly->save(); // Save to database
        
        $this->editingAssemblyIndex = null;
        $this->editingAssemblyQuantity = null;
        
        $this->calculateTotals();
    }
    
    public function cancelEditAssembly()
    {
        $this->editingAssemblyIndex = null;
        $this->editingAssemblyQuantity = null;
    }
    
    public function removeAssembly($index)
    {
        // Ensure assemblies is a collection
        if (!($this->assemblies instanceof \Illuminate\Support\Collection)) {
            $this->assemblies = collect($this->assemblies);
        }
        
        // Remove the assembly and re-index the collection
        // Note: We need to reassign because collections are immutable
        $this->assemblies = $this->assemblies->forget($index)->values();
        
        $this->calculateTotals();
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
        if ($this->editingAssemblyItemData['quantity'] <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }
        
        $assemblyIndex = $this->editingAssemblyItemData['assembly_index'];
        $itemIndex = $this->editingAssemblyItemData['item_index'];
        
        // Get the assembly and item using collection methods
        $assembly = $this->assemblies->get($assemblyIndex);
        if (!($assembly->items instanceof \Illuminate\Support\Collection)) {
            $assembly->items = collect($assembly->items);
        }
        $item = $assembly->items->get($itemIndex);
        $item->quantity = $this->editingAssemblyItemData['quantity'];
        $item->save(); // Save to database
        
        $this->editingAssemblyItemData = [
            'assembly_index' => null,
            'item_index' => null,
            'quantity' => null
        ];
        
        $this->calculateTotals();
    }
    
    public function cancelEditAssemblyItem()
    {
        $this->editingAssemblyItemData = [
            'assembly_index' => null,
            'item_index' => null,
            'quantity' => null
        ];
    }
    
    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $estimateData = [
                'customer_name' => $this->customer_name,
                'customer_email' => $this->customer_email,
                'customer_phone' => $this->customer_phone,
                'customer_address' => $this->customer_address,
                'status' => $this->status,
                'markup_percentage' => $this->markup_percentage,
                'discount_percentage' => $this->discount_percentage,
                'notes' => $this->notes,
                'valid_until' => $this->valid_until ?: null,
                'is_temporary' => false  // Always set to false when saving
            ];
            
            // Update the estimate
            $this->estimate->update($estimateData);
            
            // Create a new version snapshot
            $this->estimate->createVersionSnapshot();
            
            DB::commit();
            
            session()->flash('message', 'Estimate saved successfully.');
            
            return redirect()->route('estimates.index');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error saving estimate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Error saving estimate: ' . $e->getMessage());
        }
    }
    
    public function cancel()
    {
        return redirect()->route('estimates.index');
    }
    
    public function render(): View
    {
        $filteredItems = $this->items;
        if ($this->itemSearch) {
            $filteredItems = $filteredItems->filter(function ($item) {
                return str_contains(strtolower($item->name), strtolower($this->itemSearch)) ||
                    str_contains(strtolower($item->description), strtolower($this->itemSearch));
            });
        }

        $filteredAssemblies = $this->assemblies;
        if ($this->assemblySearch) {
            $filteredAssemblies = $filteredAssemblies->filter(function ($assembly) {
                return str_contains(strtolower($assembly->name), strtolower($this->assemblySearch)) ||
                    str_contains(strtolower($assembly->description), strtolower($this->assemblySearch));
            });
        }
        
        $filteredPackages = $this->packages;
        if ($this->packageSearch) {
            $filteredPackages = $filteredPackages->filter(function ($package) {
                return str_contains(strtolower($package->name), strtolower($this->packageSearch)) ||
                    str_contains(strtolower($package->description), strtolower($this->packageSearch));
            });
        }

        $availableItems = Item::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $availableAssemblies = Assembly::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $availablePackages = Package::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $availableLaborRates = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
            ->orderBy('name')
            ->get();
            
        return view('livewire.estimates.form', [
            'filteredItems' => $filteredItems,
            'filteredAssemblies' => $filteredAssemblies,
            'filteredPackages' => $filteredPackages,
            'availableItems' => $availableItems,
            'availableAssemblies' => $availableAssemblies,
            'availablePackages' => $availablePackages,
            'availableLaborRates' => $availableLaborRates,
        ]);
    }

    public function addPackage()
    {
        try {
            if (!$this->selectedPackage) {
                throw new \Exception('Please select a package to add.');
            }

            if ($this->packageQuantity <= 0) {
                throw new \Exception('Quantity must be greater than zero.');
            }

            // Load the package with all necessary relationships
            $package = Package::with(['assemblies.items'])->findOrFail($this->selectedPackage);
            
            Log::info('Before adding package - Current state:', [
                'items_count' => $this->items instanceof \Illuminate\Support\Collection ? $this->items->count() : 'not a collection',
                'assemblies_count' => $this->assemblies instanceof \Illuminate\Support\Collection ? $this->assemblies->count() : 'not a collection',
                'packages_count' => $this->packages instanceof \Illuminate\Support\Collection ? $this->packages->count() : 'not a collection',
                'first_item' => $this->items->first() ? get_class($this->items->first()) : 'no items'
            ]);
            
            // Ensure packages is a collection
            if (!($this->packages instanceof \Illuminate\Support\Collection)) {
                $this->packages = collect($this->packages);
            }
            
            // Create and save the estimate package
            $estimatePackage = EstimatePackage::create([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_id' => $this->estimate->id,
                'package_id' => $package->id,
                'original_package_id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'quantity' => $this->packageQuantity,
            ]);

            Log::info('Created EstimatePackage:', [
                'package_id' => $estimatePackage->id,
                'name' => $estimatePackage->name
            ]);

            // Add assemblies from the package
            foreach ($package->assemblies as $assembly) {
                // Create the estimate assembly
                $estimateAssembly = EstimateAssembly::create([
                    'tenant_id' => auth()->user()->current_tenant_id,
                    'estimate_package_id' => $estimatePackage->id,
                    'assembly_id' => $assembly->id,
                    'original_assembly_id' => $assembly->id,
                    'name' => $assembly->name,
                    'description' => $assembly->description,
                    'quantity' => $assembly->pivot->quantity,
                ]);

                Log::info('Created EstimateAssembly:', [
                    'assembly_id' => $estimateAssembly->id,
                    'name' => $estimateAssembly->name,
                    'estimate_package_id' => $estimateAssembly->estimate_package_id
                ]);

                // Add items from the assembly
                foreach ($assembly->items as $item) {
                    // Get primary labor rate from settings
                    $laborRate = Settings::getPrimaryLaborRate();
                    
                    if (!$laborRate || !is_object($laborRate)) {
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
                    }

                    // Create the estimate item
                    EstimateItem::create([
                        'tenant_id' => auth()->user()->current_tenant_id,
                        'estimate_assembly_id' => $estimateAssembly->id,
                        'item_id' => $item->id,
                        'original_item_id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'quantity' => $item->pivot->quantity,
                        'unit_of_measure' => $item->unit_of_measure,
                        'material_cost_rate' => $item->material_cost_rate,
                        'material_charge_rate' => $item->material_charge_rate,
                        'labor_units' => $item->labor_units,
                        'labor_rate_id' => $laborRate->id,
                        'original_cost_rate' => $item->material_cost_rate,
                        'original_charge_rate' => $item->material_charge_rate,
                    ]);
                }
            }

            // Refresh the estimate
            $this->estimate = Estimate::with([
                'packages',
                'packages.assemblies',
                'packages.assemblies.items'
            ])->findOrFail($this->estimate->id);
            
            // Update the packages array with the refreshed data
            $this->packages = $this->estimate->packages;
            
            // Collapse the newly added package and its assemblies
            $newPackageIndex = $this->packages->count() - 1;
            $this->collapsedPackages[] = $newPackageIndex;
            
            $package = $this->packages->last();
            foreach ($package->assemblies as $assemblyIndex => $assembly) {
                $this->collapsedPackageAssemblies[] = $newPackageIndex . '-' . $assemblyIndex;
            }

            $this->selectedPackage = null;
            $this->packageQuantity = 1;
            $this->calculateTotals();
            $this->dispatch('package-added');
            session()->flash('message', 'Package added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding package', [
                'error' => $e->getMessage(),
                'estimate_id' => $this->estimate ? $this->estimate->id : null,
                'selected_package' => $this->selectedPackage
            ]);
            session()->flash('error', 'Error adding package: ' . $e->getMessage());
        }
    }

    protected function loadAvailablePackages()
    {
        $tenantId = Auth::user()->current_tenant_id;
        $this->availablePackages = Package::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function editPackage($index)
    {
        $this->editingPackageIndex = $index;
        $this->editingPackageQuantity = $this->packages[$index]->quantity;
    }
    
    public function updatePackage()
    {
        if ($this->editingPackageQuantity <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }
        
        // Get the package using collection methods
        $package = $this->packages->get($this->editingPackageIndex);
        $package->quantity = $this->editingPackageQuantity;
        $package->save(); // Save to database
        
        $this->editingPackageIndex = null;
        $this->editingPackageQuantity = null;
        
        $this->calculateTotals();
    }
    
    public function cancelEditPackage()
    {
        $this->editingPackageIndex = null;
        $this->editingPackageQuantity = null;
    }
    
    public function removePackage($index)
    {
        // Ensure packages is a collection
        if (!($this->packages instanceof \Illuminate\Support\Collection)) {
            $this->packages = collect($this->packages);
        }
        
        // If package has an ID, delete from database
        if ($this->packages->get($index)->id) {
            EstimatePackage::where('id', $this->packages->get($index)->id)->delete();
        }
        
        // Remove the package and re-index the collection
        $this->packages = $this->packages->forget($index)->values();
        
        $this->calculateTotals();
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
        if ($this->editingPackageAssemblyData['quantity'] <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }
        
        $packageIndex = $this->editingPackageAssemblyData['package_index'];
        $assemblyIndex = $this->editingPackageAssemblyData['assembly_index'];
        
        // Get the package and assembly using collection methods
        $package = $this->packages->get($packageIndex);
        if (!($package->assemblies instanceof \Illuminate\Support\Collection)) {
            $package->assemblies = collect($package->assemblies);
        }
        $assembly = $package->assemblies->get($assemblyIndex);
        $assembly->quantity = $this->editingPackageAssemblyData['quantity'];
        $assembly->save(); // Save to database
        
        $this->editingPackageAssemblyData = [
            'package_index' => null,
            'assembly_index' => null,
            'quantity' => null
        ];
        
        $this->calculateTotals();
    }
    
    public function cancelEditPackageAssembly()
    {
        $this->editingPackageAssemblyData = [
            'package_index' => null,
            'assembly_index' => null,
            'quantity' => null
        ];
    }

    public function editPackageAssemblyItem($packageIndex, $assemblyIndex, $itemIndex)
    {
        $item = $this->packages[$packageIndex]->assemblies[$assemblyIndex]->items[$itemIndex];
        $this->editingPackageAssemblyItemData = [
            'package_index' => $packageIndex,
            'assembly_index' => $assemblyIndex,
            'item_index' => $itemIndex,
            'quantity' => $item->quantity
        ];
    }

    public function updatePackageAssemblyItem()
    {
        $packageIndex = $this->editingPackageAssemblyItemData['package_index'];
        $assemblyIndex = $this->editingPackageAssemblyItemData['assembly_index'];
        $itemIndex = $this->editingPackageAssemblyItemData['item_index'];
        $quantity = $this->editingPackageAssemblyItemData['quantity'];

        if ($quantity <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }

        try {
            $item = $this->packages[$packageIndex]->assemblies[$assemblyIndex]->items[$itemIndex];
            $item->quantity = $quantity;
            $item->save();

            $this->cancelEditPackageAssemblyItem();
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::error('Error updating package assembly item', [
                'error' => $e->getMessage(),
                'package_index' => $packageIndex,
                'assembly_index' => $assemblyIndex,
                'item_index' => $itemIndex
            ]);
            session()->flash('error', 'Error updating item quantity.');
        }
    }

    public function cancelEditPackageAssemblyItem()
    {
        $this->editingPackageAssemblyItemData = [
            'package_index' => null,
            'assembly_index' => null,
            'item_index' => null,
            'quantity' => null
        ];
    }

    public function togglePackageCollapse($packageIndex)
    {
        if (in_array($packageIndex, $this->collapsedPackages)) {
            $this->collapsedPackages = array_diff($this->collapsedPackages, [$packageIndex]);
        } else {
            $this->collapsedPackages[] = $packageIndex;
        }
    }

    public function togglePackageAssemblyCollapse($packageIndex, $assemblyIndex)
    {
        $key = $packageIndex . '-' . $assemblyIndex;
        if (in_array($key, $this->collapsedPackageAssemblies)) {
            $this->collapsedPackageAssemblies = array_diff($this->collapsedPackageAssemblies, [$key]);
        } else {
            $this->collapsedPackageAssemblies[] = $key;
        }
    }

    public function toggleAssemblyCollapse($assemblyIndex)
    {
        if (in_array($assemblyIndex, $this->collapsedAssemblies)) {
            $this->collapsedAssemblies = array_diff($this->collapsedAssemblies, [$assemblyIndex]);
        } else {
            $this->collapsedAssemblies[] = $assemblyIndex;
        }
    }

    public function addAssemblyToPackage($packageIndex)
    {
        if (!$this->selectedPackageAssembly) {
            session()->flash('error', 'Please select an assembly to add.');
            return;
        }

        if ($this->packageAssemblyQuantity <= 0) {
            session()->flash('error', 'Quantity must be greater than zero.');
            return;
        }

        try {
            // Get the package we're adding to
            $package = $this->packages->get($packageIndex);
            if (!$package) {
                throw new \Exception('Package not found.');
            }

            // Load the assembly with its items
            $assembly = Assembly::with('items')->findOrFail($this->selectedPackageAssembly);
            
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

            // Create the estimate assembly
            $estimateAssembly = EstimateAssembly::create([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_package_id' => $package->id,
                'assembly_id' => $assembly->id,
                'original_assembly_id' => $assembly->id,
                'name' => $assembly->name,
                'description' => $assembly->description,
                'quantity' => $this->packageAssemblyQuantity
            ]);

            // Add items from the assembly
            foreach ($assembly->items as $item) {
                EstimateItem::create([
                    'tenant_id' => auth()->user()->current_tenant_id,
                    'estimate_assembly_id' => $estimateAssembly->id,
                    'item_id' => $item->id,
                    'original_item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->pivot->quantity,
                    'unit_of_measure' => $item->unit_of_measure,
                    'material_cost_rate' => $item->material_cost_rate,
                    'material_charge_rate' => $item->material_charge_rate,
                    'labor_units' => $item->labor_units,
                    'labor_rate_id' => $laborRate->id,
                    'original_cost_rate' => $item->material_cost_rate,
                    'original_charge_rate' => $item->material_charge_rate,
                ]);
            }

            // Refresh the estimate to get the updated data
            $this->estimate = Estimate::with([
                'packages',
                'packages.assemblies',
                'packages.assemblies.items'
            ])->findOrFail($this->estimate->id);
            
            // Update the packages array with the refreshed data
            $this->packages = $this->estimate->packages;

            // Collapse the newly added assembly
            $this->collapsedPackageAssemblies[] = $packageIndex . '-' . ($package->assemblies->count() - 1);

            // Reset the form
            $this->selectedPackageAssembly = null;
            $this->packageAssemblyQuantity = 1;
            $this->addingAssemblyToPackageIndex = null;

            $this->calculateTotals();
            session()->flash('message', 'Assembly added to package successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding assembly to package', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding assembly to package: ' . $e->getMessage());
        }
    }

    public function startAddingAssemblyToPackage($packageIndex)
    {
        $this->addingAssemblyToPackageIndex = $packageIndex;
        $this->selectedPackageAssembly = null;
        $this->packageAssemblyQuantity = 1;
    }

    public function cancelAddingAssemblyToPackage()
    {
        $this->addingAssemblyToPackageIndex = null;
        $this->selectedPackageAssembly = null;
        $this->packageAssemblyQuantity = 1;
    }
} 