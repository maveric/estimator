<?php

namespace App\Livewire\Estimates;

use Livewire\Component;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Item;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use App\Models\Assembly;
use App\Models\EstimateAssembly;

class EstimateForm extends Component
{
    public $estimate;
    public $items;
    public $assemblies;
    public $packages;
    
    // For adding new items
    public $availableItems;
    public $selectedItemId;
    public $newItemQuantity = 1;

    // For adding new assemblies
    public $availableAssemblies;
    public $selectedAssemblyId;
    public $newAssemblyQuantity = 1;

    // For adding new packages
    public $availablePackages;
    public $selectedPackageId;
    public $newPackageQuantity = 1;

    // For markup and discount
    public $markup_percentage = 0;
    public $discount_percentage = 0;
    public $discount_amount = 0;
    
    protected $listeners = [
        'item-changed' => 'handleItemChanged',
        'item-deleted' => 'handleItemDeleted',
        'assembly-changed' => 'handleAssemblyChanged',
        'assembly-deleted' => 'handleAssemblyDeleted',
        'package-changed' => 'handlePackageChanged',
        'package-deleted' => 'handlePackageDeleted',
        'customer-information-updated' => 'handleCustomerInformationUpdated'
    ];

    public function mount($estimate = null)
    {
        if (is_string($estimate)) {
            $this->estimate = Estimate::find($estimate);
        } else {
            $this->estimate = $estimate;
        }
        
        if ($this->estimate) {
            $this->items = $this->estimate->items()->get();
            $this->assemblies = $this->estimate->assemblies()->get();
            $this->packages = $this->estimate->packages()->get();
            $this->markup_percentage = $this->estimate->markup_percentage ?? 0;
            $this->discount_percentage = $this->estimate->discount_percentage ?? 0;
            $this->discount_amount = $this->estimate->discount_amount ?? 0;
        } else {
            $this->items = collect();
            $this->assemblies = collect();
            $this->packages = collect();
        }

        // Load available items and assemblies
        $this->loadAvailableItems();
        $this->loadAvailableAssemblies();
        $this->loadAvailablePackages();
    }

