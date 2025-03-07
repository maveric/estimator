<?php

namespace App\Livewire\Estimates;

use Livewire\Component;
use App\Models\Estimate;

class CustomerInformation extends Component
{
    public $estimate;
    
    // Customer information fields
    public $customer_name = '';
    public $customer_email = '';
    public $customer_phone = '';
    public $customer_address = '';
    public $valid_until = '';

    public function mount($estimate = null)
    {
        if ($estimate) {
            $this->estimate = $estimate;
            $this->customer_name = $estimate->customer_name;
            $this->customer_email = $estimate->customer_email;
            $this->customer_phone = $estimate->customer_phone;
            $this->customer_address = $estimate->customer_address;
            $this->valid_until = $estimate->valid_until ? $estimate->valid_until->format('Y-m-d') : '';
        }
    }

    protected function rules()
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'valid_until' => 'nullable|date',
        ];
    }

    public function updated($field)
    {
        $this->validateOnly($field);
        
        // Emit changes to parent component
        $this->dispatch('customer-information-updated', [
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'valid_until' => $this->valid_until,
        ]);
    }

    public function render()
    {
        return view('livewire.estimates.customer-information');
    }
} 