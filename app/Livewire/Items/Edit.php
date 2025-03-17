<?php

namespace App\Livewire\Items;

use App\Models\Item;
use App\Models\LaborRate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class Edit extends Component
{
    use AuthorizesRequests;

    public Item $item;
    
    public $name = '';
    public $description = '';
    public $sku = '';
    public $unit_of_measure = '';
    public $material_cost = '';
    public $material_price = '';
    public $labor_minutes = '';
    public $labor_rate_id = '';
    public $is_template = false;
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'sku' => 'required|string|max:50',
        'unit_of_measure' => 'required|string|max:50',
        'material_cost' => 'required|numeric|min:0',
        'material_price' => 'required|numeric|min:0',
        'labor_minutes' => 'required|numeric|min:0',
        'labor_rate_id' => 'required|exists:labor_rates,id',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $layout = 'layouts.app';

    public function mount(Item $item)
    {
        if (!auth()->user()->can('edit items')) {
            abort(403);
        }

        // Ensure user can only edit items from their team
        if ($item->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $this->item = $item;
        $this->name = $item->name;
        $this->description = $item->description;
        $this->sku = $item->sku;
        $this->unit_of_measure = $item->unit_of_measure;
        $this->material_cost = $item->material_cost;
        $this->material_price = $item->material_price;
        $this->labor_minutes = $item->labor_minutes;
        $this->labor_rate_id = $item->labor_rate_id;
        $this->is_template = $item->is_template;
        $this->is_active = $item->is_active;
    }

    public function save()
    {
        $this->validate();

        // Add unique validation for SKU within the team, excluding the current item
        $this->validate([
            'sku' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $exists = Item::where('team_id', Auth::user()->currentTeam->id)
                        ->where('sku', $value)
                        ->where('id', '!=', $this->item->id)
                        ->exists();
                    
                    if ($exists) {
                        $fail('The SKU has already been taken within your team.');
                    }
                },
            ],
        ]);

        // Ensure labor_rate belongs to the same team
        $laborRate = LaborRate::find($this->labor_rate_id);
        if (!$laborRate || $laborRate->team_id !== Auth::user()->currentTeam->id) {
            $this->addError('labor_rate_id', 'Please select a valid labor rate from your team.');
            return;
        }

        $this->item->update([
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'unit_of_measure' => $this->unit_of_measure,
            'material_cost' => $this->material_cost,
            'material_price' => $this->material_price,
            'labor_minutes' => $this->labor_minutes,
            'labor_rate_id' => $this->labor_rate_id,
            'is_template' => $this->is_template,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('item-updated');

        session()->flash('flash.banner', 'Item updated successfully.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('items.index');
    }

    public function render()
    {
        $laborRates = Auth::user()->currentTeam->laborRates()
            ->where('is_active', true)
            ->get();

        return view('livewire.items.edit', [
            'laborRates' => $laborRates,
        ]);
    }
}
