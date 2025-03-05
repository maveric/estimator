<?php

namespace App\Livewire\Settings;

use App\Models\LaborRate;
use App\Models\Settings;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class SettingsManager extends Component
{
    // Labor Rate Settings
    public $laborRates = [];
    public $newLaborRate = [
        'name' => '',
        'cost_rate' => 0,
        'charge_rate' => 0,
        'effective_from' => '',
    ];
    
    // Edit mode tracking
    public $editingLaborRateId = null;
    
    // Success/error messages
    public $message = '';
    public $error = '';
    
    public function mount()
    {
        $this->loadSettings();
        $this->loadLaborRates();
    }
    
    public function loadSettings()
    {
        $settings = Settings::where('tenant_id', auth()->user()->current_tenant_id)->first();
        if ($settings) {
            $this->default_material_markup = $settings->default_material_markup;
            $this->default_labor_markup = $settings->default_labor_markup;
            $this->primary_labor_rate = $settings->primary_labor_rate;
        }
    }
    
    public function loadLaborRates()
    {
        $this->laborRates = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
            ->select('id', 'name', 'cost_rate', 'charge_rate', 'effective_from', 'is_primary')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
    
    public function addLaborRate()
    {
        $this->validate([
            'newLaborRate.name' => 'required|string|max:255',
            'newLaborRate.cost_rate' => 'required|numeric|min:0',
            'newLaborRate.charge_rate' => 'required|numeric|min:0',
            'newLaborRate.effective_from' => 'required|date',
        ]);
        
        try {
            LaborRate::create([
                'tenant_id' => Auth::user()->current_tenant_id,
                'name' => $this->newLaborRate['name'],
                'cost_rate' => $this->newLaborRate['cost_rate'],
                'charge_rate' => $this->newLaborRate['charge_rate'],
                'effective_from' => $this->newLaborRate['effective_from'],
            ]);
            
            $this->message = 'Labor rate added successfully.';
            $this->resetNewLaborRate();
            $this->loadLaborRates();
        } catch (\Exception $e) {
            Log::error('Error adding labor rate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error = 'Error adding labor rate: ' . $e->getMessage();
        }
    }
    
    public function editLaborRate($id)
    {
        $this->editingLaborRateId = $id;
    }
    
    public function updateLaborRate($id)
    {
        // Find the index in the array that corresponds to the labor rate ID
        $index = collect($this->laborRates)->search(function($rate) use ($id) {
            return $rate['id'] == $id;
        });
        
        if ($index === false) {
            $this->error = 'Labor rate not found in the current list.';
            return;
        }
        
        $this->validate([
            "laborRates.$index.name" => 'required|string|max:255',
            "laborRates.$index.cost_rate" => 'required|numeric|min:0',
            "laborRates.$index.charge_rate" => 'required|numeric|min:0',
            "laborRates.$index.effective_from" => 'required|date',
        ]);
        
        try {
            $laborRate = LaborRate::find($id);
            
            if (!$laborRate) {
                $this->error = 'Labor rate not found.';
                return;
            }
            
            $laborRate->update([
                'name' => $this->laborRates[$index]['name'],
                'cost_rate' => $this->laborRates[$index]['cost_rate'],
                'charge_rate' => $this->laborRates[$index]['charge_rate'],
                'effective_from' => $this->laborRates[$index]['effective_from'],
            ]);
            
            $this->message = 'Labor rate updated successfully.';
            $this->editingLaborRateId = null;
            $this->loadLaborRates();
        } catch (\Exception $e) {
            Log::error('Error updating labor rate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error = 'Error updating labor rate: ' . $e->getMessage();
        }
    }
    
    public function deleteLaborRate($id)
    {
        try {
            $laborRate = LaborRate::find($id);
            
            if (!$laborRate) {
                $this->error = 'Labor rate not found.';
                return;
            }
            
            // Check if this labor rate is being used
            // This would require additional checks with your specific models
            
            $laborRate->delete();
            
            $this->message = 'Labor rate deleted successfully.';
            $this->loadLaborRates();
        } catch (\Exception $e) {
            Log::error('Error deleting labor rate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error = 'Error deleting labor rate: ' . $e->getMessage();
        }
    }
    
    public function cancelEdit()
    {
        $this->editingLaborRateId = null;
        $this->loadLaborRates(); // Reload to discard changes
    }
    
    private function resetNewLaborRate()
    {
        $this->newLaborRate = [
            'name' => '',
            'cost_rate' => 0,
            'charge_rate' => 0,
            'effective_from' => date('Y-m-d'),
        ];
    }
    
    public function setPrimaryLaborRate($rateId)
    {
        try {
            DB::beginTransaction();

            // Remove primary status from all existing labor rates
            LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                ->update(['is_primary' => false]);

            // Set the selected rate as primary
            $rate = LaborRate::where('tenant_id', auth()->user()->current_tenant_id)
                ->findOrFail($rateId);
            $rate->is_primary = true;
            $rate->save();

            // Update settings with the new primary labor rate
            $settings = Settings::firstOrCreate(['tenant_id' => auth()->user()->current_tenant_id]);
            $settings->primary_labor_rate = $rate->cost_rate;
            $settings->save();

            DB::commit();
            $this->loadLaborRates();
            $this->loadSettings();
            session()->flash('message', 'Primary labor rate updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error setting primary labor rate: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.settings.settings-manager');
    }
} 