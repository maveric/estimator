<?php

namespace App\Livewire\Assemblies;

use App\Models\Assembly;
use App\Models\Item;
use App\Models\AssemblyItem;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Settings;

#[Layout('layouts.app')]
class AssemblyForm extends Component
{
    public $assembly = null;
    public $mode = 'create';
    public $name = '';
    public $description = '';
    public $is_active = true;
    
    // Change from single category_id to array of selected categories
    public $selectedCategories = [];
    
    // For adding items to the assembly
    public $selectedItem = '';
    public $quantity = 1;
    public $items = [];
    
    // For editing item quantities
    public $editingItemIndex = null;
    public $editingItemQuantity = null;
    
    // For cost calculations
    public $unitCost = 0;
    public $unitPartsCost = 0;
    public $unitLaborCost = 0;
    public $billedCost = 0;
    public $billedPartsCost = 0;
    public $billedLaborCost = 0;
    
    public $availableItems = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'items' => 'array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'selectedCategories' => 'array',
        ];
    }

    public function mount($assembly = null)
    {
        Log::info('AssemblyForm mount starting', ['assembly' => $assembly]);
        $this->availableItems = Item::where('is_active', true)->orderBy('name')->get();
        
        if ($assembly) {
            // If $assembly is a string (ID), load the actual Assembly model
            if (is_string($assembly) || is_numeric($assembly)) {
                Log::info('Loading assembly from ID', ['id' => $assembly]);
                $assembly = Assembly::with('categories')->find($assembly);
                
                // If assembly not found, log error and return
                if (!$assembly) {
                    Log::error('Assembly not found', ['id' => $assembly]);
                    session()->flash('error', 'Assembly not found.');
                    return redirect()->route('assemblies.index');
                }
            } else {
                // Load categories relationship if not already loaded
                if (!$assembly->relationLoaded('categories')) {
                    $assembly->load('categories');
                }
            }
            
            $this->assembly = $assembly;
            $this->mode = 'edit';
            $this->name = $assembly->name;
            $this->description = $assembly->description;
            $this->is_active = $assembly->is_active;
            
            // Load selected categories
            $this->selectedCategories = $assembly->categories->pluck('id')->toArray();
            
            // Load existing assembly items with correct item details
            $this->items = $this->assembly->items->map(function ($item) {
                $dbItem = \App\Models\Item::find($item->id);
                return [
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->pivot->quantity,
                    'unit_of_measure' => $item->unit_of_measure,
                    'material_cost_rate' => $dbItem ? $dbItem->material_cost_rate : 0,
                    'material_charge_rate' => $dbItem ? $dbItem->material_charge_rate : 0,
                    'labor_units' => $dbItem ? $dbItem->labor_units : 0,
                ];
            })->toArray();
            
            Log::info('Assembly loaded successfully', [
                'id' => $assembly->id,
                'name' => $assembly->name,
                'items_count' => count($this->items)
            ]);
        }
        
        // Calculate all costs using the consolidated method
        $this->calculateCosts();
        
        Log::info('AssemblyForm mount completed');
    }

    // Consolidated method to calculate all costs
    public function calculateCosts()
    {
        // Reset all cost properties
        $this->unitPartsCost = 0;
        $this->unitLaborCost = 0;
        $this->billedPartsCost = 0;
        $this->billedLaborCost = 0;
        
        // Get the primary labor rate and default markup from settings
        $primaryLaborRate = Settings::getPrimaryLaborRate();
        $defaultLaborMarkup = Settings::getDefaultLaborMarkup();
        
        // Calculate total labor units
        $totalLaborUnits = 0;
        
        foreach ($this->items as $itemData) {
            // Get the full item model to access all properties
            $dbItem = Item::find($itemData['item_id']);
            if (!$dbItem) continue;
            
            // Make sure we're using numeric values
            $quantity = (float)$itemData['quantity'];
            $materialCostRate = (float)$dbItem->material_cost_rate;
            $materialChargeRate = (float)$dbItem->material_charge_rate;
            $laborUnits = (float)$dbItem->labor_units;
            
            // Calculate material costs
            $this->unitPartsCost += $materialCostRate * $quantity;
            $this->billedPartsCost += $materialChargeRate * $quantity;
            
            // Calculate labor costs (labor_units is in minutes)
            $itemLaborUnits = $laborUnits * $quantity;
            $totalLaborUnits += $itemLaborUnits;
            
            // Convert to hours for cost calculation
            $laborHours = $itemLaborUnits / 60;
            $this->unitLaborCost += $laborHours * $primaryLaborRate;
            $this->billedLaborCost += ($laborHours * $primaryLaborRate) * $defaultLaborMarkup;
        }
        
        // Calculate totals
        $this->unitCost = $this->unitPartsCost + $this->unitLaborCost;
        $this->billedCost = $this->billedPartsCost + $this->billedLaborCost;
        
        Log::info('Costs calculated', [
            'unitCost' => $this->unitCost,
            'unitPartsCost' => $this->unitPartsCost,
            'unitLaborCost' => $this->unitLaborCost,
            'billedCost' => $this->billedCost,
            'billedPartsCost' => $this->billedPartsCost,
            'billedLaborCost' => $this->billedLaborCost,
            'totalLaborUnits' => $totalLaborUnits
        ]);
        
        return [
            'unitPartsCost' => $this->unitPartsCost,
            'unitLaborCost' => $this->unitLaborCost,
            'billedPartsCost' => $this->billedPartsCost,
            'billedLaborCost' => $this->billedLaborCost,
            'unitCost' => $this->unitCost,
            'billedCost' => $this->billedCost,
            'totalLaborUnits' => $totalLaborUnits
        ];
    }

    public function addItem()
    {
        if (!$this->selectedItem || !$this->quantity) {
            return;
        }
        
        $item = \App\Models\Item::find($this->selectedItem);
        if (!$item) {
            return;
        }
        
        // Check if item already exists in the assembly
        $existingItemKey = array_search($this->selectedItem, array_column($this->items, 'item_id'));
        
        if ($existingItemKey !== false) {
            // Update quantity if item already exists
            $this->items[$existingItemKey]['quantity'] += $this->quantity;
        } else {
            // Add new item with all necessary details
            $this->items[] = [
                'item_id' => $item->id,
                'name' => $item->name,
                'quantity' => $this->quantity,
                'unit_of_measure' => $item->unit_of_measure,
                'material_cost_rate' => $item->material_cost_rate,
                'material_charge_rate' => $item->material_charge_rate,
                'labor_units' => $item->labor_units,
            ];
        }
        
        // Reset form fields
        $this->selectedItem = '';
        $this->quantity = 1;
        
        // Recalculate costs after adding an item
        $this->calculateCosts();
    }

    public function editItem($index)
    {
        // Get the item at the specified index
        if (isset($this->items[$index])) {
            $this->editingItemIndex = $index;
            $this->editingItemQuantity = $this->items[$index]['quantity'];
        }
    }

    public function updateItem()
    {
        if ($this->editingItemIndex !== null) {
            // Update the quantity of the item being edited
            $this->items[$this->editingItemIndex]['quantity'] = $this->editingItemQuantity;
            
            // Reset editing state
            $this->editingItemIndex = null;
            $this->editingItemQuantity = null;
            
            // Recalculate costs immediately
            $this->calculateCosts();
        }
    }

    public function cancelEdit()
    {
        // Reset editing state without saving changes
        $this->editingItemIndex = null;
        $this->editingItemQuantity = null;
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            array_splice($this->items, $index, 1);
            
            // Recalculate costs after removing an item
            $this->calculateCosts();
        }
    }

    public function save()
    {
        Log::info('Assembly save method started', [
            'mode' => $this->mode,
            'items_count' => count($this->items),
            'name' => $this->name,
            'categories' => $this->selectedCategories
        ]);
        
        $this->validate();

        try {
            DB::beginTransaction();
            
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ];
            
            if ($this->mode === 'edit') {
                // Update existing assembly
                $this->assembly->update($data);
                
                // Sync categories
                $pivotData = array_fill(0, count($this->selectedCategories), ['tenant_id' => auth()->user()->tenant_id]);
                $syncData = array_combine($this->selectedCategories, $pivotData);
                $this->assembly->categories()->sync($syncData);
                
                // Delete existing items
                $this->assembly->items()->detach();
                
                Log::info('Existing assembly updated', ['id' => $this->assembly->id]);
            } else {
                // Create new assembly
                $data['tenant_id'] = auth()->user()->tenant_id;
                $this->assembly = Assembly::create($data);
                
                // Attach categories
                foreach ($this->selectedCategories as $categoryId) {
                    $this->assembly->categories()->attach($categoryId, ['tenant_id' => auth()->user()->tenant_id]);
                }
                
                Log::info('New assembly created', ['id' => $this->assembly->id]);
            }
            
            // Add items to the assembly
            foreach ($this->items as $item) {
                $pivotData = [
                    'quantity' => $item['quantity']
                ];
                
                // Add tenant_id if the column exists in assembly_items table
                if (Schema::hasColumn('assembly_items', 'tenant_id')) {
                    $pivotData['tenant_id'] = auth()->user()->tenant_id;
                }
                
                $this->assembly->items()->attach($item['item_id'], $pivotData);
            }
            
            Log::info('Items attached to assembly', [
                'assembly_id' => $this->assembly->id,
                'items_count' => count($this->items)
            ]);
            
            DB::commit();
            
            session()->flash('message', $this->mode === 'edit' 
                ? 'Assembly updated successfully!' 
                : 'Assembly created successfully!');
            
            Log::info('Assembly saved successfully, redirecting to index');
            
            return redirect()->route('assemblies.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving assembly', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving assembly: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('assemblies.index');
    }

    public function render()
    {
        $availableItems = \App\Models\Item::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $categories = \App\Models\Category::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // Calculate all costs using the consolidated method
        $costs = $this->calculateCosts();
        
        return view('livewire.assemblies.form', [
            'availableItems' => $availableItems,
            'categories' => $categories,
            'totalPartsCost' => $costs['unitPartsCost'],
            'totalPartsCharge' => $costs['billedPartsCost'],
            'totalLaborUnits' => $costs['totalLaborUnits'],
            'laborCost' => $costs['unitLaborCost'],
            'laborCharge' => $costs['billedLaborCost'],
            'totalCost' => $costs['unitCost'],
            'totalCharge' => $costs['billedCost'],
        ]);
    }

    // Add this method to ensure items are preserved during Livewire updates
    public function hydrate()
    {
        Log::info('AssemblyForm hydrated', [
            'items_count' => count($this->items)
        ]);
    }
}

