<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class CategoryManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showInactive = false;
    
    // Form properties
    public $name = '';
    public $description = '';
    
    // Edit properties
    public $editingCategoryId = null;
    public $editName = '';
    public $editDescription = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];

    protected $editRules = [
        'editName' => 'required|string|max:255',
        'editDescription' => 'nullable|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingShowInactive()
    {
        $this->resetPage();
    }

    public function saveCategory()
    {
        $this->validate();

        try {
            Category::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => true,
            ]);

            $this->reset(['name', 'description']);
            session()->flash('message', 'Category created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating category', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error creating category: ' . $e->getMessage());
        }
    }

    public function startEditing(Category $category)
    {
        $this->editingCategoryId = $category->id;
        $this->editName = $category->name;
        $this->editDescription = $category->description;
    }

    public function cancelEditing()
    {
        $this->reset(['editingCategoryId', 'editName', 'editDescription']);
    }

    public function updateCategory()
    {
        $this->validate($this->editRules);

        try {
            $category = Category::findOrFail($this->editingCategoryId);
            
            $category->update([
                'name' => $this->editName,
                'description' => $this->editDescription,
            ]);

            $this->reset(['editingCategoryId', 'editName', 'editDescription']);
            session()->flash('message', 'Category updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating category', [
                'id' => $this->editingCategoryId,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error updating category: ' . $e->getMessage());
        }
    }

    public function toggleActive(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
    }

    public function deleteCategory($id)
    {
        try {
            $category = Category::findOrFail($id);
            
            // Check if category is in use
            $itemCount = $category->items()->count();
            $assemblyCount = $category->assemblies()->count();
            
            if ($itemCount > 0 || $assemblyCount > 0) {
                session()->flash('error', "Cannot delete category. It's being used by {$itemCount} items and {$assemblyCount} assemblies.");
                return;
            }
            
            $category->delete();
            session()->flash('message', 'Category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting category', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error deleting category: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Category::query()
            ->where('tenant_id', auth()->user()->tenant_id);
        
        // Apply search filter if provided
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply active/inactive filter
        if (!$this->showInactive) {
            $query->where('is_active', true);
        }
        
        // Eager load relationships for counting
        $query->withCount(['items', 'assemblies']);
        
        $categories = $query->orderBy('name')->paginate(10);
        
        return view('livewire.categories.manager', [
            'categories' => $categories
        ]);
    }
} 