    public function loadAvailableItems()
    {
        $this->availableItems = Item::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function loadAvailableAssemblies()
    {
        $this->availableAssemblies = Assembly::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    protected function loadAvailablePackages()
    {
        $this->availablePackages = \App\Models\Package::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
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
                'estimate_id' => $this->estimate->id,
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
            $this->selectedItemId = '';
            $this->newItemQuantity = 1;
            
            // Refresh items
            $this->refreshItems();
            
        } catch (\Exception $e) {
            Log::error('Error adding item to estimate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding item: ' . $e->getMessage());
        }
    }

    public function addAssembly()
    {
        if (!$this->selectedAssemblyId || $this->newAssemblyQuantity <= 0) {
            return;
        }

        try {
            $sourceAssembly = Assembly::with('items')->findOrFail($this->selectedAssemblyId);
            
            // Create new estimate assembly
            $estimateAssembly = new EstimateAssembly([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_id' => $this->estimate->id,
                'original_assembly_id' => $sourceAssembly->id,
                'name' => $sourceAssembly->name,
                'description' => $sourceAssembly->description,
                'quantity' => $this->newAssemblyQuantity,
            ]);

            $estimateAssembly->save();
            
            // Copy items from source assembly
            foreach ($sourceAssembly->items as $sourceItem) {
                $item = new EstimateItem([
                    'tenant_id' => auth()->user()->current_tenant_id,
                    'estimate_assembly_id' => $estimateAssembly->id,  // Only link to assembly
                    'original_item_id' => $sourceItem->id,
                    'name' => $sourceItem->name,
                    'description' => $sourceItem->description,
                    'unit_of_measure' => $sourceItem->unit_of_measure,
                    'quantity' => $sourceItem->pivot->quantity,
                    'material_cost_rate' => $sourceItem->material_cost_rate,
                    'material_charge_rate' => $sourceItem->material_charge_rate,
                    'labor_units' => $sourceItem->labor_units,
                    'labor_rate_id' => $sourceItem->labor_rate_id,
                    'original_cost_rate' => $sourceItem->material_cost_rate,
                    'original_charge_rate' => $sourceItem->material_charge_rate
                ]);
                $item->save();
            }
            
            // Reset form
            $this->selectedAssemblyId = '';
            $this->newAssemblyQuantity = 1;
            
            // Refresh assemblies and trigger re-render
            $this->refreshAssemblies();
            $this->dispatch('assembly-added', ['assemblyId' => $estimateAssembly->id]);
            
        } catch (\Exception $e) {
            Log::error('Error adding assembly to estimate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding assembly: ' . $e->getMessage());
        }
    }

    public function handleItemChanged($data)
    {
        Log::info('Item changed:', $data);
        
        // Refresh the items collection with labor rates
        $this->items = EstimateItem::with(['laborRate'])
            ->where('estimate_id', $this->estimate->id)
            ->get();
            
        // Force a re-render of the component
        $this->dispatch('estimate-updated')->self();
    }

    public function handleItemDeleted($data)
    {
        Log::info('Item deleted:', $data);
        $this->refreshItems();
    }

    public function handleAssemblyChanged($data)
    {
        Log::info('Assembly changed:', $data);
        $this->refreshAssemblies();
    }

    public function handleAssemblyDeleted($data)
    {
        Log::info('Assembly deleted:', $data);
        $this->refreshAssemblies();
    }

    public function addPackage()
    {
        if (!$this->selectedPackageId || $this->newPackageQuantity <= 0) {
            return;
        }

        try {
            $sourcePackage = \App\Models\Package::with(['assemblies.items'])->findOrFail($this->selectedPackageId);
            
            // Create new estimate package
            $estimatePackage = new \App\Models\EstimatePackage([
                'tenant_id' => auth()->user()->current_tenant_id,
                'estimate_id' => $this->estimate->id,
                'package_id' => $sourcePackage->id,
                'original_package_id' => $sourcePackage->id,
                'name' => $sourcePackage->name,
                'description' => $sourcePackage->description,
                'quantity' => $this->newPackageQuantity,
            ]);

            $estimatePackage->save();

            // Clone assemblies and their items from the source package
            foreach ($sourcePackage->assemblies as $assembly) {
                $estimateAssembly = new \App\Models\EstimateAssembly([
                    'tenant_id' => auth()->user()->current_tenant_id,
                    'estimate_package_id' => $estimatePackage->id,
                    'original_assembly_id' => $assembly->id,
                    'name' => $assembly->name,
                    'description' => $assembly->description,
                    'quantity' => $assembly->pivot->quantity,
                ]);

                $estimateAssembly->save();

                // Clone items from the assembly
                foreach ($assembly->items as $item) {
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
            }
            
            // Reset form
            $this->selectedPackageId = null;
            $this->newPackageQuantity = 1;
            
            // Refresh estimate data
            $this->estimate->refresh();
            $this->packages = $this->estimate->packages;
            
        } catch (\Exception $e) {
            Log::error('Error adding package to estimate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error adding package: ' . $e->getMessage());
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

    public function handlePackageChanged($data)
    {
        $this->estimate->refresh();
        $this->packages = $this->estimate->packages;
    }

    public function handlePackageDeleted($data)
    {
        $this->estimate->refresh();
        $this->packages = $this->estimate->packages;
    }

    public function handleCustomerInformationUpdated($data)
    {
        $this->estimate->update([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'customer_address' => $data['customer_address'],
            'valid_until' => $data['valid_until'],
        ]);
    }

    public function refreshItems()
    {
        $this->items = EstimateItem::with(['laborRate'])
            ->where('estimate_id', $this->estimate->id)
            ->get();
    }

    public function refreshAssemblies()
    {
        $this->assemblies = EstimateAssembly::with(['items.laborRate'])
            ->where('estimate_id', $this->estimate->id)
            ->get();
    }

    public function updatedMarkupPercentage($value)
    {
        $this->markup_percentage = max(0, min(100, floatval($value)));
        $this->estimate->update(['markup_percentage' => $this->markup_percentage]);
    }

    public function updatedDiscountPercentage($value)
    {
        $this->discount_percentage = max(0, min(100, floatval($value)));
        $this->estimate->update(['discount_percentage' => $this->discount_percentage]);
    }

    public function updatedDiscountAmount($value)
    {
        $this->discount_amount = max(0, floatval($value));
        $this->estimate->update(['discount_amount' => $this->discount_amount]);
    }

    protected function calculateTotals()
    {
        $materialCost = 0;
        $materialCharge = 0;
        $laborCost = 0;
        $laborCharge = 0;

        // Calculate totals from direct items
        foreach ($this->items as $item) {
            $materialCost += $item->quantity * $item->material_cost_rate;
            $materialCharge += $item->quantity * $item->material_charge_rate;
            
            $laborHours = ($item->quantity * $item->labor_units) / 60;
            if ($item->laborRate) {
                $laborCost += $laborHours * $item->laborRate->cost_rate;
                $laborCharge += $laborHours * $item->laborRate->charge_rate;
            }
        }

        // Calculate totals from assemblies
        foreach ($this->assemblies as $assembly) {
            $assemblyTotals = $assembly->calculateTotals();
            $materialCost += $assemblyTotals['material_cost'] * $assembly->quantity;
            $materialCharge += $assemblyTotals['material_charge'] * $assembly->quantity;
            $laborCost += $assemblyTotals['labor_cost'] * $assembly->quantity;
            $laborCharge += $assemblyTotals['labor_charge'] * $assembly->quantity;
        }

        // Calculate totals from packages
        foreach ($this->packages as $package) {
            $packageTotals = $package->calculateTotals();
            $materialCost += $packageTotals['material_cost'] * $package->quantity;
            $materialCharge += $packageTotals['material_charge'] * $package->quantity;
            $laborCost += $packageTotals['labor_cost'] * $package->quantity;
            $laborCharge += $packageTotals['labor_charge'] * $package->quantity;
        }

        $totalCost = $materialCost + $laborCost;
        $subtotal = $materialCharge + $laborCharge;

        // Calculate markup
        $markupAmount = $subtotal * ($this->markup_percentage / 100);
        $afterMarkup = $subtotal + $markupAmount;

        // Calculate percentage discount
        $discountPercentageAmount = $afterMarkup * ($this->discount_percentage / 100);
        $afterPercentageDiscount = $afterMarkup - $discountPercentageAmount;

        // Apply fixed amount discount
        $discountFixedAmount = min($afterPercentageDiscount, $this->discount_amount);
        $finalTotal = $afterPercentageDiscount - $discountFixedAmount;

        // Calculate profit
        $profit = $finalTotal - $totalCost;
        $profitPercentage = $finalTotal > 0 ? ($profit / $finalTotal) * 100 : 0;

        return [
            'material_cost' => $materialCost,
            'material_charge' => $materialCharge,
            'labor_cost' => $laborCost,
            'labor_charge' => $laborCharge,
            'total_cost' => $totalCost,
            'subtotal' => $subtotal,
            'markup_amount' => $markupAmount,
            'discount_percentage_amount' => $discountPercentageAmount,
            'discount_fixed_amount' => $discountFixedAmount,
            'total_discount' => $discountPercentageAmount + $discountFixedAmount,
            'final_total' => $finalTotal,
            'profit' => $profit,
            'profit_percentage' => $profitPercentage
        ];
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.estimates.form', [
            'totals' => $this->calculateTotals()
        ]);
    }
} 