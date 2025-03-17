<?php

namespace App\Livewire\LaborRates;

use App\Models\LaborRate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class Edit extends Component
{
    use AuthorizesRequests;

    public LaborRate $laborRate;
    
    public $name = '';
    public $cost_rate = '';
    public $price_rate = '';
    public $is_default = false;
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'cost_rate' => 'required|numeric|min:0',
        'price_rate' => 'required|numeric|min:0',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function mount(LaborRate $laborRate)
    {
        if (!auth()->user()->can('edit labor rates')) {
            abort(403);
        }

        // Ensure user can only edit labor rates from their team
        if ($laborRate->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $this->laborRate = $laborRate;
        $this->name = $laborRate->name;
        $this->cost_rate = $laborRate->cost_rate;
        $this->price_rate = $laborRate->price_rate;
        $this->is_default = $laborRate->is_default;
        $this->is_active = $laborRate->is_active;
    }

    public function save()
    {
        $this->validate();

        $team = Auth::user()->currentTeam;

        // If this is set as default, unset any existing defaults (except this one)
        if ($this->is_default) {
            $team->laborRates()
                ->where('id', '!=', $this->laborRate->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $this->laborRate->update([
            'name' => $this->name,
            'cost_rate' => $this->cost_rate,
            'price_rate' => $this->price_rate,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('labor-rate-updated');

        session()->flash('flash.banner', 'Labor rate updated successfully.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('labor-rates.index');
    }

    public function render()
    {
        return view('livewire.labor-rates.edit');
    }
}
