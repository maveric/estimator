<?php

namespace App\Livewire\LaborRates;

use App\Models\LaborRate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
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

    protected $layout = 'layouts.app';

    public function mount()
    {
        if (!auth()->user()->can('create labor rates')) {
            abort(403);
        }
    }

    public function save()
    {
        $this->validate();

        $team = Auth::user()->currentTeam;

        // If this is set as default, unset any existing defaults
        if ($this->is_default) {
            $team->laborRates()->where('is_default', true)->update(['is_default' => false]);
        }

        // If this is the first labor rate for the team, make it default
        if ($team->laborRates()->count() === 0) {
            $this->is_default = true;
        }

        $team->laborRates()->create([
            'name' => $this->name,
            'cost_rate' => $this->cost_rate,
            'price_rate' => $this->price_rate,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('labor-rate-created');

        session()->flash('flash.banner', 'Labor rate created successfully.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('labor-rates.index');
    }

    public function render()
    {
        return view('livewire.labor-rates.create')->layout('layouts.app');
    }
}
