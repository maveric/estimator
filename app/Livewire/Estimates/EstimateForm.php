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
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class EstimateForm extends Component
{
    public $estimate = null;
    
    // Add back customer information properties
    public $customer_name = '';
    public $customer_email = '';
    public $customer_phone = '';
    public $customer_address = '';
    public $valid_until = '';
    
    // Items and assemblies
    public $items = [];
    public $assemblies = [];
    public $packages = [];
    public $laborRates = [];
    
    // For adding new assemblies
    public $selectedAssembly = '';
    public $assemblyQuantity = 1;
    
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
    
    #[On('customer-information-updated')]
    public function handleCustomerInformationUpdate($data)
    {
        $this->customer_name = $data['customer_name'] ?? '';
        $this->customer_email = $data['customer_email'] ?? '';
        $this->customer_phone = $data['customer_phone'] ?? '';
        $this->customer_address = $data['customer_address'] ?? '';
        $this->valid_until = $data['valid_until'] ?? '';
    }

    #[On('estimate-details-updated')]
    public function handleEstimateDetailsUpdate($data)
    {
        try {
            // Refresh the estimate with all necessary relationships
            $this->estimate = Estimate::with([
                'items',
                'assemblies.items',
                'packages.assemblies.items'
            ])->find($this->estimate->id);
            
            // Log the markup percentage for debugging
            Log::info('Estimate details updated:', [
                'estimate_id' => $this->estimate->id,
                'markup_percentage' => $this->estimate->markup_percentage,
                'discount_percentage' => $this->estimate->discount_percentage
            ]);
            
            // Recalculate totals with the fresh data
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::error('Error handling estimate details update:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating totals: ' . $e->getMessage());
        }
    }

    #[On('estimate-items-updated')]
    public function handleItemsUpdate($data)
    {
        try {
            // Refresh the estimate with all necessary relationships
            $this->estimate = Estimate::with([
                'items',
                'assemblies.items',
                'packages.assemblies.items'
            ])->find($this->estimate->id);
            
            // Update the items collection
            $this->items = collect($this->estimate->items);
            
            // Recalculate totals with the fresh data
            $this->calculateTotals();
            
            // Dispatch an event to notify other components
            $this->dispatch('totals-updated', [
                'totalCost' => $this->totalCost,
                'totalCharge' => $this->totalCharge
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in handleItemsUpdate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating items: ' . $e->getMessage());
        }
    }

    #[On('assembly-items-updated')]
    public function handleAssemblyItemsUpdate($data = [])
    {
        try {
            // Refresh the estimate with all necessary relationships
            $this->estimate = Estimate::with([
                'items',
                'assemblies.items',
                'packages.assemblies.items'
            ])->find($this->estimate->id);
            
            // Update the assemblies collection
            $this->assemblies = collect($this->estimate->assemblies);
            
            // Recalculate totals with the fresh data
            $this->calculateTotals();
            
            // Dispatch an event to notify other components
            $this->dispatch('totals-updated', [
                'totalCost' => $this->totalCost,
                'totalCharge' => $this->totalCharge
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in handleAssemblyItemsUpdate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            session()->flash('error', 'Error updating assembly items: ' . $e->getMessage());
        }
    }

    #[On('estimate-assemblies-updated')]
    public function handleAssembliesUpdate($data)
    {
        try {
            $this->assemblies = collect($data['assemblies']);
            $this->calculateTotals();
        } catch (\Exception $e) {
            \Log::error('Error in handleAssembliesUpdate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating assemblies: ' . $e->getMessage());
        }
    }

    #[On('estimate-packages-updated')]
    public function handlePackagesUpdate($data)
    {
        try {
            // Refresh the estimate with all necessary relationships
            $this->estimate = Estimate::with([
                'items',
                'assemblies.items',
                'packages.assemblies.items'
            ])->find($this->estimate->id);
            
            // Update the packages collection
            $this->packages = collect($this->estimate->packages);
            
            // Recalculate totals with the fresh data
            $this->calculateTotals();
            
            // Dispatch an event to notify other components
            $this->dispatch('totals-updated', [
                'totalCost' => $this->totalCost,
                'totalCharge' => $this->totalCharge
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in handlePackagesUpdate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating packages: ' . $e->getMessage());
        }
    }

    #[On('estimate-assembly-updated')]
    public function handleAssemblyUpdate($data)
    {
        try {
            // Refresh the estimate with all necessary relationships
            $this->estimate = Estimate::with([
                'items',
                'assemblies.items',
                'packages.assemblies.items'
            ])->find($this->estimate->id);
            
            // Update the assemblies collection
            $this->assemblies = collect($this->estimate->assemblies);
            
            // Recalculate totals with the fresh data
            $this->calculateTotals();
            
            // Dispatch an event to notify other components
            $this->dispatch('totals-updated', [
                'totalCost' => $this->totalCost,
                'totalCharge' => $this->totalCharge
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in handleAssemblyUpdate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating assembly: ' . $e->getMessage());
        }
    }

    protected function rules()
    {
        return [
            // Customer information validation rules
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
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
                $estimate = Estimate::with([
                    'items',
                    'assemblies.items.laborRate',
                    'packages.assemblies.items.laborRate'
                ])->findOrFail($estimate);
            }
            
            $this->estimate = $estimate;
            
            // Add back customer information initialization
            $this->customer_name = $estimate->customer_name;
            $this->customer_email = $estimate->customer_email;
            $this->customer_phone = $estimate->customer_phone;
            $this->customer_address = $estimate->customer_address;
            $this->valid_until = $estimate->valid_until ? $estimate->valid_until->format('Y-m-d') : '';
            
            // Load items and ensure it's a collection
            $this->items = collect($estimate->items);
            
            // Load assemblies with their items
            $this->assemblies = collect($estimate->assemblies->map(function ($assembly) {
                $assembly->items = collect($assembly->items->map(function ($item) {
                    return new EstimateItem([
                        'id' => $item->id,
                        'tenant_id' => auth()->user()->current_tenant_id,
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
            
            // Load packages with their assemblies and items
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
            $tenantId = $userData->current_tenant_id;
            
            // Get default labor rate
            $laborRate = LaborRate::where('tenant_id', $tenantId)
                ->where('is_default', true)
                ->first();
                
            if (!$laborRate) {
                // Fallback to any labor rate if no default is set
                $laborRate = LaborRate::where('tenant_id', $tenantId)
                    ->first();
            }
            
            if (!$laborRate) {
                throw new \Exception('No labor rate found. Please create at least one labor rate.');
            }
            
            $this->estimate = new Estimate([
                'tenant_id' => $tenantId,
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
                'estimate_number' => Estimate::getNextEstimateNumber($tenantId)
            ]);
            
            try {
                DB::transaction(function () {
                    $this->estimate->save();
                    
                    // Ensure the estimate is properly loaded with all relationships
                    $this->estimate = Estimate::with([
                        'items',
                        'assemblies.items.laborRate',
                        'packages.assemblies.items.laborRate'
                    ])->find($this->estimate->id);
                    
                    Log::info('Created temporary estimate:', [
                        'id' => $this->estimate->id,
                        'user_id' => $this->estimate->user_id,
                        'tenant_id' => $this->estimate->tenant_id
                    ]);
                });
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
        
        // Get the primary labor rate
        $primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->active()
            ->first();
            
        if (!$primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }
        
        foreach ($package->assemblies as $assembly) {
            $assemblyItemsCost = 0;
            $assemblyItemsCharge = 0;
            
            foreach ($assembly->items as $item) {
                // Calculate material costs
                $assemblyItemsCost += $item->quantity * ($item->material_cost_rate ?? 0);
                $assemblyItemsCharge += $item->quantity * ($item->material_charge_rate ?? 0);
                
                // Calculate labor costs (convert minutes to hours)
                $laborHours = ($item->labor_units * $item->quantity) / 60;
                $assemblyItemsCost += $laborHours * $primaryLaborRate->cost_rate;
                $assemblyItemsCharge += $laborHours * $primaryLaborRate->charge_rate;
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
        // Add logging at the start of calculation
        Log::info('Starting calculateTotals:', [
            'estimate_id' => $this->estimate->id,
            'markup_percentage' => $this->estimate->markup_percentage ?? 'not set',
            'discount_percentage' => $this->estimate->discount_percentage ?? 'not set'
        ]);

        // Get the primary labor rate
        $primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->active()
            ->first();
            
        if (!$primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }

        // Calculate items cost and charge
        $itemsCost = 0;
        $itemsCharge = 0;
        
        foreach ($this->items as $item) {
            // Material costs
            $itemsCost += $item->quantity * $item->material_cost_rate;
            $itemsCharge += $item->quantity * $item->material_charge_rate;
            
            // Labor costs (convert minutes to hours)
            $laborHours = ($item->labor_units * $item->quantity) / 60;
            
            // Use item's labor rate if set, otherwise use primary labor rate
            $laborRate = $item->laborRate ?? $primaryLaborRate;
            $itemsCost += $laborHours * $laborRate->cost_rate;
            $itemsCharge += $laborHours * $laborRate->charge_rate;
        }
        
        // Calculate assemblies cost and charge
        $assembliesCost = 0;
        $assembliesCharge = 0;
        
        foreach ($this->assemblies as $assembly) {
            $assemblyItemsCost = 0;
            $assemblyItemsCharge = 0;
            
            foreach ($assembly->items as $item) {
                // Material costs
                $assemblyItemsCost += $item->quantity * $item->material_cost_rate;
                $assemblyItemsCharge += $item->quantity * $item->material_charge_rate;
                
                // Labor costs (convert minutes to hours)
                $laborHours = ($item->labor_units * $item->quantity) / 60;
                
                // Use item's labor rate if set, otherwise use primary labor rate
                $laborRate = $item->laborRate ?? $primaryLaborRate;
                $assemblyItemsCost += $laborHours * $laborRate->cost_rate;
                $assemblyItemsCharge += $laborHours * $laborRate->charge_rate;
            }
            
            $assembliesCost += $assemblyItemsCost * $assembly->quantity;
            $assembliesCharge += $assemblyItemsCharge * $assembly->quantity;
        }
        
        // Calculate packages cost and charge
        $packagesCost = 0;
        $packagesCharge = 0;
        
        if ($this->estimate) {
            $packages = $this->estimate->packages()->with(['assemblies.items'])->get();
            foreach ($packages as $package) {
                $totals = $this->calculatePackageTotal($package);
                $packagesCost += $totals['cost'];
                $packagesCharge += $totals['charge'];
            }
        }
        
        // Calculate subtotals
        $this->subtotalCost = $itemsCost + $assembliesCost + $packagesCost;
        $this->subtotalCharge = $itemsCharge + $assembliesCharge + $packagesCharge;
        
        // Log subtotals before markup/discount
        Log::info('Subtotals calculated:', [
            'subtotalCost' => $this->subtotalCost,
            'subtotalCharge' => $this->subtotalCharge
        ]);
        
        // Apply markup
        $this->markupAmount = 0;
        if ($this->estimate && $this->estimate->markup_percentage > 0) {
            $this->markupAmount = $this->subtotalCharge * ($this->estimate->markup_percentage / 100);
            
            // Log markup calculation
            Log::info('Markup applied:', [
                'markup_percentage' => $this->estimate->markup_percentage,
                'markupAmount' => $this->markupAmount
            ]);
        }
        
        // Apply discount
        $this->discountAmount = 0;
        $chargeAfterMarkup = $this->subtotalCharge + $this->markupAmount;
        if ($this->estimate && $this->estimate->discount_percentage > 0) {
            $this->discountAmount = $chargeAfterMarkup * ($this->estimate->discount_percentage / 100);
            
            // Log discount calculation
            Log::info('Discount applied:', [
                'discount_percentage' => $this->estimate->discount_percentage,
                'discountAmount' => $this->discountAmount
            ]);
        }
        
        // Calculate totals
        $this->totalCost = $this->subtotalCost;
        $this->totalCharge = $chargeAfterMarkup - $this->discountAmount;
        
        // Log final totals
        Log::info('Final totals:', [
            'totalCost' => $this->totalCost,
            'totalCharge' => $this->totalCharge
        ]);

        // Update the database with new totals
        if ($this->estimate) {
            try {
                DB::transaction(function () {
                    $this->estimate->update([
                        'total_cost' => $this->totalCost,
                        'total_charge' => $this->totalCharge
                    ]);
                });
            } catch (\Exception $e) {
                Log::error('Error updating estimate totals:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'estimate_id' => $this->estimate->id
                ]);
                session()->flash('warning', 'Warning: Unable to update estimate totals in database.');
            }
        }
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
            $assembly = Assembly::with('items')->findOrFail($this->selectedAssembly);
            
            // Get labor rate
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
            
            DB::transaction(function () use ($assembly, $laborRate) {
                // Create the estimate assembly
                $estimateAssembly = EstimateAssembly::create([
                    'tenant_id' => auth()->user()->current_tenant_id,
                    'estimate_id' => $this->estimate->id,
                    'assembly_id' => $assembly->id,
                    'original_assembly_id' => $assembly->id,
                    'name' => $assembly->name,
                    'description' => $assembly->description,
                    'quantity' => $this->assemblyQuantity
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
                        'unit_of_measure' => $item->unit_of_measure,
                        'quantity' => $item->pivot->quantity,
                        'material_cost_rate' => $item->material_cost_rate,
                        'material_charge_rate' => $item->material_charge_rate,
                        'labor_units' => $item->labor_units,
                        'labor_rate_id' => $laborRate->id,
                        'original_cost_rate' => $item->material_cost_rate,
                        'original_charge_rate' => $item->material_charge_rate,
                    ]);
                }
            });

            // Refresh the estimate
            $this->estimate = Estimate::with([
                'items',
                'assemblies.items',
                'packages.assemblies.items'
            ])->find($this->estimate->id);
            
            // Update assemblies collection
            $this->assemblies = collect($this->estimate->assemblies);
            
            // Reset form
            $this->selectedAssembly = '';
            $this->assemblyQuantity = 1;
            
            // Recalculate totals
            $this->calculateTotals();
            
        } catch (\Exception $e) {
            Log::error('Error adding assembly to estimate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding assembly: ' . $e->getMessage());
        }
    }
    
    public function removeAssembly($index)
    {
        try {
            $assembly = $this->assemblies->get($index);
            
            if ($assembly && $assembly->id) {
                DB::transaction(function () use ($assembly) {
                    // Delete all items associated with this assembly
                    EstimateItem::where('estimate_assembly_id', $assembly->id)->delete();
                    
                    // Delete the assembly
                    $assembly->delete();
                });
            }
            
            // Refresh the estimate
            $this->estimate = Estimate::with([
                'items',
                'assemblies.items',
                'packages.assemblies.items'
            ])->find($this->estimate->id);
            
            // Update assemblies collection
            $this->assemblies = collect($this->estimate->assemblies);
            
            // Recalculate totals
            $this->calculateTotals();
            
        } catch (\Exception $e) {
            Log::error('Error removing assembly', [
                'error' => $e->getMessage(),
                'index' => $index
            ]);
            session()->flash('error', 'Error removing assembly: ' . $e->getMessage());
        }
    }
    
    public function editAssembly($index) {}
    public function updateAssembly() {}
    public function cancelEditAssembly() {}
    
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
        Log::info('updateAssemblyItem called - INITIAL CHECK');
        
        try {
            // First, log the raw data we're working with
            Log::info('Raw editing data:', [
                'editingAssemblyItemData' => $this->editingAssemblyItemData,
                'assemblies' => $this->assemblies instanceof \Illuminate\Support\Collection ? 'Collection' : gettype($this->assemblies),
                'assemblies_count' => $this->assemblies instanceof \Illuminate\Support\Collection ? $this->assemblies->count() : 'not a collection'
            ]);

            if (!is_array($this->editingAssemblyItemData)) {
                throw new \Exception('editingAssemblyItemData is not an array');
            }

            if (!isset($this->editingAssemblyItemData['assembly_index']) || 
                !isset($this->editingAssemblyItemData['item_index']) || 
                !isset($this->editingAssemblyItemData['quantity'])) {
                throw new \Exception('Missing required data in editingAssemblyItemData');
            }

            if (!is_numeric($this->editingAssemblyItemData['quantity']) || $this->editingAssemblyItemData['quantity'] <= 0) {
                throw new \Exception('Quantity must be greater than zero.');
            }

            // Get the assembly
            if (!isset($this->assemblies[$this->editingAssemblyItemData['assembly_index']])) {
                throw new \Exception('Assembly not found at index ' . $this->editingAssemblyItemData['assembly_index']);
            }
            $assembly = $this->assemblies[$this->editingAssemblyItemData['assembly_index']];
            
            Log::info('Found assembly:', [
                'assembly_id' => $assembly->id,
                'items_count' => $assembly->items instanceof \Illuminate\Support\Collection ? $assembly->items->count() : 'not a collection'
            ]);

            // Get the item
            if (!isset($assembly->items[$this->editingAssemblyItemData['item_index']])) {
                throw new \Exception('Item not found at index ' . $this->editingAssemblyItemData['item_index']);
            }
            $item = $assembly->items[$this->editingAssemblyItemData['item_index']];

            Log::info('Found item to update:', [
                'item_id' => $item->id,
                'old_quantity' => $item->quantity,
                'new_quantity' => $this->editingAssemblyItemData['quantity']
            ]);

            // Update the quantity
            $item->quantity = $this->editingAssemblyItemData['quantity'];
            $saved = $item->save();

            Log::info('Save result:', ['saved' => $saved]);

            // Reset the editing state
            $this->editingAssemblyItemData = [
                'assembly_index' => null,
                'item_index' => null,
                'quantity' => null
            ];

            // Refresh the assemblies collection
            if ($this->estimate) {
                $this->assemblies = $this->estimate->assemblies()->with('items')->get();
                Log::info('Refreshed assemblies:', [
                    'assemblies_count' => $this->assemblies->count()
                ]);
            }

            // Recalculate totals
            $this->calculateTotals();

            // Dispatch events to notify of updates
            $this->dispatch('assembly-item-updated');
            $this->dispatch('estimate-assemblies-updated', ['assemblies' => $this->assemblies]);

        } catch (\Exception $e) {
            Log::error('Error in updateAssemblyItem:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'editing_data' => $this->editingAssemblyItemData
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
    
    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            if (!$this->estimate) {
                $this->estimate = new Estimate();
            }
            
            // Save customer information
            $this->estimate->customer_name = $this->customer_name;
            $this->estimate->customer_email = $this->customer_email;
            $this->estimate->customer_phone = $this->customer_phone;
            $this->estimate->customer_address = $this->customer_address;
            $this->estimate->valid_until = $this->valid_until;
            
            // Save totals
            $this->estimate->total_cost = $this->totalCost;
            $this->estimate->total_charge = $this->totalCharge;
            
            // Set tenant and user
            if (!$this->estimate->tenant_id) {
                $this->estimate->tenant_id = auth()->user()->current_tenant_id;
            }
            if (!$this->estimate->user_id) {
                $this->estimate->user_id = auth()->id();
            }
            
            $this->estimate->save();
            
            DB::commit();
            
            session()->flash('message', 'Estimate saved successfully.');
            
            return redirect()->route('estimates.view', $this->estimate);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving estimate:', [
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
                    $laborRate = LaborRate::where('is_primary', true)
                        ->where('tenant_id', auth()->user()->current_tenant_id)
                        ->active()
                        ->first();
                        
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

            // Refresh the estimate
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

    public function calculateLaborCosts($laborUnits)
    {
        // Get the primary labor rate
        $primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->active()
            ->first();
            
        if (!$primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }
        
        // Convert labor units (minutes) to hours
        $laborHours = $laborUnits / 60;
        
        return [
            'cost' => $laborHours * $primaryLaborRate->cost_rate,
            'charge' => $laborHours * $primaryLaborRate->charge_rate
        ];
    }
} 