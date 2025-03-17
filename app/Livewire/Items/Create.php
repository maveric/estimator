<?php

namespace App\Livewire\Items;

use App\Models\Item;
use App\Models\LaborRate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Create extends Component
{
    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string|max:1000')]
    public $description = '';

    #[Rule('required|string|max:50|unique:items,sku')]
    public $sku = '';

    #[Rule('required|string|max:50')]
    public $unit_of_measure = '';

    #[Rule('required|decimal:0,4|min:0')]
    public $material_cost = 0;

    #[Rule('required|decimal:0,4|min:0')]
    public $material_price = 0;

    #[Rule('required|decimal:0,4|min:0')]
    public $labor_minutes = 0;

    #[Rule('required|exists:labor_rates,id')]
    public $labor_rate_id = '';

    #[Rule('boolean')]
    public $is_template = false;

    #[Rule('boolean')]
    public $is_active = true;

    public function mount()
    {
        if (!Auth::user()->can('create items')) {
            abort(403);
        }

        // Set default labor rate if available
        $defaultRate = LaborRate::forTeam(Auth::user()->currentTeam->id)
            ->active()
            ->where('is_default', true)
            ->first();

        if ($defaultRate) {
            $this->labor_rate_id = $defaultRate->id;
        }
    }

    #[Computed]
    public function laborRates()
    {
        return LaborRate::forTeam(Auth::user()->currentTeam->id)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function create()
    {
        if (!Auth::user()->can('create items')) {
            abort(403);
        }

        $validated = $this->validate();

        $validated['team_id'] = Auth::user()->currentTeam->id;

        $item = Item::create($validated);

        $this->dispatch('item-created', itemId: $item->id);

        return redirect()->route('items.index')
            ->with('flash.banner', 'Item created successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    public function render()
    {
        return view('livewire.items.create');
    }
}
