<?php

namespace App\Livewire\Assemblies;

use App\Models\Assembly;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use App\Models\Settings;

#[Layout('layouts.app')]
class AssemblyList extends Component
{
    use WithPagination;

    public $search = '';
    public $showInactive = false;
    public $categoryFilter = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
        'categoryFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingShowInactive()
    {
        $this->resetPage();
    }
    
    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    private function calculateAssemblyCosts($assembly)
    {
        $unitPartsCost = 0;
        $unitLaborCost = 0;
        $billedPartsCost = 0;
        $billedLaborCost = 0;
        $totalLaborUnits = 0;
        
        // Get the primary labor rate and default markup from settings
        $primaryLaborRate = Settings::getPrimaryLaborRate();
        $defaultLaborMarkup = Settings::getDefaultLaborMarkup();
        
        // Calculate costs for each item in the assembly
        foreach ($assembly->items as $item) {
            $quantity = $item->pivot->quantity;
            
            // Calculate material costs
            $unitPartsCost += $item->material_cost_rate * $quantity;
            $billedPartsCost += $item->material_charge_rate * $quantity;
            
            // Calculate labor costs (labor_units is in minutes)
            $itemLaborUnits = $item->labor_units * $quantity;
            $totalLaborUnits += $itemLaborUnits;
            
            // Convert to hours for cost calculation
            $laborHours = $itemLaborUnits / 60;
            $unitLaborCost += $laborHours * $primaryLaborRate;
            $billedLaborCost += ($laborHours * $primaryLaborRate) * $defaultLaborMarkup;
        }
        
        // Calculate totals
        $unitCost = $unitPartsCost + $unitLaborCost;
        $billedCost = $billedPartsCost + $billedLaborCost;
        
        return [
            'unitCost' => $unitCost,
            'unitPartsCost' => $unitPartsCost,
            'unitLaborCost' => $unitLaborCost,
            'billedCost' => $billedCost,
            'billedPartsCost' => $billedPartsCost,
            'billedLaborCost' => $billedLaborCost,
            'totalLaborUnits' => $totalLaborUnits
        ];
    }

    public function render()
    {
        $query = Assembly::query()
            ->where('tenant_id', auth()->user()->current_tenant_id);
        
        // Apply search filter if provided
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply category filter if provided
        if ($this->categoryFilter) {
            $query->whereHas('categories', function($q) {
                $q->where('categories.id', $this->categoryFilter);
            });
        }
        
        // Apply active/inactive filter
        if (!$this->showInactive) {
            $query->where('is_active', true);
        }
        
        // Get assemblies with their items and categories
        $assemblies = $query->with(['items', 'categories'])->paginate(10);
        
        // Calculate costs for each assembly using the consistent method
        foreach ($assemblies as $assembly) {
            $costs = $this->calculateAssemblyCosts($assembly);
            $assembly->unitCost = $costs['unitCost'];
            $assembly->unitPartsCost = $costs['unitPartsCost'];
            $assembly->unitLaborCost = $costs['unitLaborCost'];
            $assembly->billedCost = $costs['billedCost'];
            $assembly->billedPartsCost = $costs['billedPartsCost'];
            $assembly->billedLaborCost = $costs['billedLaborCost'];
        }
        
        // Get categories for filter dropdown
        $categories = \App\Models\Category::where('tenant_id', auth()->user()->current_tenant_id)
            ->where('is_active', true)
            ->withCount('assemblies')
            ->having('assemblies_count', '>', 0)
            ->orderBy('name')
            ->get();
        
        return view('livewire.assemblies.list', [
            'assemblies' => $assemblies,
            'categories' => $categories
        ]);
    }

    public function toggleActive(Assembly $assembly)
    {
        $assembly->update(['is_active' => !$assembly->is_active]);
    }

    public function deleteAssembly($id)
    {
        try {
            $assembly = Assembly::findOrFail($id);
            $assembly->delete();
            
            session()->flash('message', 'Assembly deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting assembly', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error deleting assembly: ' . $e->getMessage());
        }
    }
}
