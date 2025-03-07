<?php

namespace App\Livewire\Estimates;

use App\Models\Estimate;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EstimateDetails extends Component
{
    public $estimate;
    public $status = 'draft';
    public $markup_percentage = 0;
    public $discount_percentage = 0;
    public $notes = '';

    protected $rules = [
        'status' => 'required|in:draft,sent,approved,declined',
        'markup_percentage' => 'numeric|min:0|max:100',
        'discount_percentage' => 'numeric|min:0|max:100',
        'notes' => 'nullable|string',
    ];

    public function mount(Estimate $estimate)
    {
        $this->estimate = $estimate;
        $this->status = $estimate->status;
        $this->markup_percentage = $estimate->markup_percentage;
        $this->discount_percentage = $estimate->discount_percentage;
        $this->notes = $estimate->notes;
    }

    public function updatedStatus()
    {
        $this->saveDetails();
    }

    public function updatedMarkupPercentage()
    {
        $this->saveDetails();
    }

    public function updatedDiscountPercentage()
    {
        $this->saveDetails();
    }

    public function updatedNotes()
    {
        $this->saveDetails();
    }

    protected function saveDetails()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $this->estimate->update([
                    'status' => $this->status,
                    'markup_percentage' => $this->markup_percentage,
                    'discount_percentage' => $this->discount_percentage,
                    'notes' => $this->notes
                ]);
            });

            // Dispatch event to notify EstimateForm
            $this->dispatch('estimate-details-updated', [
                'status' => $this->status,
                'markup_percentage' => $this->markup_percentage,
                'discount_percentage' => $this->discount_percentage,
                'notes' => $this->notes
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving estimate details:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('warning', 'Warning: Unable to update estimate details.');
        }
    }

    public function render()
    {
        return view('livewire.estimates.estimate-details');
    }
} 