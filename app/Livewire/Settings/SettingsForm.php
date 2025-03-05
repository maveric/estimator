<?php

namespace App\Http\Livewire\Settings;

use App\Models\Settings;
use Livewire\Component;

class SettingsForm extends Component
{
    public $default_material_markup;
    public $default_labor_markup;
    public $primary_labor_rate;

    public function mount()
    {
        $settings = Settings::where('tenant_id', auth()->user()->current_tenant_id)->first();
        if ($settings) {
            $this->default_material_markup = $settings->default_material_markup;
            $this->default_labor_markup = $settings->default_labor_markup;
            $this->primary_labor_rate = $settings->primary_labor_rate;
        }
    }

    public function save()
    {
        $this->validate([
            'default_material_markup' => 'required|numeric|min:0',
            'default_labor_markup' => 'required|numeric|min:0',
            'primary_labor_rate' => 'required|numeric|min:0',
        ]);

        $settings = Settings::where('tenant_id', auth()->user()->current_tenant_id)->first();
        if (!$settings) {
            $settings = new Settings();
            $settings->tenant_id = auth()->user()->current_tenant_id;
        }

        $settings->default_material_markup = $this->default_material_markup;
        $settings->default_labor_markup = $this->default_labor_markup;
        $settings->primary_labor_rate = $this->primary_labor_rate;
        $settings->save();

        session()->flash('message', 'Settings saved successfully.');
    }
} 