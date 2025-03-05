<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Item;

new #[Layout('layouts.app')] class extends Component
{
    public string $mode = 'create';
    public ?Item $item = null;
    
    // Form properties
    public string $name = '';
    public ?string $description = '';
    public ?string $sku = '';
    public string $unit_of_measure = '';
    public float $material_cost_rate = 0.00;
    public float $material_charge_rate = 0.00;
    public float $labor_units = 0.00;
    public bool $is_active = true;

    public function mount($mode = 'create', Item $item = null)
    {
        $this->mode = $mode;
        
        if ($item) {
            $this->item = $item;
            $this->name = $item->name;
            $this->description = $item->description ?? '';
            $this->sku = $item->sku ?? '';
            $this->unit_of_measure = $item->unit_of_measure;
            $this->material_cost_rate = $item->material_cost_rate;
            $this->material_charge_rate = $item->material_charge_rate;
            $this->labor_units = $item->labor_units;
            $this->is_active = $item->is_active;
        }
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:255|unique:items,sku,' . ($this->item?->id ?? ''),
            'unit_of_measure' => 'required|string|max:255',
            'material_cost_rate' => 'required|numeric|min:0',
            'material_charge_rate' => 'required|numeric|min:0',
            'labor_units' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function save()
    {
        $validated = $this->validate();
        
        try {
            if ($this->mode === 'create') {
                $item = new Item($validated);
                $item->tenant_id = auth()->user()->tenant_id;
                $item->save();
                
                $this->dispatch('item-saved', 
                    status: 'success',
                    message: 'Item created successfully.'
                );
            } else {
                $this->item->update($validated);
                
                $this->dispatch('item-saved', 
                    status: 'success',
                    message: 'Item updated successfully.'
                );
            }
            
            $this->redirect(route('items.index'));
        } catch (\Exception $e) {
            $this->dispatch('item-saved', 
                status: 'error',
                message: 'Error saving item: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.items.item-form');
    }
}; ?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $mode === 'create' ? 'Create Item' : 'Edit Item' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <livewire:items.item-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
