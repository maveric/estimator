<?php

namespace App\Livewire\Items;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showInactive = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
    ];

    public function mount()
    {
        if (!auth()->user()->can('view items')) {
            abort(403);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingShowInactive()
    {
        $this->resetPage();
    }

    public function toggleStatus(Item $item)
    {
        if (!auth()->user()->can('edit items')) {
            abort(403);
        }

        // Ensure user can only toggle items from their team
        if ($item->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $item->update(['is_active' => !$item->is_active]);

        $this->dispatch('item-updated');
    }

    public function render()
    {
        $query = Item::query()
            ->forTeam(Auth::user()->currentTeam->id)
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when(!$this->showInactive, fn ($query) => $query->where('is_active', true))
            ->orderBy('name');

        $items = $query->paginate(10);

        return view('livewire.items.index', [
            'items' => $items,
        ]);
    }
}
