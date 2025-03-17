<?php

namespace App\Livewire\LaborRates;

use App\Models\LaborRate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $layout = 'layouts.app';

    public $search = '';
    public $showInactive = false;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        if (!auth()->user()->can('view labor rates')) {
            abort(403);
        }
    }

    public function toggleStatus(LaborRate $laborRate)
    {
        if (!auth()->user()->can('edit labor rates')) {
            abort(403);
        }

        $laborRate->update(['is_active' => !$laborRate->is_active]);
        $this->dispatch('labor-rate-updated');
    }

    public function render()
    {
        $query = Auth::user()->currentTeam->laborRates()
            ->when($this->search, fn($query) => 
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('cost_rate', 'like', '%' . $this->search . '%')
                    ->orWhere('price_rate', 'like', '%' . $this->search . '%')
            )
            ->when(!$this->showInactive, fn($query) => 
                $query->where('is_active', true)
            )
            ->orderBy('is_default', 'desc')
            ->orderBy('name');

        return view('livewire.labor-rates.index', [
            'laborRates' => $query->paginate(10),
        ])->layout('layouts.app');
    }
}
