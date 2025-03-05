<?php

namespace App\Livewire\Items;

use App\Models\Item;
use App\Models\Category;
use App\Models\Settings;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class ItemForm extends Component
{
    public $item;
    public $mode = 'create';
    public $primaryLaborRate;
    public $defaultLaborMarkup;
    
    // Updated property names to match database columns
    public $name;
    public $sku;
    public $description;
    public $material_cost_rate = 0;
    public $material_charge_rate = 0;
    public $labor_units = 0;
    public $unit_of_measure;
    public $is_active = true;
    
    // Change from single category_id to array of selected categories
    public $selectedCategories = [];
    
    protected $rules = [
        'name' => 'required|string|max:255',
        'sku' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'material_cost_rate' => 'required|numeric|min:0',
        'material_charge_rate' => 'required|numeric|min:0',
        'labor_units' => 'required|numeric|min:0',
        'unit_of_measure' => 'required|string|max:50',
        'selectedCategories' => 'array',
        'is_active' => 'boolean',
    ];
    
    public function mount($item = null)
    {
        $this->loadSettings();
        if ($item) {
            // Check if $item is an ID (string or number) and fetch the model if needed
            if (!is_object($item)) {
                $this->item = Item::with('categories')->findOrFail($item);
            } else {
                $this->item = $item;
                // Load categories relationship if not already loaded
                if (!$this->item->relationLoaded('categories')) {
                    $this->item->load('categories');
                }
            }
            
            $this->mode = 'edit';
            
            // Load values from the item model with updated field names
            $this->name = $this->item->name;
            $this->sku = $this->item->sku;
            $this->description = $this->item->description;
            $this->material_cost_rate = $this->item->material_cost_rate;
            $this->material_charge_rate = $this->item->material_charge_rate;
            $this->labor_units = $this->item->labor_units;
            $this->unit_of_measure = $this->item->unit_of_measure;
            $this->is_active = $this->item->is_active;
            
            // Load selected categories
            $this->selectedCategories = $this->item->categories->pluck('id')->toArray();
            
            Log::info('Item loaded for editing', [
                'id' => $this->item->id,
                'name' => $this->name,
                'material_cost_rate' => $this->material_cost_rate,
                'material_charge_rate' => $this->material_charge_rate,
                'categories' => $this->selectedCategories
            ]);
        }
    }
    
    public function loadSettings()
    {
        $this->primaryLaborRate = Settings::getPrimaryLaborRate();
        $this->defaultLaborMarkup = Settings::getDefaultLaborMarkup();
    }
    
    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $data = [
                'name' => $this->name,
                'sku' => $this->sku,
                'description' => $this->description,
                'material_cost_rate' => $this->material_cost_rate,
                'material_charge_rate' => $this->material_charge_rate,
                'labor_units' => $this->labor_units,
                'unit_of_measure' => $this->unit_of_measure,
                'is_active' => $this->is_active,
            ];
            
            if ($this->item) {
                // Update existing item
                $this->item->update($data);
                
                // Sync categories
                $pivotData = array_fill(0, count($this->selectedCategories), ['tenant_id' => auth()->user()->tenant_id]);
                $syncData = array_combine($this->selectedCategories, $pivotData);
                $this->item->categories()->sync($syncData);
                
                session()->flash('message', 'Item updated successfully.');
            } else {
                // Create new item
                $data['tenant_id'] = auth()->user()->tenant_id;
                $item = Item::create($data);
                
                // Attach categories
                foreach ($this->selectedCategories as $categoryId) {
                    $item->categories()->attach($categoryId, ['tenant_id' => auth()->user()->tenant_id]);
                }
                
                session()->flash('message', 'Item created successfully.');
                DB::commit();
                return redirect()->route('items.index');
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving item: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('items.index');
    }

    public function render()
    {
        Log::info('ItemForm render starting');
        
        try {
            $categories = Category::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            
            return view('livewire.items.item-form', [
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ItemForm render', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('livewire.items.item-form');
        }
    }
}
