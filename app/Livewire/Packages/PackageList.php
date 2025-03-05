<?php

namespace App\Livewire\Packages;

use App\Models\Package;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class PackageList extends Component
{
    use WithPagination;

    public $search = '';
    public $showInactive = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $tenantId = Auth::user()->current_tenant_id;
        
        $query = Package::where('tenant_id', $tenantId)
            ->when(!$this->showInactive, function ($query) {
                return $query->where('is_active', true);
            })
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name');
            
        $packages = $query->paginate(10);
        
        return view('livewire.packages.package-list', [
            'packages' => $packages
        ]);
    }

    public function toggleActive($id): void
    {
        $package = Package::findOrFail($id);
        $package->is_active = !$package->is_active;
        $package->save();
        
        session()->flash('message', 'Package status updated successfully!');
    }
} 